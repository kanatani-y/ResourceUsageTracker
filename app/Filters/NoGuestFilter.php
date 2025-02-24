<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Myth\Auth\Models\GroupModel;

class NoGuestFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('authentication');

        // **未ログインのユーザーはリダイレクト**
        if (!$auth->check()) {
            return redirect()->route('home')->with('error', 'アクセス権限がありません。');
        }

        // **ユーザーのグループを取得**
        $userId = $auth->id();
        $groupModel = new GroupModel();
        $userGroups = $groupModel->getGroupsForUser($userId);

        // **ユーザーが "guest" グループのみの場合はアクセス拒否**
        if (in_array('guest', $userGroups) && count($userGroups) === 1) {
            return redirect()->route('home')->with('error', 'アクセス権限がありません。');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 何もしない
    }
}
