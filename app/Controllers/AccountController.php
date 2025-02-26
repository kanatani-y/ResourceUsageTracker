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
            return redirect()->to(site_url('resources'))->with('error', 'リソースが見つかりません。');
        }
    
        if ($resource) {
            $accounts = $accountModel->withDeleted()->where('resource_id', $resource_id)
                ->orderBy("CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END ASC", '', false)
                ->orderBy('accounts.active', 'DESC')
                ->orderBy('accounts.account_name', 'ASC')
                ->findAll();
        } else {
            $accounts = $accountModel->withDeleted()->select('accounts.*, resources.name as resource_name, resources.type as resource_type')
                ->join('resources', 'resources.id = accounts.resource_id', 'left')
                ->orderBy("CASE WHEN accounts.deleted_at IS NULL THEN 0 ELSE 1 END ASC", '', false)
                ->orderBy('accounts.active', 'DESC')
                ->orderBy('resources.name', 'ASC')
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
    
        $selectedResource = $resource_id ? $resourceModel->find($resource_id) : null;
    
        return view('account/form', [
            'resource_id'       => $resource_id,
            'resources'         => $resources,
            'selectedResource'  => $selectedResource,
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
            'account_name'        => $this->request->getPost('account_name'),
            'password'        => $encodedPassword,
            'connection_type' => $this->request->getPost('connection_type'),
            'port'            => $this->request->getPost('port') !== '' ? $this->request->getPost('port') : -1,
            'description'     => $this->request->getPost('description'),
            'active'          => $this->request->getPost('active') ?? 1,
        ];
    
        $accountModel->insert($data);
    
        return redirect()->to(site_url('accounts/'))->with('message', 'アカウントが登録されました。');
    }

    public function edit($id)
    {
        $accountModel = new AccountModel();
        $resourceModel = new ResourceModel();
    
        $account = $accountModel->find($id);
        if (!$account) {
            return redirect()->to(site_url('accounts'))->with('error', 'アカウントが見つかりません。');
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
            'account_name'        => $this->request->getPost('account_name'),
            'connection_type' => $this->request->getPost('connection_type'),
            'port'            => $this->request->getPost('port') !== '' ? $this->request->getPost('port') : -1, 
            'description'     => $this->request->getPost('description'),
            'active'          => $this->request->getPost('active'),
        ];
    
        // パスワード変更があれば更新
        if ($this->request->getPost('password')) {
            $data['password'] = base64url_encode($this->request->getPost('password'));
        }
    
        $accountModel->update($id, $data);
    
        return redirect()->to(site_url('accounts'))->with('message', 'アカウントが更新されました。');
    }

    public function delete($id)
    {
        $accountModel = new AccountModel();
        $account = $accountModel->find($id);
    
        if (!$account) {
            return redirect()->to(site_url('accounts'))->with('error', 'アカウントが見つかりません。');
        }
    
        // **管理者のみ削除可能**
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to(site_url('accounts'))->with('error', '操作の権限がありません。');
        }
    
        // **リソースのステータスが 0:無効 でない場合は削除不可**
        if ($account['active'] != 0) {
            return redirect()->to(site_url('accounts'))->with('error', '無効のアカウントのみ削除できます。');
        }
    
        // **削除処理（論理削除）**
        $accountModel->delete($id);
    
        return redirect()->to(site_url('accounts'))->with('message', 'アカウントが削除されました。');
    }

    public function restore($id)
    {
        $accountModel = new AccountModel();
        
        // **削除済みのリソースを取得**
        $account = $accountModel->onlyDeleted()->find($id);

        if (!$account) {
            return redirect()->to(site_url('accounts'))->with('error', 'アカウントが見つかりません。');
        }
    
        // **管理者のみ復元可能**
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to(site_url('accounts'))->with('error', '操作の権限がありません。');
        }
    
        // **復元処理**
        $account['deleted_at'] = null;
        $accountModel->save($account);
    
        return redirect()->to(site_url('accounts'))->with('message', 'アカウントが復元されました。');
    }
}
