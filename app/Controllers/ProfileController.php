<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\Shield\Models\UserIdentityModel;

class ProfileController extends BaseController
{
    public function settings()
    {
        return view('profile/settings', ['user' => auth()->user()]);
    }

    public function update()
    {
        $userModel = new UserModel();
        $userIdentityModel = new UserIdentityModel();
        $user = $userModel->find(auth()->id());

        if (!$user) {
            return redirect()->back()->with('errors', ['current_password' => 'ユーザー情報が取得できませんでした。']);
        }

        // `password_hash` を取得
        $passwordIdentity = $userIdentityModel
            ->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->first();

        if (!$passwordIdentity || empty($passwordIdentity->secret2)) {
            return redirect()->back()->with('errors', ['current_password' => 'パスワード情報が取得できませんでした。']);
        }

        // **バリデーションチェック**
        if (!$userModel->validate($this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        // **パスワードチェック**
        if (!password_verify($this->request->getPost('current_password'), $passwordIdentity->secret2)) {
            return redirect()->back()->withInput()->with('errors', ['current_password' => '現在のパスワードが違います。']);
        }

        // **更新データの準備**
        $data = [];

        if ($this->request->getPost('fullname') !== $user->fullname) {
            $data['fullname'] = $this->request->getPost('fullname');
        }

        if ($this->request->getPost('email') !== $user->email) {
            $data['email'] = $this->request->getPost('email');
        }

        // **パスワード変更**
        if ($this->request->getPost('new_password')) {
            $userIdentityModel
                ->where('user_id', $user->id)
                ->where('type', 'email_password')
                ->set(['secret2' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT)])
                ->update();
        }

        // **ユーザー情報を更新**
        if (!empty($data)) {
            $userModel->update($user->id, $data);
        }

        return redirect()->route('profile.settings')->with('message', 'プロフィールが更新されました。');
    }
}
