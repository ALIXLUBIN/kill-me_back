<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class Admin_attaque_model extends Model
{

	protected $table = 'attack';
	protected $primaryKey = 'id';
	protected $allowedFields = [
		"name",
		"id",
		"damage",
		"shieldPiercing",
		"manaCost",
		"heal",
		"type",
		"shieldRepair",
	];

	public function getAllAttaque() {
		$query = $this->db->table('attack')
		->select('*');

		return $query->get()->getResultArray();
	}
}
