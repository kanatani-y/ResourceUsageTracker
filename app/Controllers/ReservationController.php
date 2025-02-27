<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReservationModel;
use App\Models\AccountModel;
use App\Models\ResourceModel;
use App\Models\UserModel;

class ReservationController extends BaseController
{
    public function index($date = null)
    {
        $reservationModel = new ReservationModel();
        $resourceModel = new ResourceModel();
    
        // 日付が指定されていなければ本日の日付をセット
        if ($date === null) {
            $date = date('Y-m-d');
        }
    
        // リソース一覧を取得
        $resources = $resourceModel->orderBy('name', 'ASC')->findAll();
    
        // 指定日付の予約を取得
        $reservations = $reservationModel
            ->select('reservations.*, users.fullname as user_name, resources.name as resource_name')
            ->join('users', 'users.id = reservations.user_id')
            ->join('resources', 'resources.id = reservations.resource_id')
            ->where('DATE(start_time)', $date)
            ->orderBy('start_time', 'ASC')
            ->findAll();
    
        return view('reservations/index', [
            'resources'    => $resources,
            'reservations' => $reservations,
            'selectedDate' => $date,
        ]);
    }

    public function create($resource_id = null, $account_id = null, $reservation_date = null, $time = null)
    {
        // **ゲストユーザーは予約作成画面にアクセスできない**
        if (auth()->user()->inGroup('guest')) {
            return redirect()->to(site_url('reservations/schedule'))->with('error', 'ゲストユーザーは予約を登録できません。');
        }
        
        // `time` の `-` を `:` に戻す
        if ($time) {
            $time = str_replace('-', ':', $time);
        }

        $resourceModel = new ResourceModel();
        $accountModel = new AccountModel();
        $userModel = new \App\Models\UserModel();

        if ($account_id) {
            $account = $accountModel->find($account_id);
    
            // 利用禁止または廃止のアカウントは予約不可
            if ($account && in_array($account['active'], [0])) {
                return redirect()->to(site_url('reservations/schedule'))->with('error', 'このアカウントは予約できません。');
            }
        }

        // リソース一覧取得
        $resources = $resourceModel->select('id, name, hostname')->orderBy('name', 'ASC')->findAll();
    
        // **各リソースのアカウント一覧取得（利用可能のもののみ）**
        $accounts = [];
        foreach ($resources as $resource) {
            $resourceAccounts = $accountModel->where('resource_id', $resource['id'])
                                            ->where('active', 1) // 予約可能なアカウントのみ取得
                                            ->findAll();
    
            $accounts[$resource['id']] = !empty($resourceAccounts) ? $resourceAccounts : [];
        }

        $users = auth()->user()->inGroup('admin')
            ? $userModel->select('users.id, users.username, users.fullname')
                ->join('auth_groups_users agu', 'agu.user_id = users.id')
                ->where('agu.group !=', 'guest') // guest グループを除外
                ->where('users.active', 1) // 無効ユーザーをブロック
                ->findAll()
            : [];
        
        // **予約日をURLから取得し、デフォルト値を設定**
        $selectedDate = $reservation_date ?? date('Y-m-d');

        // **開始時刻のデフォルト設定（1時間刻み）**
        if (!$time) {
            $currentHour = (int) date('H');

            if ($currentHour < 9) {
                $startTime = "09:00";
            } elseif ($currentHour >= 17) {
                $startTime = "17:00";
            } else {
                $startTime = sprintf('%02d:00', min($currentHour + 1, 17));
            }
        } else {
            $startTime = $time;
        }

        // **終了時刻を開始時刻の+1時間に設定（最大18:00）**
        $startHour = (int) explode(':', $startTime)[0];
        $endTime = sprintf('%02d:00', min($startHour + 1, 18));

        return view('reservations/form', [
            'resource_id'  => $resource_id,
            'account_id'   => $account_id,
            'time'         => $startTime,
            'end_time'     => $endTime,
            'resources'    => $resources,
            'accounts'     => $accounts,
            'reservations' => [],
            'selectedDate' => $selectedDate, // 予約日
            'users'        => $users,
        ]);
    }

    public function store()
    {
        // **ゲストユーザーは予約できない**
        if (auth()->user()->inGroup('guest')) {
            return redirect()->to(site_url('reservations/schedule'))->with('error', 'ゲストユーザーは予約を登録できません。');
        }

        $reservationModel = new ReservationModel();
    
        // **送信されたデータを結合**
        $date = $this->request->getPost('reservation_date'); // YYYY-MM-DD
        $startTime = $this->request->getPost('start_time');  // HH:MM
        $endTime = $this->request->getPost('end_time');      // HH:MM
    
        $startDateTime = "$date $startTime:00"; // YYYY-MM-DD HH:MM:00
        $endDateTime = "$date $endTime:00";     // YYYY-MM-DD HH:MM:00

            // **開始時刻 < 終了時刻 のバリデーション**
        if (strtotime($startDateTime) >= strtotime($endDateTime)) {
            return redirect()->back()->withInput()->with('error', '開始時刻は終了時刻より前に設定してください。');
        }

        // **管理者以外は過去日の予約を登録できない**
        if (!auth()->user()->inGroup('admin') && $date < date('Y-m-d')) {
            return redirect()->back()->withInput()->with('error', '過去日の予約はできません。');
        }

         // **管理者が指定したユーザーを使用、それ以外は自分のID**
        $user_id = (auth()->user()->inGroup('admin') && $this->request->getPost('user_id'))
            ? $this->request->getPost('user_id')
            : auth()->user()->id;
        
        // **デフォルト値を適用**
        $account_id = $this->request->getPost('account_id');
        if (empty($account_id)) {
            $account_id = -1; // アカウントなしのデフォルト値
        }

        // **管理者が代理登録した場合、使用目的に"ユーザ氏名＋代理登録:" を追加**
        $purpose = $this->request->getPost('purpose');
        $userModel = new UserModel();
        if (auth()->user()->inGroup('admin') && $user_id != auth()->user()->id) {
            $user = $userModel->find($user_id);
            if ($user) {
                $purpose = "【{$user->fullname} 代理登録】" . $purpose;
            }
        }

        // **データ準備**
        $data = [
            'resource_id' => $this->request->getPost('resource_id'),
            'account_id'  => $account_id,
            'user_id'     => $user_id,
            'start_time'  => $startDateTime,
            'end_time'    => $endDateTime,
            'purpose'     => $purpose,
        ];

        // **予約の重複チェック**
        if ($reservationModel->isOverlapping($data['resource_id'], $data['account_id'], $data['start_time'], $data['end_time'])) {
            return redirect()->back()->withInput()->with('error', 'この時間帯にはすでに予約が入っています。');
        }

        try {
            $reservationModel->insert($data);
            $this->logAction('reservation', 'created', $reservationModel->insertID(), $data, auth()->user()->id);
        } catch (\Exception $e) {
            $this->logAction('reservation', 'failed', null, $data, auth()->user()->id, '予約作成時にエラー発生: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '予約の登録中にエラーが発生しました。');
        }

        return redirect()->to(site_url('reservations/schedule') . '?date=' . urlencode($date))
                ->with('message', '予約を追加しました。');
    }

    public function schedule()
    {
        $resourceModel = new ResourceModel();
        $accountModel = new AccountModel();
        $reservationModel = new ReservationModel();
    
        // 予約一覧を取得（特定の日付）
        $selectedDate = $this->request->getGet('date') ?? date('Y-m-d');
    
        // 全リソースとアカウントを取得
        $resources = $resourceModel->findAll();
        $accounts = [];
        foreach ($resources as $resource) {
            $resourceAccounts = $accountModel
                ->select('id, account_name, active, connection_type')
                ->where('resource_id', $resource['id'])
                ->findAll();
    
            if (empty($resourceAccounts)) {
                // アカウントがない場合、デフォルト値を追加
                $accounts[$resource['id']] = [['id' => 0, 'account_name' => 'なし', 'active' => 1, 'connection_type' => '']];
            } else {
                $accounts[$resource['id']] = $resourceAccounts;
            }
        }
    
        // 予約データ取得（選択日）
        $reservations = $reservationModel
            ->select('reservations.*, resources.name as resource_name, resources.type as resource_type, 
                IFNULL(accounts.account_name, "なし") as account_name, 
                accounts.active as account_active, 
                users.fullname as user_name')
            ->join('resources', 'resources.id = reservations.resource_id')
            ->join('accounts', 'accounts.id = reservations.account_id AND reservations.account_id > 0', 'left')
            ->join('users', 'users.id = reservations.user_id', 'left')
            ->where("start_time BETWEEN '$selectedDate 00:00:00' AND '$selectedDate 23:59:59'")
            ->orderBy('start_time', 'ASC')
            ->findAll();
    
        return view('reservations/schedule', [
            'resources'    => $resources,
            'accounts'     => $accounts,
            'reservations' => $reservations,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function edit($id)
    {
        // **ゲストユーザーは予約編集画面にアクセスできない**
        if (auth()->user()->inGroup('guest')) {
            return redirect()->route('reservations.schedule')->with('error', 'ゲストユーザーは予約を編集できません。');
        }
        
        $reservationModel = new ReservationModel();
        $resourceModel = new ResourceModel();
        $accountModel = new AccountModel();
        $userModel = new \App\Models\UserModel();
    
        // 予約情報を取得
        $reservation = $reservationModel->find($id);
        if (!$reservation) {
            return redirect()->route('reservations.schedule')->with('error', '指定された予約が見つかりませんでした。');
        }
    
        // **管理者を除外し、予約者本人以外のアクセスを禁止**
        if (!auth()->user()->inGroup('admin') && $reservation['user_id'] != auth()->user()->id) {
            return redirect()->route('reservations.schedule')->with('error', 'この予約を編集する権限がありません。');
        }

        $account = $accountModel->find($reservation['account_id']);
    
        // 無効のアカウントは予約不可
        if ($account && in_array($account['active'], [0])) {
            return redirect()->route('reservations.schedule')->with('error', 'このアカウントは予約できません。');
        }
        
        // リソース一覧取得
        $resources = $resourceModel->select('id, name, hostname')->orderBy('name', 'ASC')->findAll();

        // **各リソースのアカウント一覧取得（利用可能のもののみ）**
        $accounts = [];
        foreach ($resources as $resource) {
            $resourceAccounts = $accountModel->where('resource_id', $resource['id'])
                                            ->where('active', 1) // 予約可能なアカウントのみ取得
                                            ->findAll();
    
            $accounts[$resource['id']] = !empty($resourceAccounts) ? $resourceAccounts : [];
        }
        // **開始・終了時間の設定**
        $startTime = date('H:i', strtotime($reservation['start_time']));
        $endTime = date('H:i', strtotime($reservation['end_time']));
        $selectedDate = date('Y-m-d', strtotime($reservation['start_time']));
    
        // **予約状況の取得**
        $reservations = $reservationModel
            ->select('reservations.*, users.fullname AS user_name')
            ->join('users', 'users.id = reservations.user_id')
            ->where('resource_id', $reservation['resource_id'])
            ->where('start_time >=', "$selectedDate 00:00:00")
            ->orderBy('start_time', 'ASC')
            ->findAll();
    
        // **ユーザー情報（管理者のみ選択可能）**
        $users = auth()->user()->inGroup('admin')
            ? $userModel->select('users.id, users.username, users.fullname')
                ->join('auth_groups_users agu', 'agu.user_id = users.id')
                ->where('agu.group !=', 'guest') // guest グループを除外
                ->where('users.active', 1) // 無効ユーザーをブロック
                ->findAll()
            : [];
    
        return view('reservations/form', [
            'reservation'  => $reservation,
            'resource_id'  => $reservation['resource_id'],
            'account_id'   => $reservation['account_id'],
            'time'         => $startTime,  // 開始時刻
            'end_time'     => $endTime,    // 終了時刻
            'resources'    => $resources,
            'accounts'     => $accounts,
            'reservations' => $reservations,
            'selectedDate' => $selectedDate,
            'users'        => $users,
        ]);
    }

    public function update($id)
    {
        $reservationModel = new ReservationModel();

        // 予約情報を取得
        $reservation = $reservationModel->find($id);
        if (!$reservation) {
            return redirect()->route('reservations.schedule')->with('error', '指定された予約が見つかりませんでした。');
        }

        // **入力データを取得**
        $date = $this->request->getPost('reservation_date'); // YYYY-MM-DD
        $startTime = $this->request->getPost('start_time');  // HH:MM
        $endTime = $this->request->getPost('end_time');      // HH:MM
        $purpose = $this->request->getPost('purpose');       // 使用目的

        // **時間のフォーマットを変換**
        $startDateTime = "$date $startTime:00";
        $endDateTime = "$date $endTime:00";

        // **開始時刻 < 終了時刻 のバリデーション**
        if (strtotime($startDateTime) >= strtotime($endDateTime)) {
            return redirect()->back()->withInput()->with('error', '開始時刻は終了時刻より前に設定してください。');
        }

        // **管理者以外は過去日の予約を変更できない**
        if (!auth()->user()->inGroup('admin') && $date < date('Y-m-d')) {
            return redirect()->route('reservations.schedule')->with('error', '過去日の予約は変更できません。');
        }

        // **管理者が指定したユーザーを使用、それ以外は自分のID**
        $user_id = (auth()->user()->inGroup('admin') && $this->request->getPost('user_id'))
        ? $this->request->getPost('user_id')
        : auth()->user()->id;

        // **デフォルト値を適用**
        $account_id = $this->request->getPost('account_id');
        if (empty($account_id)) {
            $account_id = -1; // アカウントなしのデフォルト値
        }

        // **更新データの準備**
        $data = [
            'resource_id' => $reservation['resource_id'],
            'account_id'  => $account_id,
            'user_id'     => $user_id,
            'start_time'  => $startDateTime,
            'end_time'    => $endDateTime,
            'purpose'     => $purpose,
        ];
    
        // **予約の重複チェック**
        if ($reservationModel->isOverlapping($data['resource_id'], $data['account_id'], $data['start_time'], $data['end_time'], $id)) {
            return redirect()->back()->withInput()->with('error', 'この時間帯にはすでに予約が入っています。');
        }

        try {
            $reservationModel->update($id, $data);
            $this->logAction('reservation', 'updated', $id, $data, auth()->user()->id);
        } catch (\Exception $e) {
            $this->logAction('reservation', 'failed', $id, $data, auth()->user()->id, '予約更新時にエラー発生: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', '予約の更新中にエラーが発生しました。');
        }

        return redirect()->to(route_to('reservations.schedule', ['date' => $date]))
                        ->with('message', '予約が更新されました。');
    }

    public function delete($id)
    {
        $reservationModel = new ReservationModel();
        $reservation = $reservationModel->find($id);
    
        if (!$reservation) {
            return redirect()->route('reservations.schedule')->with('error', '予約が見つかりません。');
        }
        // **管理者または予約本人のみ削除可能**
        if (!auth()->user()->inGroup('admin') && $reservation['user_id'] != auth()->user()->id) {
            return redirect()->route('reservations.schedule')->with('error', 'この予約を削除する権限がありません。');
        }
    
        try {
            // **予約の削除実行**
            $reservationModel->delete($id);

            // **削除成功時のログ**
            $this->logAction(
                'reservation', 
                'deleted', 
                $id, 
                ['user_id' => auth()->user()->id, 'reservation_user_id' => $reservation['user_id']]
            );
    
        } catch (\Exception $e) {
            $this->logAction(
                'reservation', 
                'failed', 
                $id, 
                ['user_id' => auth()->user()->id, 'reservation_user_id' => $reservation['user_id']], 
                auth()->user()->id, 
                '予約削除中にエラー発生: ' . $e->getMessage()
            );
            return redirect()->route('reservations.schedule')->with('error', '予約の削除中にエラーが発生しました。');
        }
        return redirect()->route('reservations.schedule')->with('message', '予約を削除しました。');
    }

    public function getReservations()
    {
        try {
            $reservationModel = new ReservationModel();
        
            $selectedDate = $this->request->getGet('date') ?? date('Y-m-d');
            $resourceId = $this->request->getGet('resource_id');
            $accountId = $this->request->getGet('account_id');
    
            if (!$resourceId) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid Parameters']);
            }
    
            // 条件に一致する予約を取得
            $query = $reservationModel
                ->select('reservations.*, users.fullname AS user_name, accounts.account_name AS account_name')
                ->join('users', 'users.id = reservations.user_id', 'left')
                ->join('accounts', 'accounts.id = reservations.account_id', 'left OUTER')
                ->where('reservations.start_time >=', "$selectedDate 00:00:00")
                ->where('reservations.start_time <=', "$selectedDate 23:59:59")
                ->where('reservations.resource_id', $resourceId);
    
            // アカウントありの場合のみ `account_id` 条件を適用
            if ($accountId !== null && $accountId > 0) {
                $query->where('account_id', $accountId);
            }
    
            $reservations = $query->orderBy('start_time', 'ASC')->findAll();
    
            return $this->response->setJSON($reservations);
        } catch (\Exception $e) {
            log_message('error', 'getReservations Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

}
