<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class Admin_character_model extends Model
{

	protected $table = 'character';
	protected $primaryKey = 'id';
	protected $allowedFields = [
		"name",
		"id",
		"health",
		"maxHealth",
		"mana",
		"maxMana",
		"manaRegen",
		"strength",
		"maxStrength",
		"shield",
		"maxShield",
		"price",
	];

	public function getAllCharacter() {
		$query = $this->db->table('character')
		->select('*');

		return $query->get()->getResultArray();
	}

	public function deleteAttack($id) {
		$this->db->table('character_attack')
		->where('character', $id)
		->delete();
	}

	public function addAttack($id, $attack) {
		$this->db->table('character_attack')
		->insertBatch($attack);
	}
}
