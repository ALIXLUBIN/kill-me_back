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

	public function create($id = null) {

			$name = htmlspecialchars($this->request->getVar('name'));
			$health = $this->request->getVar('health', FILTER_VALIDATE_INT);
			$maxHealth = $this->request->getVar('maxHealth', FILTER_VALIDATE_INT);
			$mana = $this->request->getVar('mana', FILTER_VALIDATE_INT);
			$maxMana = $this->request->getVar('maxMana', FILTER_VALIDATE_INT);
			$manaRegen = $this->request->getVar('manaRegen', FILTER_VALIDATE_INT);
			$strength = $this->request->getVar('strength', FILTER_VALIDATE_INT);
			$maxStrength = $this->request->getVar('maxStrength', FILTER_VALIDATE_INT);
			$shield = $this->request->getVar('shield', FILTER_VALIDATE_INT);
			$maxShield = $this->request->getVar('maxShield', FILTER_VALIDATE_INT);
			$price = $this->request->getVar('price', FILTER_VALIDATE_INT);



			// if (is_null($name) || is_null($health)  || is_null($maxHealth) || is_null($mana) || is_null($maxMana) || is_null($manaRegen) || is_null($strength) || is_null($maxStrength) || is_null($shield) || is_null($maxShield) || is_null($price))
			// 	return $this->fail('Veuillez remplir tous les champs', 400);

			if (isset($id))
				$this->model->update($id, [
					'name' => $name,
					'health' => $health,
					'maxHealth' => $maxHealth,
					'mana' => $mana,
					'maxMana' => $maxMana,
					'manaRegen' => $manaRegen,
					'strength' => $strength,
					'maxStrength' => $maxStrength,
					'shield' => $shield,
					'maxShield' => $maxShield,
					'price' => $price
				]);

				else {

					$this->model->insert([
						'name' => $name,
						'health' => $health,
						'maxHealth' => $maxHealth,
						'mana' => $mana,
						'maxMana' => $maxMana,
						'manaRegen' => $manaRegen,
						'strength' => $strength,
						'maxStrength' => $maxStrength,
						'shield' => $shield,
						'maxShield' => $maxShield,
						'price' => $price
					]);
				}


			return $this->respondCreated();
	}

	public function update($id = null)
	{
		$this->create((int)$id);
	}

	public function updateCharacterAttack(int $id) {

		$attack = explode(',', $this->request->getVar('attack'));


		$data = [];
		foreach ($attack as $key => $value) {
			if (!$value)
				break;

			$data[] = [
				'character' => $id,
				'attack'=> (int)$value
			];
		}

		if (empty($attack))
			return $this->fail('Veuillez remplir tous les champs', 400);

		$this->model->deleteAttack($id);
		$this->model->addAttack($id, $data);

		return $this->respondUpdated();

	}


}
