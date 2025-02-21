<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Models\ResourceModel;

class AccountController extends BaseController
{
    public function index($resource_id = null)
    {
        $accountModel = new AccountModel();
        $resourceModel = new ResourceModel();
    
        // リソースIDが指定された場合、リソース情報を取得
        $resource = $resource_id ? $resourceModel->find($resource_id) : null;
    
        if ($resource_id && !$resource) {
            return redirect()->route('resources.index')->with('error', 'リソースが見つかりません。');
        }
    
        // アカウント取得
        if (isset($resource)) {
            $accounts = $accountModel->where('resource_id', $resource_id)
                ->orderBy("CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END ASC", '', false)
                ->orderBy('id', 'ASC')
                ->findAll();
        } else {
            $accounts = $accountModel->select('accounts.*, resources.name as resource_name')
                ->join('resources', 'resources.id = accounts.resource_id', 'left')
                ->orderBy("CASE WHEN accounts.deleted_at IS NULL THEN 0 ELSE 1 END ASC", '', false)
                ->orderBy('accounts.id', 'ASC')
                ->findAll();
        }
    
        return view('account/list', [
            'resource' => $resource,
            'accounts' => $accounts,
        ]);
    }

    public function create($resource_id = null)
    {
        $resourceModel = new ResourceModel();
        $resources = $resourceModel->orderBy('name', 'ASC')->findAll();
    
        $selectedResource = null;
        if ($resource_id) {
            $selectedResource = $resourceModel->find($resource_id);
        }
    
        return view('account/form', [
            'resource_id'       => $resource_id,
            'resources'         => $resources,
            'selectedResource'  => $selectedResource, // 明示的に変更
        ]);
    }

    public function store()
    {
        $accountModel = new AccountModel();
    
        // **バリデーション**
        if (!$accountModel->validate($this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $accountModel->errors());
        }
    
        // **パスワードをエンコードして格納**
        $password = $this->request->getPost('password');
        $encodedPassword = !empty($password) ? base64url_encode($password) : null;
    
        $data = [
            'resource_id'     => $this->request->getPost('resource_id'),
            'username'        => $this->request->getPost('username'),
            'password'        => $encodedPassword, // **明示的にエンコード**
            'connection_type' => $this->request->getPost('connection_type'),
            'port'            => $this->request->getPost('port') !== '' ? $this->request->getPost('port') : -1,
            'description'     => $this->request->getPost('description'),
            'status'          => $this->request->getPost('status') ?? 'available',
        ];
    
        $accountModel->insert($data);
    
        return redirect()->to(route_to('accounts.index', $data['resource_id']))
                        ->with('message', 'アカウントが登録されました。');
    }
    
    public function edit($id)
    {
        $accountModel = new AccountModel();
        $resourceModel = new ResourceModel();
    
        $account = $accountModel->find($id);
        if (!$account) {
            return redirect()->route('accounts.index')->with('error', 'アカウントが見つかりません。');
        }
    
        $resources = $resourceModel->orderBy('name', 'ASC')->findAll();
        $selectedResource = $resourceModel->find($account['resource_id']);
    
        return view('account/form', [
            'account' => $account,
            'resources' => $resources,
            'selectedResource' => $selectedResource
        ]);
    }
    

    public function update($id)
    {
        $accountModel = new AccountModel();
        $account = $accountModel->find($id);
    
        if (!$account) {
            return redirect()->back()->with('error', 'アカウントが見つかりません。');
        }
    
        $data = [
            'resource_id'     => $this->request->getPost('resource_id'),
            'username'        => $this->request->getPost('username'),
            'connection_type' => $this->request->getPost('connection_type'),
            'port'            => $this->request->getPost('port') !== '' ? $this->request->getPost('port') : -1, 
            'description'     => $this->request->getPost('description'),
            'status'          => $this->request->getPost('status'),
        ];
    
        // パスワード変更があれば更新
        if ($this->request->getPost('password')) {
            $data['password'] = base64url_encode($this->request->getPost('password')); // エンコード
        }
    
        $accountModel->update($id, $data);
    
        return redirect()->to(route_to('accounts.index', $account['resource_id']))
                        ->with('message', 'アカウントが更新されました。');
    }

    public function delete($id)
    {
        $accountModel = new AccountModel();
        $account = $accountModel->find($id);

        if (!$account) {
            return redirect()->back()->with('error', 'アカウントが見つかりません。');
        }

        $updateData = ['deleted_at' => date('Y-m-d H:i:s')];

        if ($accountModel->update($id, $updateData)) {
            return redirect()->back()->with('message', 'アカウントが削除されました。');
        }

        return redirect()->back()->with('error', 'アカウントの削除に失敗しました。');
    }
}
