<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Blog extends ResourceController
{
    protected $modelName = 'App\Models\BlogModel';
    protected $format = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    public function create()
    {
        helper(['form']);

        $rules = [
            'title' => 'required|min_length[6]',
            'description' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [
            'post_title' => $this->request->getVar('title'),
            'post_description' => $this->request->getVar('description'),
        ];
        $id = $this->model->insert($data);
        $data['post_id'] = $id;
        return $this->respondCreated($data);
    }

    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Item not found');
        }
        $data = $this->model->find($id);
        return $this->respond($data);
    }

    public function update($id = null)
    {
        helper(['form']);
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Item not found');
        }
        $rules = [
            'title' => 'required|min_length[6]',
            'description' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $input = $this->request->getRawInput();
        $data = [
            'post_title' => $input['title'],
            'post_description' => $input['description'],
        ];

        $this->model->update($id, $data);
        return $this->respond($data);
    }

    public function delete($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Item not found');
        }

        $this->model->delete($id);
        return $this->respondDeleted($data);
    }
}
