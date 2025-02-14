<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected $allowedFields = [
        'username',
        'fullname',
        'active',
    ];

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
