<?php

namespace App\Models;

use CodeIgniter\Model;

class ReservationModel extends Model
{
    protected $table = 'reservations';
    protected $primaryKey = 'id';
    protected $allowedFields = ['resource_id', 'account_id', 'user_id', 'start_time', 'end_time', 'purpose'];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;

    protected $validationRules = [
        'resource_id' => [
            'rules' => 'required|integer',
            'label' => 'リソース',
        ],
        'account_id' => [
            'rules' => 'permit_empty|integer',
            'label' => 'アカウント',
        ],
        'user_id' => [
            'rules' => 'required|integer',
            'label' => 'ユーザー',
        ],
        'start_time' => [
            'rules' => 'required|valid_date[Y-m-d H:i:s]',
            'label' => '開始時間',
        ],
        'end_time' => [
            'rules' => 'required|valid_date[Y-m-d H:i:s]|validateDateTimeOrder[start_time]',
            'label' => '終了時間',
        ],
        'purpose' => [
            'rules' => 'permit_empty|max_length[500]',
            'label' => '使用目的',
        ],
    ];

    protected $validationMessages = [
        'end_time' => [
            'validateDateTimeOrder' => '終了時間は開始時間より後に設定してください。',
        ],
    ];

    public function isOverlapping($resource_id, $account_id, $start_time, $end_time, $exclude_id = null)
    {
        $this->where('resource_id', $resource_id);
        $this->where('start_time <', $end_time);
        $this->where('end_time >', $start_time);
    
        // アカウントがあるリソースは `account_id` も一致するものをチェック
        if ($account_id > 0) {
            $this->where('account_id', $account_id);
        }
    
        if ($exclude_id) {
            $this->where('id !=', $exclude_id);
        }
    
        return $this->countAllResults() > 0;
    }
}
