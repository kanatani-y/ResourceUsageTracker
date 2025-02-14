<?php

namespace App\Models;

use CodeIgniter\Model;

class ResourceModel extends Model
{
    protected $table = 'resources'; // テーブル名
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name', 
        'hostname',
        'type', 
        'os', 
        'ip_address', 
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

    protected $dateFormat = 'datetime'; // 日付フォーマットの指定

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // **バリデーションルール**
    protected $validationRules = [
        'name' => [
            'rules' => 'required|max_length[100]',
            'label' => 'リソース名'
        ],
        'hostname' => [
            'rules' => 'required|max_length[255]|regex_match[/^[a-zA-Z0-9.-]+$/]|is_unique[resources.hostname]',
            'label' => 'ホスト名',
            'errors' => [
                'regex_match' => 'ホスト名は英数字、ドット、ハイフンのみ使用できます。',
            ]
        ],
        'type' => [
            'rules' => 'required|in_list[server,network,storage,other]',
            'label' => 'リソース種別',
            'errors' => [
                'in_list' => 'リソース種別は「server, network, storage, other」のいずれかを選択してください。',
            ]
        ],
        'os' => [
            'rules' => 'permit_empty|max_length[255]',
            'label' => 'OS'
        ],
        'ip_address' => [
            'rules' => 'permit_empty|valid_ip',
            'label' => 'IPアドレス'
        ],
        'cpu' => [
            'rules' => 'permit_empty|integer|greater_than[0]',
            'label' => 'CPU コア数'
        ],
        'memory' => [
            'rules' => 'permit_empty|integer|greater_than[0]',
            'label' => 'メモリ (GB)'
        ],
        'storage' => [
            'rules' => 'permit_empty|integer|greater_than[0]',
            'label' => 'ストレージ (GB)'
        ],
        'status' => [
            'rules' => 'permit_empty|in_list[active,inactive]',
            'label' => 'ステータス',
            'errors' => [
                'in_list' => 'ステータスは「active」または「inactive」のみ選択できます。',
            ]
        ],
        'location' => [
            'rules' => 'permit_empty|max_length[255]',
            'label' => '設置場所'
        ],
        'description' => [
            'rules' => 'permit_empty|max_length[500]',
            'label' => '説明'
        ]
    ];
}
