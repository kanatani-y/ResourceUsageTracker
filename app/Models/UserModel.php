<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected $allowedFields = [
        'username',
        'fullname',
        'active',
        'last_active',
        'created_at', 
        'updated_at', 
        'deleted_at',
    ];

    protected $useTimestamps = true; // 自動的に `created_at`, `updated_at` を管理
    protected $useSoftDeletes = true; // `deleted_at` をソフトデリートとして利用

    protected $dateFormat = 'datetime'; // 日付フォーマットの指定

    // **バリデーションルール**
    protected $validationRules = [
        'fullname' => [
            'rules'  => 'required|max_length[60]|regex_match[/^[^\s ]+[ ][^\s ]+$/u]',
            'label'  => '氏名',
            'errors' => [
                'regex_match' => '姓と名の間に半角スペースを1つ入れてください。',
            ],
        ],
        'email' => [
            'rules' => 'required|valid_email|max_length[255]',
            'label' => 'メールアドレス',
        ],
        'current_password' => [
            'rules' => 'required',
            'label' => '現在のパスワード',
        ],
        'new_password' => [
            'rules' => 'permit_empty|min_length[4]',
            'label' => '新しいパスワード',
        ],
        'new_password_confirm' => [
            'rules' => 'permit_empty|matches[new_password]',
            'label' => '新しいパスワード（確認）',
        ],
    ];

    // バリデーションを自動適用
    protected $skipValidation = false;
}
