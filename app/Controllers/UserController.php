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
    
        return view('admin/user_list', ['users' => $users]);
    }     

    public function create()
    {
        // ビューを表示
        return view('admin/user_form');
    }

    public function store()
    {
        $users = model(UserModel::class);
        $userIdentityModel = new UserIdentityModel(); // auth_identities の操作用
        $username = $this->request->getPost('username');
    
        // **既存ユーザーのチェック**
        $existingUser = $users->where('username', $username)->withDeleted()->first();
        if ($existingUser) {
            return redirect()->route('admin.register')->withInput()->with('error', 'そのユーザー名はすでに使用されています。');
        }
    
        // **パスワードのハッシュ化**
        $password = $this->request->getPost('password');
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
        // **新規ユーザー作成**
        $userData = [
            'username' => $username,
            'email'    => $this->request->getPost('email'),
            'fullname' => $this->request->getPost('fullname'),
            'active'   => $this->request->getPost('active'),
            'last_active' => Time::now(),
        ];
    
        // **ユーザーを保存**
        $users->skipValidation(true)->save($userData);
        $userId = $users->getInsertID(); // 保存後の `user_id` を取得
    
        if (!$userId) {
            return redirect()->route('admin.register')->withInput()->with('error', 'ユーザー登録に失敗しました。');
        }
    
        // **`auth_identities` に `password_hash` を登録**
        $userIdentityModel->insert([
            'user_id'  => $userId,
            'type'     => 'email_password',
            'name'     => $this->request->getPost('email'),
            'secret'   => $this->request->getPost('email'), // `email` を `secret` に保存
            'secret2'  => $hashedPassword, // `password_hash` を `secret2` に保存
            'created_at' => Time::now(),
            'updated_at' => Time::now(),
        ]);
    
        return redirect()->route('admin.users.index')->with('message', 'ユーザーが登録されました。');
    }

    public function edit($id)
    {
        $users = model(UserModel::class);
        $user = $users->find($id);

        if (! $user) {
            return redirect()->route('admin.users.index')->with('error', 'ユーザーが見つかりません。');
        }

        return view('admin/user_form', ['user' => $user]);
    }

    public function update($id)
    {
        $users = model(UserModel::class);
        $user = $users->find($id);

        if (! $user) {
            return redirect()->route('admin.users.index')->with('error', 'ユーザーが見つかりません。');
        }

        // **Administrator の場合は role と active を変更不可にする**
        if ($user->username === 'admin') {
            if ($this->request->getPost('role') !== 'admin' || $this->request->getPost('active') !== (string) $user->active) {
                return redirect()->back()->with('error', 'Administrator の役割やアカウント状態は変更できません。');
            }
        }

        // 更新データを作成
        $updateData = [
            'fullname' => $this->request->getPost('fullname'),
        ];

        // **Administrator 以外は role と active を更新可能**
        if ($user->username !== 'admin') {
            $updateData['role'] = $this->request->getPost('role');
            $updateData['active'] = $this->request->getPost('active');
        }

        // パスワード変更がある場合のみ更新
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $updateData['password'] = $password;
        }

        $user->fill($updateData);

        if (! $users->save($user)) {
            return redirect()->back()->withInput()->with('errors', $users->errors());
        }

        // 役割を更新（Administrator 以外のみ）
        if ($user->username !== 'admin') {
            $role = $this->request->getPost('role');
            if ($role) {
                $user->syncGroups($role);
            }
        }

        return redirect()->route('admin.users.index')->with('message', 'ユーザー情報が更新されました。');
    }

    public function delete($id)
    {
        $users = model(UserModel::class);
        $user = $users->find($id);
    
        if (! $user) {
            return redirect()->route('admin.users.index')->with('error', 'ユーザーが見つかりませんでした。');
        }
    
        // 論理削除（deleted_at に現在日時をセット）
        if ($users->delete($id)) {
            return redirect()->route('admin.users.index')->with('message', 'ユーザーが正常に削除されました。');
        } else {
            return redirect()->route('admin.users.index')->with('error', 'ユーザーの削除に失敗しました。');
        }
    }
    
}
