<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CharacterModel;

class Battle extends ResourceController
{
	protected $modelName = 'App\Models\BattleModel';
	protected $format = 'json';

	public function index() {

	}

	public function show($id = null) {

	}

	public function create() {
		
		// get the data from the request
		$data = $this->request->getJSON(true);

		// check if isOwned
		$characterModel = new CharacterModel();
		$isOwned = $characterModel->isOwned((int)$data['character_id'], $GLOBALS['user_id']);

		// if not owned return error
		if (count($isOwned) == 0) {
			return $this->failUnauthorized('You don\'t own this character');
		}

		// if alraedt in queue
		$selfInQueue = $this->model->getQueuePlayer($GLOBALS['user_id']);
		if (count($selfInQueue) != 0)
			return $this->failUnauthorized('already in queue');

		// get if the user is in the queue
		$waitingPlayer = $this->model->getWaitingPlayer($GLOBALS['user_id']);
		if (count($waitingPlayer) != 0)
			return $this->respond('find a player');
			// $this->lunchBattle($data['character_id'], $waitingPlayer[0]['character']);

		// add the user to the queue
		$this->model->addToQueue($data['character_id'], $GLOBALS['user_id']);

		return $this->respond('added to queue');






	}
}