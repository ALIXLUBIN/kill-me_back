<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class Admin_character_model extends Model
{

	public function getAllCharacter() {
		$query = $this->db->table('character')
		->select('*');

		return $query->get()->getResultArray();
	}
}
