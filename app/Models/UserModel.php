<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
	protected $table = 'users';
	protected $primaryKey = 'id';
	protected $allowedFields = ['id', 'nickname', 'email', 'password'];
	protected $beforeInsert = ['beforeInsert'];
	protected $beforeUpdate = ['beforeUpdate'];

	protected function beforeInsert(array $data)
	{
		$data = $this->passwordHash($data);
		return $data;
	}

	protected function beforeUpdate(array $data)
	{
		$data = $this->passwordHash($data);
		return $data;
	}

	protected function passwordHash(array $data)
	{
		if (isset($data['data']['password'])) {
			$data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
		}
		return $data;
	}

	public function getUserId($token)
	{
		$builder = $this->db->table('oauth_access_tokens');
		$builder->select('oauth_access_tokens.user_id, users.nickname, users.score, users.money, users.scope');
		$builder->join('users', 'users.id = oauth_access_tokens.user_id');
		$builder->where('access_token', $token);
		$query = $builder->get();
		$result = $query->getRowArray();
		return $result;
	}

	public function getScope($userId) {
		$querry = $this->db->table('users')
		->select('scope')
		->where('id', $userId);

		return $querry->get()->getRowArray();
	}
}
