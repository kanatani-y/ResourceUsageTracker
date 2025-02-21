<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountModel extends Model
{
    protected $table      = 'accounts';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'resource_id',
        'username',
        'password',
        'connection_type',
        'port',
        'status',
        'description',
        'created_at', 
        'updated_at', 
        'deleted_at'
    ];

    // **バリデーションルール**
    protected $validationRules = [
        'resource_id' => [
            'rules' => 'required|integer',
            'label' => 'リソースID'
        ],
        'username' => [
            'rules' => 'required|max_length[255]|regex_match[/^[a-zA-Z0-9._@-]+$/]',
            'label' => 'ユーザー名',
            'errors' => [
                'regex_match' => 'ユーザー名は英数字、ドット、アンダースコア、ハイフン、@ のみ使用できます。',
            ]
        ],
        'password' => [
            'rules' => 'permit_empty|min_length[4]|max_length[255]',
            'label' => 'パスワード',
        ],
        'connection_type' => [
            'rules' => 'required|in_list[SSH,RDP,FTP,OTH]',
            'label' => '接続方式',
            'errors' => [
                'in_list' => '接続方式は「SSH, RDP, FTP, その他」のいずれかを選択してください。',
            ]
        ],
        'port' => [
            'rules' => 'permit_empty|integer|greater_than_equal_to[-1]|less_than_equal_to[65535]',
            'label' => 'ポート番号',
            'errors' => [
                'greater_than_equal_to' => 'ポート番号は -1（未指定） または 1～65535 の数値で入力してください。',
            ]
        ],
        'status' => [
            'rules' => 'required|in_list[available,restricted,retired]',
            'label' => 'ステータス',
            'errors' => [
                'in_list' => 'ステータスは「利用可能, 利用禁止, 廃止」のいずれかを選択してください。',
            ]
        ],
        'description' => [
            'rules' => 'permit_empty|max_length[500]',
            'label' => '備考'
        ]
    ];

    // **パスワードを保存するときに Base64URL エンコード**
    protected function beforeInsert(array $data)
    {
        return $this->encodePassword($data);
    }

    protected function beforeUpdate(array $data)
    {
        return $this->encodePassword($data);
    }

    private function encodePassword(array $data)
    {
        if (!empty($data['data']['password'])) {
            $data['data']['password'] = base64url_encode($data['data']['password']);
        }
        return $data;
    }
}
