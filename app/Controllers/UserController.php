<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\UserIdentityModel;
use App\Models\UserModel;

class UserController extends BaseController
{

    public function index()
    {
        $userModel = model(UserModel::class);
    
        $users = $userModel
            ->withDeleted()
            ->orderBy("CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END ASC", '', false)
            ->orderBy("id ASC", '', false)
            ->findAll();
    
        return view('users/list', ['users' => $users]);
    }     

    public function create()
    {
        // ビューを表示
        return view('users/form');
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $users = model(UserModel::class);
        $userIdentityModel = new UserIdentityModel();
    
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = $this->request->getPost('role') ?? 'user'; // デフォルトで "user"
    
        // **トランザクション開始**
        $db->transStart();
    
        try {
            // **既存ユーザーのチェック**
            $existingUser = $users->where('username', $username)->withDeleted()->first();
            if ($existingUser) {
                return redirect()->route('admin.users.register')->withInput()->with('error', 'そのユーザー名はすでに使用されています。');
            }
    
            // **新規ユーザー作成**
            $userData = [
                'username' => $username,
                'email'    => $this->request->getPost('email'),
                'fullname' => $this->request->getPost('fullname'),
                'active'   => $this->request->getPost('active'),
                'last_active' => Time::now(),
            ];
    
            // **ユーザーを保存**
            if (!$users->skipValidation(true)->save($userData)) {
                throw new \Exception('ユーザー登録に失敗しました。');
            }
    
            $userId = $users->getInsertID();
            if (!$userId) {
                throw new \Exception('ユーザーIDが取得できませんでした。');
            }
    
            // **`auth_identities` に `password_hash` を登録**
            if (!$userIdentityModel->insert([
                'user_id'  => $userId,
                'type'     => 'email_password',
                'name'     => $this->request->getPost('email'),
                'secret'   => $this->request->getPost('email'), // `email` を `secret` に保存
                'secret2'  => $hashedPassword, // `password_hash` を `secret2` に保存
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ])) {
                throw new \Exception('認証情報の登録に失敗しました。');
            }
    
            // **ユーザーオブジェクトを取得**
            $user = $users->find($userId);
            if (!$user) {
                throw new \Exception("ユーザーID {$userId} の取得に失敗しました。");
            }
    
            // **ユーザーグループを設定**
            $user->id = $userId; // IDを明示的に設定
            if (!$user->syncGroups($role)) {
                throw new \Exception("ユーザーID {$userId} のグループ登録 ({$role}) に失敗しました。");
            }
            log_message('info', "ユーザーID {$userId} をグループ {$role} に登録しました。");
    
            // **トランザクションをコミット**
            $db->transCommit();
    
            return redirect()->route('admin.users.index')->with('message', 'ユーザーが登録されました。');
    
        } catch (\Exception $e) {
            // **エラー発生時にロールバック**
            $db->transRollback();
            log_message('error', "ユーザー登録エラー: " . $e->getMessage());
    
            return redirect()->route('admin.users.register')->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $users = model(UserModel::class);
        $user = $users->find($id);

        if (! $user) {
            return redirect()->route('admin.users.index')->with('error', 'ユーザーが見つかりません。');
        }

        return view('users/form', ['user' => $user]);
    }

    public function update($id)
    {
        $db = \Config\Database::connect();
        $users = model(UserModel::class);
        $userIdentityModel = new UserIdentityModel();
        $user = $users->find($id);
    
        if (!$user) {
            return redirect()->route('admin.users.index')->with('error', 'ユーザーが見つかりません。');
        }
    
        // **ログインユーザが自身の "active" を変更できないようにする**
        if (auth()->user()->id == $id) {
            if ($this->request->getPost('active') !== (string) $user->active) {
                return redirect()->back()->with('error', '自身のアカウントの有効/無効を変更することはできません。');
            }
        }
    
        // **管理者のグループ変更を禁止**
        if ($user->inGroup('admin') && $this->request->getPost('role') !== 'admin') {
            return redirect()->back()->with('error', '管理者のグループを変更することはできません。');
        }
    
        $updateData = [
            'fullname' => $this->request->getPost('fullname'),
            'email'    => $this->request->getPost('email'),
        ];
    
        // **Administrator 以外は active を更新可能**
        if ($user->username !== 'admin') {
            $updateData['active'] = $this->request->getPost('active');
        }
    
        // **ユーザー名が変更された場合のみ更新**
        $newUsername = $this->request->getPost('username');
        if ($newUsername !== $user->username) {
            // **同じ `username` が既に存在しないかチェック**
            $existingUser = $users->where('username', $newUsername)->where('id !=', $id)->first();
            if ($existingUser) {
                return redirect()->back()->withInput()->with('error', 'そのユーザー名は既に使用されています。');
            }
    
            // `username` を `updateData` に追加
            $updateData['username'] = $newUsername;
        }
    
        // **パスワード変更がある場合のみ更新**
        $newPassword = $this->request->getPost('password');
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
            // `auth_identities` の `password_hash` を更新
            $userIdentityModel
                ->where('user_id', $user->id)
                ->where('type', 'email_password')
                ->set(['secret2' => $hashedPassword])
                ->update();
        }
    
        // **トランザクション開始**
        $db->transStart();
    
        try {
            $user->fill($updateData);
    
            // **ユーザー情報の更新**
            if (!$users->save($user)) {
                throw new \Exception('ユーザー情報の更新に失敗しました。');
            }
    
            // **グループの更新（adminを除く）**
            if ($user->username !== 'admin') {
                $role = $this->request->getPost('role');
                if ($role) {
                    $user->id = $id; // IDを明示的に設定
                    if (!$user->syncGroups($role)) {
                        throw new \Exception("ユーザーID {$id} のグループ更新 ({$role}) に失敗しました。");
                    }
    
                    log_message('info', "ユーザーID {$id} のグループを {$role} に更新しました。");
                }
            }
    
            // **トランザクションをコミット**
            $db->transCommit();
    
            return redirect()->route('admin.users.index')->with('message', 'ユーザー情報が更新されました。');
    
        } catch (\Exception $e) {
            // **エラー発生時にロールバック**
            $db->transRollback();
            log_message('error', "ユーザー更新エラー: " . $e->getMessage());
    
            return redirect()->back()->withInput()->with('error', "更新に失敗しました: " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $users = model(UserModel::class);
        $user = $users->find($id);
    
        if (! $user) {
            return redirect()->route('admin.users.index')->with('error', 'ユーザーが見つかりませんでした。');
        }
    
        // **ログイン中の管理者自身を削除不可にする**
        if (auth()->user()->id == $id) {
            return redirect()->route('admin.users.index')->with('error', '自身のアカウントは削除できません。');
        }

        // 論理削除（deleted_at に現在日時をセット）
        if ($users->delete($id)) {
            return redirect()->route('admin.users.index')->with('message', 'ユーザーが正常に削除されました。');
        } else {
            return redirect()->route('admin.users.index')->with('error', 'ユーザーの削除に失敗しました。');
        }
    }
    
    public function restore($id)
    {
        $UserModel = new UserModel();
        $user = $UserModel->onlyDeleted()->find($id);

        if (!$user) {
            return redirect()->route('admin.users.index')->with('error', 'リソースが見つかりません。');
        }

        $UserModel->update($id, ['deleted_at' => null]);

        return redirect()->route('admin.users.index')->with('message', 'リソースが復元されました。');
    }
}
