<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Shield\Entities\User;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Models\UserIdentityModel;

class CreateAdminUser extends Migration
{
    public function up()
    {
        $users = model(UserModel::class);
        $userIdentityModel = model(UserIdentityModel::class);

        // 管理者ユーザーを作成
        $adminData = [
            'username' => 'admin',
            'fullname' => 'Administrator',
            'email'    => 'admin@example.com',
            'active'   => 1, // 有効化
        ];

        // 初期パスワード（`password_hash()` を適用）
        $plainPassword = 'admin';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        // ユーザーが既に存在しない場合のみ作成
        $existingAdmin = $users->where('username', 'admin')->first();
        if (!$existingAdmin) {
            // `users` テーブルに管理者ユーザーを作成
            $users->skipValidation(true)->save($adminData);

            // 保存したユーザーのIDを取得
            $adminId = $users->getInsertID();
            log_message('debug', "Admin User ID: {$adminId}");

            // `auth_identities` に `email_password` のエントリーを追加
            $userIdentityModel->insert([
                'user_id'   => $adminId,
                'type'      => 'email_password',
                'name'      => 'admin@example.com',
                'secret'    => 'admin@example.com', // メールアドレス
                'secret2'   => $hashedPassword, // ハッシュ化されたパスワード
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ]);

            // ユーザーを `admin` グループに追加
            $adminUser = $users->find($adminId);
            if ($adminUser) {
                $adminUser->addGroup('admin');
                log_message('debug', "Admin added to group: admin");
            }
        }
    }

    public function down()
    {
        $users = model(UserModel::class);
        $userIdentityModel = model(UserIdentityModel::class);

        // `users` テーブルから `admin` を削除
        $adminUser = $users->where('username', 'admin')->first();
        if ($adminUser) {
            $adminId = $adminUser->id;

            // `auth_identities` から `admin` のエントリーを削除
            $userIdentityModel->where('user_id', $adminId)->delete();

            // `users` テーブルから `admin` を削除
            $adminUser->removeGroup('admin');
            $users->delete($adminId);
        }
    }
}
