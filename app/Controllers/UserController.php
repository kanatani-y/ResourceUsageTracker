<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use App\Controllers\BaseController;
use CodeIgniter\Shield\Entities\User;
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
    $username = $this->request->getPost('username');

    // すでに登録されているかチェック
    $existingUser = $users->where('username', $username)->withDeleted()->first();

    if ($existingUser) {
        // すでに存在する場合はエラー
        return redirect()->route('admin.register')->withInput()->with('error', 'そのユーザー名はすでに使用されています。');
    }

    // 新規登録
    $user = new User([
        'username' => $username,
        'email'    => $this->request->getPost('email'),
        'fullname' => $this->request->getPost('fullname'),
        'password' => $this->request->getPost('password'),
        'active'   => $this->request->getPost('active'),
        'last_active' => Time::now(),
    ]);

    $users->save($user);

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
