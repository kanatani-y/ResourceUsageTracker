<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Shield\Entities\User;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;

class CreateAdminUser extends Migration
{
    public function up()
    {
        $users = model(UserModel::class);

        // 管理者ユーザーを作成
        $adminData = [
            'username' => 'admin',
            'fullname' => 'Administrator',
            'email'    => 'admin@example.com',
            'password' => 'admin', // 初期パスワード
            'active'   => 1, // 有効化
        ];

        // ユーザーが既に存在しない場合のみ作成
        $existingAdmin = $users->where('username', 'admin')->first();
        if (!$existingAdmin) {
            $adminUser = new User($adminData);
            $users->save($adminUser);

            // 保存したユーザーのIDを取得
            $adminId = $users->getInsertID();
            log_message('debug', "Admin User ID: {$adminId}");

            // ユーザーをデータベースから再取得
            $adminUser = $users->findById($adminId);
            if ($adminUser) {
                // `addGroup()` を使用
                $adminUser->addGroup('admin');
                log_message('debug', "Admin added to group: admin");
            }
        }
    }

    public function down()
    {
        // ロールバック時に管理者アカウントを削除
        $users = model(UserModel::class);
        $adminUser = $users->where('username', 'admin')->first();

        if ($adminUser) {
            $adminId = $adminUser->id;
            $adminUser->removeGroup('admin'); // `removeGroup()` を使用
            $users->where('id', $adminId)->delete();
        }
    }
}
