<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class AuthController extends Controller
{

    public function login()
    {
        if (auth()->loggedIn()) {
            return redirect()->route('home'); // 既にログイン済みならホームへ
        }
    
        if ($this->request->getMethod() === 'post') {
            $credentials = [
                'email'    => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
            ];
    
            $auth = auth('session')->getAuthenticator();
            $result = $auth->attempt($credentials);
    
            if (! $result->isOK()) {
                return redirect()->route('login')->withInput()->with('error', $result->reason());
            }
    
            return redirect()->route('home');
        }
    
        return view('Auth/login');
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
