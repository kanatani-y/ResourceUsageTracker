<?php

namespace App\Controllers\Resource;

use App\Controllers\BaseController;
use App\Models\ResourceModel;

class ResourceController extends BaseController
{
    public function index()
    {
        $ResourceModel = new ResourceModel();
        $resources = $ResourceModel->where('deleted_at', null)->findAll();

        return view('Resource/Resource_list', ['resources' => $resources]);
    }

    public function create()
    {
        return view('Resource/Resource_form');
    }

    public function store()
    {
        $ResourceModel = new ResourceModel();

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status', FILTER_SANITIZE_STRING),
        ];

        $ResourceModel->insert($data);

        return redirect()->route('Resource.index')->with('message', 'デバイスが登録されました。');
    }

    public function edit($id)
    {
        $ResourceModel = new ResourceModel();
        $Resource = $ResourceModel->find($id);

        if (!$Resource) {
            return redirect()->route('Resource.index')->with('error', 'デバイスが見つかりません。');
        }

        return view('Resource/form', ['Resource' => $Resource]);
    }

    public function update($id)
    {
        $ResourceModel = new ResourceModel();

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status', FILTER_SANITIZE_STRING),
        ];

        $ResourceModel->update($id, $data);

        return redirect()->route('Resource.index')->with('message', 'デバイス情報が更新されました。');
    }

    public function delete($id)
    {
        $ResourceModel = new ResourceModel();
        $ResourceModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);

        return redirect()->route('Resource.index')->with('message', 'デバイスが削除されました。');
    }
}
