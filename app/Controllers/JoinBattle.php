<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CharacterModel;

class JoinBattle extends ResourceController
{
	protected $modelName = 'App\Models\JoinBattleModel';
	protected $format = 'json';

	public function index()
	{
		// get the data from the request
		$data = $this->request->getJSON(true);

		var_dump($data);
	}

	public function create()
	{
		// get the selected character
		$data = $this->request->getJSON(true);

		$players = [
			['user' => $GLOBALS['user_id'], 'character' => $data['character_id']]
		];

		// si déja en attente
		if ($this->model->getQueuePlayer($GLOBALS['user_id']))
			return $this->failForbidden('Vous êtes déja en attente');

		// check if isOwned
		$characterModel = new CharacterModel();
		$isOwned = $characterModel->isOwned((int)$data['character_id'], $GLOBALS['user_id']);

		// Si possède pas le perso
		if (!isset($isOwned)) 
			return $this->failForbidden('Vous ne possédez pas se personnage');

		// Si déja en game
		if ($this->model->getUserStat($GLOBALS['user_id'], false))
			return $this->respond(['messages' => 'inGame']);

		// Vérification si joeur correspond dans la file d'attente
		$founedPlayer = $this->model->getWaitingPlayer($GLOBALS['user_id']);

		if (isset($founedPlayer)) {
			// Si adversaire trouvé

			$players[] = ['user' => $founedPlayer['user'], 'character' => $founedPlayer['character']];

			// check if a player is alrady in a game
			$userIds = array_map(function($player) {
				return $player['user'];
			}, $players);

			// Marquer les joueurs non dispo dans la file
			$this->model->changeAvabilityOfQueu($userIds);
	
			if (empty($this->model->getUserInBattle($userIds)))
				$this->failForbidden('Un des joueurs est déja en game CONTATEZ L\'ADMIN !!');
	
			// Création de la battle
			$battleId = $this->model->BattleCreat($players);
	
			foreach ($players as $key => $value) {
				$this->model->initStat($value['user'], $value['character'], $battleId);
			}
			return $this->respond(['messages' => 'battle_created']);
		} else {
			// Si pas d'adversaire trouvé
			$this->model->addToQueue($data['character_id'], $GLOBALS['user_id']);
			return $this->respond(['messages' => 'waiting']);
		}
	}

	public function joinAfterWait() {

		// check if user have found a game
		if (empty($this->model->getQueuePlayer($GLOBALS['user_id'], true)))
			return $this->failForbidden('not in a game');

		// take oute from queue
		$this->model->removeFromQueue($GLOBALS['user_id']);
		
		return $this->respond(['messages' => 'ready']);
	}
}