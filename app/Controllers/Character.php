<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use PharIo\Manifest\Library;
use App\Libraries\SocketIo;

class Character extends ResourceController
{
	protected $modelName = 'App\Models\CharacterModel';
	protected $format = 'json';

	public function index()
	{

		if (isset($GLOBALS['user_id'])) {

			$data['characters'] = $this->model
			->select('character.*, (user_ownedCharacter.character IS NOT NULL) AS owned')
			->join('user_ownedCharacter', 'user_ownedCharacter.character = character.id AND user_ownedCharacter.user = '. $GLOBALS['user_id'], 'left')
			->orderBy('owned', 'DESC')
			->findAll();
		}
		else
			$data['characters'] = $this->model->findAll();

		// format the data so the id is also the identifent of array 

		$owned = ['true' => [], 'false' => []];

		$data['characters'] = array_reduce($data['characters'], function($carry, $item) use (&$owned) {
			$carry[$item['id']] = $item;

			if ($item['owned'] == "1")
				$owned['true'][] = $item['id'];
			else
				$owned['false'][] = $item['id'];

			return $carry;
		}, []);

		$data['owned'] = $owned;

		// Retournez la réponse
		return $this->respond($data);
	}

	public function show($id = null)
	{
		$charater = $this->model->find($id);
		
		// select on attack table
		$attack = $this->model->getAttack($id);

		// var_dump($attack);

		$attack = array_reduce($attack, function($carry, $item) {
			$item = array_filter($item, function($value) {
            return $value !== NULL;
			});

			if ($item['type'] == "0")
				$carry['attack'][] = $item;
			else
				$carry['spell'][] = $item;

			return $carry;
		}, []);


		$charater['attacks'] = $attack['attack'] ?? [] ;
		$charater['spells'] = $attack['spell'] ?? [];
		
    // Retournez la réponse
    return $this->respond($charater);
	}

	public function buy($id) {
		// check if user already own the character
		$owned = $this->model->isOwned($id, $GLOBALS['user_id']);
		if ($owned)
			return $this->failForbidden('Vous possédez déjà ce personnage');

		// check if user have enough money
		$money = $this->model->getMoney($GLOBALS['user_id']);
		$price = $this->model->getPrice($id);
		if ($money < $price)
			return $this->failForbidden('Fonds insuffisants');

		// buy the character
		$this->model->buy($id, $GLOBALS['user_id']);

		// return the new money
		$money = $this->model->getMoney($GLOBALS['user_id']);
		return $this->respond(['money' => $money]);
	}

}