<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BattleModel;
use App\Libraries\SocketIo;

use function PHPUnit\Framework\isNull;

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

		if (isset($game)) {
			$this->battleId = $game['battle_id'];
			$this->current = $game['current'];
		}
	}

	public function endGame() {
		$return = $this->getBattle([2]);

		$return['user_stat'] = $this->model->getUserScoreMoney($GLOBALS['user_id']);

		return $this->respond($return);
	}

	public function index(array $segment = [1, 0])
	{
		$return = $this->getBattle($segment);
		if (!$return['success']) {
			return $this->failUnauthorized('notInGame');
		}
		return $this->respond($return);
	}

	private function getBattle(array $segment) {
		$return = ['success' => false];

		if (!isset($this->battleId) or $segment != [1, 0]) {
			$game = $this->model->getBattleId($GLOBALS['user_id'], $segment);

			if (isset($game)) {
				$this->battleId = $game['battle_id'];
				$this->current = $game['current'];
			} else {
				$return['messages'] = 'notInGame';
				return $return;
			}
		}

		// check if user is in game 
		$self = $this->model->getUserStat($GLOBALS['user_id'], $this->battleId, false);

		// var_dump($self); die;
		if (!isset($self)) {
			$return['messages'] = 'notInGame';
			return $return;
		}

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
		$game['current_turn'] = $self['current_turn'];

		$game['self'] = $self;

		$ennemy = $this->model->getEnnemyStat($GLOBALS['user_id'], $game['self']['battle_id']);

		$game['ennemy'] = $ennemy;
		$game['success'] = true;

		return $game;
	}

	public function attack($id) {
		// check if user is in game
		$self = $this->model->getUserStat($GLOBALS['user_id'], $this->battleId);

		if (!isset($self))
			return $this->failForbidden('notInGame');

		// check if it's the turn of the player
		if ($self['current_turn'] != $GLOBALS['user_id'])
			return $this->failForbidden('notYourTurn');

		// check if attack is possible
		$attack = $this->model->getAttack($id, $self['character_id']);
		if (!isset($attack))
			return $this->failForbidden('attackNotPossible');

		// check the required mana
		if ($self['mana'] < $attack['manaCost'])
			return $this->failForbidden('notEnoughMana');

		$ennemy = $this->model->getEnnemyStat($GLOBALS['user_id'], $self['battle_id']);

		// Changement du tour à faire avant car si non possilbe d'attquer deux fois en théorie
		$this->model->changeTurn($ennemy['user_id'], $this->battleId);
		$this->model->updateLastAttack($id, $GLOBALS['user_id'], $this->battleId);

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


		// Retrait du mana ou ajout du mana
		if (isset($attack['manaCost']))
			$this->model->inflict( '-' . $attack['manaCost'], 'mana', $self['user_id'], $self['battle_id']);

		// Rajout du mana
		if (isset($self['manaRegen']))
			$this->model->inflict($self['manaRegen'], 'mana', $self['user_id'], $self['battle_id']);

		// Soins
		if (isset($attack['heal']))
			$this->model->inflict($attack['heal'], 'health', $self['user_id'], $self['battle_id']);

		// Réparation du bouclier
		if (isset($attack['shieldRepair']))
			$this->model->inflict($attack['shieldRepair'], 'shield', $self['user_id'], $self['battle_id']);


		$stats = $this->getBattleUserStat($id);

		// Check si l'adversaire est mort
		if ($stats[$ennemy['user_id']]['health'] <= 0) {

			$min = 5; // minimum value
			$max = 15; // maximum value
			$minusScore = rand($min, $max) * -1;

			$min = 25; // minimum value
			$max = 35; // maximum value
			$addScore = rand($min, $max);

			$addMoney = rand(250, 500);

			$stats[$ennemy['user_id']]['win']  = false;
			$stats[$ennemy['user_id']]['score']  = $minusScore;
			$stats[$GLOBALS['user_id']]['win']  = true;
			$stats[$GLOBALS['user_id']]['score']  = $addScore;

			// change the status of the game
			$this->model->endGame($GLOBALS['user_id'], $this->battleId);

			$this->model->updateStat($GLOBALS['user_id'], $addScore, $addMoney);
			$this->model->updateStat($ennemy['user_id'], $minusScore, round($addMoney * 0.8));

			$socket = new SocketIo;
			$socket->sendAttack($id, $this->battleId, $stats);
		}

		$socket = new SocketIo;
		$socket->sendAttack($id, $this->battleId, $stats);
	}

	private function getBattleUserStat($attackId = null) {

		$playerList = $this->model->playerList($this->battleId);
		$stats = [];

		foreach ($playerList as $key => $value) {
			$stats[$value['user_id']] = $this->model->getUserStat($value['user_id'], $this->battleId, false);
		}
		if (!isNull($attackId))
			$stats[$GLOBALS['user_id']]['lastAttack'] = $attackId;

		return $stats;
	}
}
