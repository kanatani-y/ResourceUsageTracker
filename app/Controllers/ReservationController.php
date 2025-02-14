<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReservationModel;
use App\Models\AccountModel;
use App\Models\ResourceModel;

class ReservationController extends BaseController
{
    public function index()
    {
        $reservationModel = new ReservationModel();
        $reservations = $reservationModel
            ->select('reservations.*, resources.name as resource_name, users.fullname as user_name')
            ->join('resources', 'resources.id = reservations.resource_id')
            ->join('users', 'users.id = reservations.user_id')
            ->orderBy('start_time', 'ASC')
            ->findAll();

        return view('reservations/index', ['reservations' => $reservations]);
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
    
        // 入力値取得
        $reservationDate = $this->request->getPost('reservation_date'); // YYYY-MM-DD
        $startTime = trim($this->request->getPost('start_time')); // HH:MM
        $endTime = trim($this->request->getPost('end_time')); // HH:MM
    
        // DATETIME 形式に変換
        $startDateTime = "{$reservationDate} {$startTime}:00";
        $endDateTime = "{$reservationDate} {$endTime}:00";
    
        $data = [
            'resource_id' => $this->request->getPost('resource_id'),
            'user_id'     => auth()->user()->id,
            'start_time'  => $startDateTime, // YYYY-MM-DD HH:MM:SS
            'end_time'    => $endDateTime,   // YYYY-MM-DD HH:MM:SS
            'status'      => 'pending'
        ];
    
        // 予約の重複チェック
        if ($reservationModel->isOverlapping($data['resource_id'], $data['start_time'], $data['end_time'])) {
            return redirect()->back()->withInput()->with('error', 'この時間帯にはすでに予約が入っています。');
        }
    
        // データを保存
        if (!$reservationModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $reservationModel->errors());
        }
    
        return redirect()->route('reservation.index')->with('message', '予約を追加しました。');
    }

    public function delete($id)
    {
        $reservationModel = new ReservationModel();
        $reservationModel->delete($id);
        return redirect()->route('reservation.index')->with('message', '予約を削除しました。');
    }
}
