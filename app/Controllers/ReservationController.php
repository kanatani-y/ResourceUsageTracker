<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReservationModel;
use App\Models\AccountModel;
use App\Models\ResourceModel;

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
    
        return view('reservation/index', [
            'resources'    => $resources,
            'reservations' => $reservations,
            'selectedDate' => $date,
        ]);
    }

    public function create($resource_id = null, $account_id = null, $time = null, $reservation_date = null)
    {
        // `time` の `-` を `:` に戻す
        if ($time) {
            $time = str_replace('-', ':', $time);
        }
    
        $resourceModel = new ResourceModel();
        $accountModel = new AccountModel();
        $reservationModel = new ReservationModel();
    
        // リソース一覧取得
        $resources = $resourceModel->select('id, name, hostname')->orderBy('name', 'ASC')->findAll();
    
        // 各リソースのアカウント一覧取得（デフォルト値を設定）
        $accounts = [];
        foreach ($resources as $resource) {
            $resourceAccounts = $accountModel->where('resource_id', $resource['id'])->findAll();
            $accounts[$resource['id']] = !empty($resourceAccounts) ? $resourceAccounts : [['id' => -1, 'username' => 'なし']];
        }
    
        // **予約日をURLから取得し、デフォルト値を設定**
        $selectedDate = $reservation_date ?? date('Y-m-d');
    
        // **開始時刻のデフォルト設定**
        $startTime = $time ?? "09:00"; // 指定がない場合は `09:00` をデフォルトに
    
        // **終了時刻を開始時刻の+1時間に設定**
        $startHour = (int) explode(':', $startTime)[0];
        $endHour = min($startHour + 1, 23); // 終了時刻が 23:00 を超えないようにする
        $endTime = sprintf('%02d:00', $endHour);
    
        return view('reservation/reservation_form', [
            'resource_id'  => $resource_id,
            'account_id'   => $account_id,
            'time'         => $startTime,  // 開始時刻
            'end_time'     => $endTime,    // 終了時刻
            'resources'    => $resources,
            'accounts'     => $accounts[$resource_id] ?? [], // 指定された `resource_id` のアカウントリストを渡す
            'reservations' => [],
            'selectedDate' => $selectedDate, // 予約日
        ]);
    }

    public function store()
    {
        $reservationModel = new ReservationModel();
    
        // **送信されたデータを結合**
        $date = $this->request->getPost('reservation_date'); // YYYY-MM-DD
        $startTime = $this->request->getPost('start_time');  // HH:MM
        $endTime = $this->request->getPost('end_time');      // HH:MM
    
        $startDateTime = "$date $startTime:00"; // YYYY-MM-DD HH:MM:00
        $endDateTime = "$date $endTime:00";     // YYYY-MM-DD HH:MM:00

        // **デフォルト値を適用**
        $account_id = $this->request->getPost('account_id');
        if (empty($account_id)) {
            $account_id = -1; // アカウントなしのデフォルト値
        }

        // **データ準備**
        $data = [
            'resource_id' => $this->request->getPost('resource_id'),
            'account_id'  => $account_id,
            'user_id'     => auth()->user()->id,
            'start_time'  => $startDateTime,
            'end_time'    => $endDateTime,
            'purpose'     => $this->request->getPost('purpose'),
        ];

        // **予約の重複チェック**
        if ($reservationModel->isOverlapping($data['resource_id'], $data['account_id'], $data['start_time'], $data['end_time'])) {
            return redirect()->back()->withInput()->with('error', 'この時間帯にはすでに予約が入っています。');
        }

        // **データ保存**
        $reservationModel->insert($data);
    
        return redirect()->to(route_to('reservation.schedule', ['date' => $date]))
                        ->with('message', '予約を追加しました。');
    }

    public function delete($id)
    {
        $reservationModel = new ReservationModel();
        $reservationModel->delete($id);
        return redirect()->route('reservation.schedule')->with('message', '予約を削除しました。');
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
            $resourceAccounts = $accountModel->where('resource_id', $resource['id'])->findAll();
            if (empty($resourceAccounts)) {
                // アカウントがない場合、デフォルト値を追加
                $accounts[$resource['id']] = [['id' => 0, 'username' => 'なし']];
            } else {
                $accounts[$resource['id']] = $resourceAccounts;
            }
        }

        // 予約データ取得（選択日）
        $reservations = $reservationModel
            ->select('reservations.*, resources.name as resource_name, 
                IFNULL(accounts.username, "なし") as account_name, 
                users.fullname as user_name')
            ->join('resources', 'resources.id = reservations.resource_id')
            ->join('accounts', 'accounts.id = reservations.account_id AND reservations.account_id > 0', 'left') 
            ->join('users', 'users.id = reservations.user_id', 'left')
            ->where("start_time BETWEEN '$selectedDate 00:00:00' AND '$selectedDate 23:59:59'")
            ->orderBy('start_time', 'ASC')
            ->findAll();

        return view('reservation/schedule', [
            'resources'    => $resources,
            'accounts'     => $accounts,
            'reservations' => $reservations,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function edit($id)
    {
        $reservationModel = new ReservationModel();
        $resourceModel = new ResourceModel();
        $accountModel = new AccountModel();
    
        // 予約情報を取得
        $reservation = $reservationModel->find($id);
        if (!$reservation) {
            return redirect()->route('reservation.schedule')->with('error', '指定された予約が見つかりませんでした。');
        }
    
        // 利用可能なリソース（編集時は変更不可）
        $resources = $resourceModel->findAll();
        $accounts = $accountModel->where('resource_id', $reservation['resource_id'])->findAll();
    
        // **予約状況の取得（予約日以降）**
        $selectedDate = date('Y-m-d');
        $reservations = $reservationModel
            ->select('reservations.*, users.fullname AS user_name')
            ->join('users', 'users.id = reservations.user_id')
            ->where('resource_id', $reservation['resource_id'])
            ->where('start_time >=', "$selectedDate 00:00:00")
            ->orderBy('start_time', 'ASC')
            ->findAll();
    
        return view('reservation/reservation_form', [
            'reservation'  => $reservation,
            'resource_id'  => $reservation['resource_id'],
            'account_id'   => $reservation['account_id'],
            'resources'    => $resources,
            'accounts'     => $accounts,
            'reservations' => $reservations,
            'selectedDate' => $selectedDate
        ]);
    }

    public function update($id)
    {
        $reservationModel = new ReservationModel();

        // 予約情報を取得
        $reservation = $reservationModel->find($id);
        if (!$reservation) {
            return redirect()->route('reservation.schedule')->with('error', '指定された予約が見つかりませんでした。');
        }

        // **入力データを取得**
        $date = $this->request->getPost('reservation_date'); // YYYY-MM-DD
        $startTime = $this->request->getPost('start_time');  // HH:MM
        $endTime = $this->request->getPost('end_time');      // HH:MM
        $purpose = $this->request->getPost('purpose');       // 使用目的

        // **時間のフォーマットを変換**
        $startDateTime = "$date $startTime:00";
        $endDateTime = "$date $endTime:00";

        // **デフォルト値を適用**
        $account_id = $this->request->getPost('account_id');
        if (empty($account_id)) {
            $account_id = -1; // アカウントなしのデフォルト値
        }

        // **更新データの準備**
        $data = [
            'resource_id' => $reservation['resource_id'],
            'account_id'  => $account_id,
            'start_time'  => $startDateTime,
            'end_time'    => $endDateTime,
            'purpose'     => $this->request->getPost('purpose'),
        ];
    
        // **予約の重複チェック**
        if ($reservationModel->isOverlapping($data['resource_id'], $data['account_id'], $data['start_time'], $data['end_time'], $id)) {
            return redirect()->back()->withInput()->with('error', 'この時間帯にはすでに予約が入っています。');
        }

        // **予約情報の更新**
        $reservationModel->update($id, $data);

        return redirect()->to(route_to('reservation.schedule', ['date' => $date]))
                        ->with('message', '予約が更新されました。');
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
                ->select('reservations.*, users.fullname AS user_name, accounts.username AS account_name')
                ->join('users', 'users.id = reservations.user_id', 'left')
                ->join('accounts', 'accounts.id = reservations.account_id', 'left OUTER')
                ->where('reservations.start_time >=', "$selectedDate 00:00:00")
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
