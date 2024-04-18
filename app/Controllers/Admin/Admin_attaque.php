<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Admin_attaque extends ResourceController
{
	protected $modelName = 'App\Models\Admin\Admin_attaque_model';
	protected $format = 'json';

	public function index()
	{
		$data = $this->model->getAllAttaque();
		return $this->respond($data);
	}

	public function create($id = null)
	{
		$data = [
			'name' => htmlspecialchars($this->request->getVar('name')),
			"damage" => $this->request->getVar("damage", FILTER_VALIDATE_INT),
			"shieldPiercing" => $this->request->getVar("shieldPiercing", FILTER_VALIDATE_INT),
			"manaCost" => $this->request->getVar("manaCost", FILTER_VALIDATE_INT),
			"heal" => $this->request->getVar("heal", FILTER_VALIDATE_INT),
			"type" => $this->request->getVar("type", FILTER_VALIDATE_INT),
			"shieldRepair" => $this->request->getVar("shieldRepair", FILTER_VALIDATE_INT),
		];

		if (empty($data)) {
			return $this->fail('Veuillez remplir tous les champs', 400);
		}

		if ($id === null) {
			$this->model->insert($data);
		} else {
			$this->model->update($id, $data);
		}

		return $this->respondCreated();
	}

	public function update($id = null)
	{
		$this->create((int)$id);
	}


}
