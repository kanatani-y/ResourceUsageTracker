<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class AuthController extends Controller
{

    public function login()
    {
        if (auth()->loggedIn()) {
            return redirect()->route('reservation.schedule'); // 既にログイン済みならホームへ
        }
    
        if ($this->request->getMethod() === 'post') {
            $credentials = [
                'username'    => $this->request->getPost('username'),
                'password' => $this->request->getPost('password'),
            ];
    
            $auth = auth('session')->getAuthenticator();
            $result = $auth->attempt($credentials);
    
            if (! $result->isOK()) {
                return redirect()->route('login')->withInput()->with('error', $result->reason());
            }
    
            return redirect()->route('reservation.schedule');
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

        return redirect()->route('reservation.schedule')->with('message', 'ゲストとしてログインしました。');
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
