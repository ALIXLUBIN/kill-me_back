<?php 
namespace App\Models;

use CodeIgniter\Model;

class CharacterModel extends Model
{
		protected $table = 'character';
		protected $primaryKey = 'id';
		// protected $allowedFields = ['', 'post_description'];

		public function getAttack($id) {
			$query = $this->db->table('attack')
			->select('attack.id, attack.name, attack.damage, attack.shieldPiercing, attack.manaCost, attack.heal, attack.type')
			->join('character_attack', 'character_attack.attack = attack.id')
			->where('character_attack.character', $id);
			// ->where('attack.type', 0);

			return $query->get()->getResultArray();
		}

		public function isOwned($caraterId, $userId) {
			$query = $this->db->table('user_ownedCharacter')
			->select('user_ownedCharacter.character')
			->where('user_ownedCharacter.user', $userId)
			->where('user_ownedCharacter.character', $caraterId);

			return $query->get()->getResultArray();
		}

		public function getMoney($userId) {
			$query = $this->db->table('users')
			->select('users.money')
			->where('users.id', $userId);

			return $query->get()->getRowArray()['money'];
		}

		public function getPrice($caraterId) {
			$query = $this->db->table('character')
			->select('character.price')
			->where('character.id', $caraterId);

			return $query->get()->getRowArray()['price'];
		}

		public function buy($caraterId, $userId) {
			$price = $this->getPrice($caraterId);

			$data = [
				'user' => $userId,
				'character' => $caraterId,
			];

			$this->db->table('user_ownedCharacter')->insert($data);

			$this->db->table('users')
			->set('money', 'money - '. $price, false)
			->where('id', $userId)
			->update();

			return true;
		}
}