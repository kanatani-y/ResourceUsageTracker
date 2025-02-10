<?php

namespace App\Models;

use CodeIgniter\Model;

class ResourceModel extends Model
{
    protected $table = 'resources'; // テーブル名
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name', 
        'type', 
        'os', 
        'ip_address', 
        'mac_address', 
        'cpu', 
        'memory', 
        'storage', 
        'status', 
        'location', 
        'description', 
        'created_at', 
        'updated_at', 
        'deleted_at'
    ];

    protected $useTimestamps = true; // 自動的に `created_at`, `updated_at` を管理
    protected $useSoftDeletes = true; // `deleted_at` をソフトデリートとして利用

    protected $dateFormat = 'datetime'; // 日付フォーマットの指定（TIMESTAMPよりDATETIMEが推奨）

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
