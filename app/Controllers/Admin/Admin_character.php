<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;
use App\Models\Admin\Admin_character_model;

class Admin_character extends ResourceController
{
	protected $modelName = 'App\Models\Admin\Admin_character_model';
	protected $format = 'json';

	public function index()
	{
		$data = $this->model->getAllCharacter();
		return $this->respond($data);
	}



}
