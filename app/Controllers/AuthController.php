<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class AuthController extends Controller
{

    public function login()
    {
        if (auth()->loggedIn()) {
            return redirect()->route('reservations.schedule'); // 既にログイン済みならリダイレクト
        }
        
        if ($this->request->getMethod() === 'POST') {
            $credentials = [
                'username' => $this->request->getPost('username'),
                'password' => $this->request->getPost('password'),
            ];
    
            $auth = auth('session')->getAuthenticator();
    
            // **ユーザー情報を取得（active = 1 のみ）**
            $userModel = model(\App\Models\UserModel::class);
            $user = $userModel->where('username', $credentials['username'])
                              ->where('active', 1) // 無効ユーザーをブロック
                              ->first();
            if (!$user) {
                return redirect()->route('login')->withInput()->with('error', '無効なユーザーです。');
            }
    
            // **認証を実行**
            $result = $auth->attempt($credentials);
    
            if (! $result->isOK()) {
                return redirect()->route('login')->withInput()->with('error', $result->reason());
            }
    
            return redirect()->route('reservations.schedule');
        }
    
        return view('Auth/login');
    }
    
    
    public function guestLogin()
    {
        $auth = service('authentication');

        // **現在のログインセッションを削除**
        if (auth()->loggedIn()) {
            auth()->logout();
            session()->destroy();
        }

        // ゲストユーザー情報（デモ用）
        $credentials = [
            'username' => 'guest',
            'password' => 'guest123' // 事前に設定されたパスワード
        ];

        $auth = auth('session')->getAuthenticator();
        $result = $auth->attempt($credentials);

        if (! $result->isOK()) {
            return redirect()->route('login')->withInput()->with('error', $result->reason());
        }

        return redirect()->route('reservations.schedule')->with('message', 'ゲストとしてログインしました。');
    }

    public function logout()
    {
        $auth = service('auth');
    
        if ($auth->loggedIn()) {
            $auth->logout();
        }
    
        return redirect()->route('login')->with('message', 'ログアウトしました。');
    }
}
