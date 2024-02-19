<?php 
namespace App\Models;

use CodeIgniter\Model;

class JoinBattleModel extends Model
{
	protected $table = 'blog';
	protected $primaryKey = 'id';

	public function addToQueue($characterId, $userId) {
		$data = [
			'character' => $characterId,
			'user' => $userId,
		];

		$this->db->table('battle_queue')->insert($data);
	}

	public function getWaitingPlayer($userId) {

		// subquery to get the score of the user
		$subQuery = $this->db->table('users')
		->select('users.score')
		->where('users.id', $userId)
		->getCompiledSelect();

		$query = $this->db->table('battle_queue')
		->select('battle_queue.character, battle_queue.user')
		->join('users', 'users.id = battle_queue.user')
		->where("users.score BETWEEN ($subQuery) - 250 AND ($subQuery) + 250", null, false)
		->orderBy('battle_queue.created_at', 'DESC');

		return $query->get()->getRowArray();

	}

	public function getQueuePlayer($userId, $available = false) {
		$query = $this->db->table('battle_queue')
		->select('battle_queue.character')
		->where('battle_queue.user', $userId);

		if ($available)
			$query->where('available' , (string)$available);

		return $query->get()->getResultArray();
	}

		public function removeFromQueue($userId) {
		$this->db->table('battle_queue')
		->where('user', $userId)
		->where('available', '0')
		->delete();

		return $this->db->affectedRows();
	}

	public function changeAvabilityOfQueu($userId) {
		$this->db->table('battle_queue')
		->whereIn('user', $userId)
		->update('available', '1');
	}

	public function initStat($user, $character, $battleId) {
		$defaultStat = $this->db->table('character')
		->select('id AS character_id, health, mana, strength, shield')
		->where('id', $character)
		->get()
		->getRowArray();

		$defaultStat['user_id'] = $user;
		$defaultStat['battle_id'] = $battleId;
	
		$this->db->table('battle_player')
		->insert($defaultStat);
	}

	public function BattleCreat() {
		$this->db->table('battle')
		->insert(['current' => '0']);

		return $this->db->insertID();
	}

	public function getUserStat($userId, $current = true) {
		$query = $this->db->table('battle')
		->select('battle.battle_id, battle_player.character_id, battle_player.health, battle_player.mana, battle_player.strength, battle_player.shield')
		->join('battle_player', 'battle_player.battle_id = battle.battle_id')
		->where('battle_player.user_id', $userId);

		if ($current)
			$query->where('battle.current', '1');

		return $query->get()->getRowArray();
	}

	public function getEnnemyStat($userId, $battleId){ 

		$query = $this->db->table('battle_player')
		->select('user_id, character_id, health, mana, strength, shield, users.nickname')
		->join('users', 'users.id = battle_player.user_id')
		->where('user_id !=', $userId)
		->where('battle_id', $battleId);

		return $query->get()->getRowArray();

	}

	public function getAttack($attackId, $characterId) {
		$query = $this->db->table('character_attack')
		->select('attack.manaCost, attack.damage, attack.shieldPiercing, attack.heal, attack.shieldRepair')
		->join('attack', 'attack.id = character_attack.attack')
		->where('character_attack.attack', $attackId)
		->where('character_attack.character', $characterId);

		return $query->get()->getRowArray();
	}

	public function inflict($amount, $filed, $playerId, $battleId) {
		$this->db->table('battle_player')
		->where('user_id', $playerId)
		->where('battle_id', $battleId)
		->set($filed, "$filed + $amount", false)
		->update();
	}

	public function getUserInBattle($userIds) {
		$query = $this->db->table('battle_player')
		->select('user_id')
		->whereIn('user_id', $userIds);

		return $query->get()->getResultArray();
	}
}