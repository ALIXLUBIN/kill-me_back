<?php 
namespace App\Models;

use CodeIgniter\Model;

class BattleModel extends Model
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
		->select('battle_queue.character')
		->join('users', 'users.id = battle_queue.user')
		->where("users.score BETWEEN ($subQuery - 250) AND ($subQuery + 250)", null, false);

		return $query->get()->getResultArray();
	}

	public function getQueuePlayer($userId) {
		$query = $this->db->table('battle_queue')
		->select('battle_queue.character')
		->where('battle_queue.user', $userId);

		return $query->get()->getResultArray();
	}
}