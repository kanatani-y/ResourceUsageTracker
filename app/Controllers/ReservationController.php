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
    

    public function create($resource_id = null)
    {
        $resourceModel = new ResourceModel();
        $accountModel = new AccountModel();
    
        // リソース情報を取得
        $resources = $resourceModel->select('id, name, hostname')->orderBy('name', 'ASC')->findAll();
    
        // リソースごとに紐づくアカウント一覧を取得
        $accounts = [];
        foreach ($resources as $resource) {
            $accounts[$resource['id']] = $accountModel->where('resource_id', $resource['id'])->findAll();
        }
    
        return view('reservation/reservation_form', [
            'resource_id' => $resource_id,
            'resources'   => $resources,
            'accounts'    => $accounts,
        ]);
    }

    public function store()
    {
        $reservationModel = new ReservationModel();
    
        // **バリデーション**
        if (!$this->validate([
            'resource_id'      => 'required|integer',
            'account_id'       => 'required|integer',
            'reservation_date' => 'required|valid_date',
            'start_time'       => 'required',
            'end_time'         => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
    
        // **送信されたデータを結合**
        $date = $this->request->getPost('reservation_date'); // YYYY-MM-DD
        $startTime = $this->request->getPost('start_time');  // HH:MM
        $endTime = $this->request->getPost('end_time');      // HH:MM
    
        $startDateTime = "$date $startTime:00"; // YYYY-MM-DD HH:MM:00
        $endDateTime = "$date $endTime:00";     // YYYY-MM-DD HH:MM:00
    
        // **データ準備**
        $data = [
            'resource_id' => $this->request->getPost('resource_id'),
            'account_id'  => $this->request->getPost('account_id'),
            'user_id'     => auth()->user()->id,
            'start_time'  => $startDateTime,
            'end_time'    => $endDateTime,
            'status'      => 'pending'
        ];
    
        // **予約の重複チェック**
        if ($reservationModel->isOverlapping($data['resource_id'], $data['start_time'], $data['end_time'])) {
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
        return redirect()->route('reservation.index')->with('message', '予約を削除しました。');
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
            $accounts[$resource['id']] = $accountModel->where('resource_id', $resource['id'])->findAll();
        }
    
        // 予約データ取得（選択日）
        $reservations = $reservationModel
            ->select('reservations.*, resources.name as resource_name, 
                    COALESCE(accounts.username, "なし") as account_name, 
                    users.fullname as user_name')
            ->join('resources', 'resources.id = reservations.resource_id')
            ->join('accounts', 'accounts.id = reservations.account_id', 'left') // アカウントがない場合を考慮
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
}
