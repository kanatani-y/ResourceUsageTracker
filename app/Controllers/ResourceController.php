<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ResourceModel;
use App\Models\AccountModel;

class ResourceController extends BaseController
{
    public function index()
    {
        $ResourceModel = new ResourceModel();

        // 削除済みのリソースも含めて取得し、削除済みを最下部に表示
        $resources = $ResourceModel->withDeleted()
            ->orderBy("CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END ASC", '', false)
            ->orderBy("id ASC", '', false)
            ->findAll();

        return view('resource/resource_list', ['resources' => $resources]);
    }

    public function show($id)
    {
        helper('EncryptionHelper'); // ヘルパーをロード

        $resourceModel = new ResourceModel();
        $accountModel = new AccountModel();
    
        $resource = $resourceModel->find($id);
    
        if (!$resource) {
            return redirect()->route('resource.index')->with('error', 'リソースが見つかりません。');
        }
    
        $accounts = $accountModel->where('resource_id', $id)
            ->orderBy("CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END ASC", '', false)
            ->orderBy('id', 'ASC')
            ->findAll();
    
        return view('resource/resource_show', [
            'resource' => $resource,
            'accounts' => $accounts,
        ]);
    }
    

    public function create()
    {
        return view('resource/resource_form');
    }

    public function store()
    {
        $ResourceModel = new ResourceModel();

        // ホスト名の重複チェック
        $existingResource = $ResourceModel->where('hostname', $this->request->getPost('hostname'))->first();
        if ($existingResource) {
            return redirect()->route('resource.create')->withInput()->with('error', 'ホスト名がすでに使用されています。');
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'hostname'    => $this->request->getPost('hostname'),
            'type'        => $this->request->getPost('type'),
            'os'          => $this->request->getPost('os'),
            'ip_address'  => $this->request->getPost('ip_address'),
            'cpu'         => $this->request->getPost('cpu'),
            'memory'      => $this->request->getPost('memory'),
            'storage'     => $this->request->getPost('storage'),
            'status'      => $this->request->getPost('status'),
            'location'    => $this->request->getPost('location'),
            'description' => $this->request->getPost('description'),
        ];

        $ResourceModel->insert($data);

        return redirect()->route('resource.index')->with('message', 'リソースが登録されました。');
    }

    public function edit($id)
    {
        $ResourceModel = new ResourceModel();
        $resource = $ResourceModel->withDeleted()->find($id);

        if (!$resource) {
            return redirect()->route('resource.index')->with('error', 'リソースが見つかりません。');
        }

        return view('resource/resource_form', ['resource' => $resource]);
    }

    public function update($id)
    {
        $ResourceModel = new ResourceModel();

        $resource = $ResourceModel->withDeleted()->find($id);
        if (!$resource) {
            return redirect()->route('resource.index')->with('error', 'リソースが見つかりません。');
        }

        // ホスト名の一意性チェック
        $existingResource = $ResourceModel->where('hostname', $this->request->getPost('hostname'))
        ->where('id !=', $id)
        ->first();

        if ($existingResource) {
            return redirect()->route('resource.edit', $id)->withInput()->with('error', 'ホスト名がすでに使用されています。');
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'hostname'    => $this->request->getPost('hostname'),
            'type'        => $this->request->getPost('type'),
            'os'          => $this->request->getPost('os'),
            'ip_address'  => $this->request->getPost('ip_address'),
            'cpu'         => $this->request->getPost('cpu'),
            'memory'      => $this->request->getPost('memory'),
            'storage'     => $this->request->getPost('storage'),
            'status'      => $this->request->getPost('status'),
            'location'    => $this->request->getPost('location'),
            'description' => $this->request->getPost('description'),
        ];

        $ResourceModel->update($id, $data);

        return redirect()->route('resource.index')->with('message', 'リソース情報が更新されました。');
    }

    public function delete($id)
    {
        $ResourceModel = new ResourceModel();
        $resource = $ResourceModel->find($id);

        if (!$resource) {
            return redirect()->route('resource.index')->with('error', 'リソースが見つかりません。');
        }

        $ResourceModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);

        return redirect()->route('resource.index')->with('message', 'リソースが削除されました。');
    }

    public function restore($id)
    {
        $ResourceModel = new ResourceModel();
        $resource = $ResourceModel->onlyDeleted()->find($id);

        if (!$resource) {
            return redirect()->route('resource.index')->with('error', 'リソースが見つかりません。');
        }

        $ResourceModel->update($id, ['deleted_at' => null]);

        return redirect()->route('resource.index')->with('message', 'リソースが復元されました。');
    }
}
