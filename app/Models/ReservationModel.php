<?php

namespace App\Models;

use CodeIgniter\Model;

class ReservationModel extends Model
{
    protected $table = 'reservations';
    protected $primaryKey = 'id';
    protected $allowedFields = ['resource_id', 'user_id', 'start_time', 'end_time', 'status'];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;

    protected $validationRules = [
        'resource_id' => 'required|integer',
        'user_id'     => 'required|integer',
        'start_time'  => 'required|valid_date[Y-m-d H:i:s]',
        'end_time'    => 'required|valid_date[Y-m-d H:i:s]|after[start_time]',
        'status'      => 'in_list[pending,confirmed,cancelled]',
    ];

    protected $validationMessages = [
        'end_time' => [
            'after' => '終了時間は開始時間より後に設定してください。',
        ],
    ];

    public function isOverlapping($resource_id, $start_time, $end_time, $exclude_id = null)
    {
        $this->where('resource_id', $resource_id);
        $this->where('start_time <', $end_time);
        $this->where('end_time >', $start_time);

        if ($exclude_id) {
            $this->where('id !=', $exclude_id);
        }

        return $this->countAllResults() > 0;
    }
}
