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
            ->orderBy('status', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('resources/list', ['resources' => $resources]);
    }

    public function show($id)
    {
        helper('EncryptionHelper'); // ヘルパーをロード

        $resourceModel = new ResourceModel();
        $accountModel = new AccountModel();
    
        $resource = $resourceModel->withDeleted()->where('id', $id)->first();
    
        if (!$resource) {
            return redirect()->route('resources.index')->with('error', 'リソースが見つかりません。');
        }
    
        $accounts = $accountModel->where('resource_id', $id)
            ->orderBy('status', 'ASC')
            ->orderBy('account_name', 'ASC')
            ->findAll();
    
        return view('resources/show', [
            'resource' => $resource,
            'accounts' => $accounts,
        ]);
    }
    

    public function create()
    {
        return view('resources/form');
    }

    public function store()
    {
        $ResourceModel = new ResourceModel();
    
        // **ホスト名の重複チェック**
        $existingResource = $ResourceModel->where('hostname', $this->request->getPost('hostname'))->first();
        if ($existingResource) {
            return redirect()->route('resources.create')
                ->withInput()
                ->with('error', 'ホスト名がすでに使用されています。');
        }
    
        // **入力データの準備**
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
    
        // **バリデーションチェック**
        if (!$this->validate($ResourceModel->validationRules)) {
            return redirect()->route('resources.create')
                ->withInput()
                ->with('error', implode(', ', $this->validator->getErrors()));
        }
    
        try {
            // **データベースへの登録**
            $insertId = $ResourceModel->insert($data, true); // 成功すれば ID を取得、失敗なら false
            if (!$insertId) {
                throw new \Exception('データベースエラー: 登録失敗');
            }
    
            return redirect()->route('resources.index')
                ->with('message', 'リソースが登録されました。');
    
        } catch (\Exception $e) {
            return redirect()->route('resources.create')
                ->withInput()
                ->with('error', 'リソースの登録に失敗しました: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $ResourceModel = new ResourceModel();
        $resource = $ResourceModel->withDeleted()->find($id);

        if (!$resource) {
            return redirect()->route('resources.index')->with('error', 'リソースが見つかりません。');
        }

        return view('resources/form', ['resource' => $resource]);
    }

    public function update($id)
    {
        $ResourceModel = new ResourceModel();
    
        // **リソースの存在チェック**
        $resource = $ResourceModel->withDeleted()->find($id);
        if (!$resource) {
            return redirect()->route('resources.index')->with('error', 'リソースが見つかりません。');
        }
    
        // **ホスト名の変更がある場合のみ、一意性チェック**
        $newHostname = $this->request->getPost('hostname');
        if ($newHostname !== $resource['hostname']) {
            $existingResource = $ResourceModel
                ->where('hostname', $newHostname)
                ->where('id !=', $id)
                ->first();
    
            if ($existingResource) {
                return redirect()->route('resources.edit', [$id])
                    ->withInput()
                    ->with('error', 'ホスト名がすでに使用されています。');
            }
        }
    
        // **更新データの準備**
        $data = [
            'name'        => $this->request->getPost('name'),
            'hostname'    => $newHostname,
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
    
        // **バリデーションチェック**
        if (!$this->validate($ResourceModel->validationRules)) {
            return redirect()->route('resources.edit', [$id])
                ->withInput()
                ->with('error', implode(', ', $this->validator->getErrors()));
        }
    
        try {
            // **更新処理の実行**
            $success = $ResourceModel->update($id, $data);
            if (!$success) {
                throw new \Exception('データベースエラー: 更新失敗');
            }
    
            return redirect()->route('resources.index')
                ->with('message', 'リソース情報が更新されました。');
    
        } catch (\Exception $e) {
            return redirect()->route('resources.edit', [$id])
                ->withInput()
                ->with('error', '更新に失敗しました: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $ResourceModel = new ResourceModel();
        $resource = $ResourceModel->find($id);
    
        if (!$resource) {
            return redirect()->route('resources.index')->with('error', 'リソースが見つかりません。');
        }
    
        // **リソースのステータスが "retired"（廃止）でない場合は削除不可**
        if ($resource['status'] !== 'retired') {
            return redirect()->route('resources.index')->with('error', '廃止されたリソースのみ削除できます。');
        }
    
        // **削除処理（論理削除）**
        $ResourceModel->delete($id);
    
        return redirect()->route('resources.index')->with('message', 'リソースが削除されました。');
    }
    

    public function restore($id)
    {
        $ResourceModel = new ResourceModel();
        
        // **削除済みのリソースを取得**
        $resource = $ResourceModel->onlyDeleted()->find($id);

        if (!$resource) {
            return redirect()->route('resources.index')->with('error', 'リソースが見つかりません。');
        }
    
        // **復元処理**
        $resource['deleted_at'] = null;
        $ResourceModel->save($resource);
    
        return redirect()->route('resources.index')->with('message', 'リソースが復元されました。');
    }
    
}
