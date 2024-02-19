<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CharacterModel;

class Battle extends ResourceController
{
	protected $modelName = 'App\Models\BattleModel';
	protected $format = 'json';

	public function index()
	{
		// check if user is in game 
		$self = $this->model->getUserStat($GLOBALS['user_id'], false);

		// var_dump($self); die;
		if (!isset($self)) 
			return $this->failUnauthorized('notInGame');

		

		$game['slef'] = $self;

		$ennemy = $this->model->getEnnemyStat($GLOBALS['user_id'], $game['slef']['battle_id']);

		$game['ennemy'] = $ennemy;

		return $this->respond($game);
	}

	public function attack($id) {
		
		// check if user is in game 
		$self = $this->model->getUserStat($GLOBALS['user_id']);
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
	}
}
