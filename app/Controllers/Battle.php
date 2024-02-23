<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BattleModel;
use App\Libraries\SocketIo;

class Battle extends ResourceController
{
	protected $modelName = 'App\Models\BattleModel';
	protected $format = 'json';

	private int $battleId;
	private int $current;

	public function __construct()
	{
		$model = new BattleModel();
		$game = $model->getBattleId($GLOBALS['user_id']);
		$this->battleId = $game['battle_id'];
		$this->current = $game['current'];
	}

	public function index()
	{
		// check if user is in game 
		$self = $this->model->getUserStat($GLOBALS['user_id'], $this->battleId, false);

		// var_dump($self); die;
		if (!isset($self)) 
		return $this->failUnauthorized('notInGame');
	
		// Add that the user is in the game
		$remaningPlayers = $this->model->joinGame($GLOBALS['user_id'], $this->battleId);

		if ($this->current == 0 && empty($remaningPlayers)) {
			// mark the game as current
			$this->model->setToCurrent($this->battleId);

			$playerList = $this->model->playerList($this->battleId);
			// $socket = new SocketIo;
			// $socket->createRoom($playerList, $this->battleId);
		}

		$game['battle_id'] = $this->battleId;
	

		$game['self'] = $self;

		$ennemy = $this->model->getEnnemyStat($GLOBALS['user_id'], $game['self']['battle_id']);

		$game['ennemy'] = $ennemy;

		return $this->respond($game);
	}

	public function attack($id) {
		
		// check if user is in game 
		$self = $this->model->getUserStat($GLOBALS['user_id'], $this->battleId);
		if (!isset($self)) 
			return $this->failForbidden('notInGame');
		
		// check if attack is possible
		$attack = $this->model->getAttack($id, $self['character_id']);
		if (!isset($attack))
			return $this->failForbidden('attackNotPossible');

		// check the required mana
		if ($self['mana'] < $attack['manaCost'])
			return $this->failForbidden('notEnoughMana');

		$ennemy = $this->model->getEnnemyStat($GLOBALS['user_id'], $self['battle_id']);

		// inflict damage
		if (isset($attack['damage'])) {
			$damage = $attack['damage'];

			// add the force multyplyer
			if (isset($self['strength']))
			$damage = $damage * (1 + $self['strength'] / 100);
		
			// redduce withs the shield
			if (isset($ennemy['shield'])) {

				if (isset($attack['shieldPiercing'])) {
					
					// si le percing est plus grand que le shield pas de chield
					$shield = 1;

					if (!$ennemy['shield'] < $attack['shieldPiercing'])
						$shield = 1 - ($ennemy['shield'] - $attack['shieldPiercing']) / 100;
				} else
					$shield = 1 - $ennemy['shield'] / 100;

				$damage = $damage * $shield;
			}

			$damage = round($damage) * -1;
			$this->model->inflict($damage, 'health', $ennemy['user_id'], $self['battle_id']);
		}

		// Retrait du mana
		if (isset($attack['manaCost']))
			$this->model->inflict( '-' . $attack['manaCost'], 'mana', $self['user_id'], $self['battle_id']);

		// Soins
		if (isset($attack['heal']))
			$this->model->inflict($attack['heal'], 'health', $self['user_id'], $self['battle_id']);

		// Réparation du bouclier
		if (isset($attack['shieldRepair']))
			$this->model->inflict($attack['shieldRepair'], 'shield', $self['user_id'], $self['battle_id']);

		$stats = $this->getBattleUserStat();
		$socket = new SocketIo;
		$socket->sendAttack($id, $this->battleId, $stats);
	}

	private function getBattleUserStat() {

		$playerList = $this->model->playerList($this->battleId);
		$stats = [];
		
		foreach ($playerList as $key => $value) {
			$stats[$value['user_id']] = $this->model->getUserStat($value['user_id'], $this->battleId, false);
		}

		return $stats;
	}
}
