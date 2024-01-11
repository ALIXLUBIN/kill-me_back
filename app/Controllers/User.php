<?php

namespace App\Controllers;

use \App\Libraries\Oauth;
use \Oauth2\Request;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class User extends ResourceController
{
	protected $modelName = 'App\Models\UserModel';
	protected $format = 'json';
	use ResponseTrait;

	public function login()
	{
		$oauth = new Oauth();
		$request = new \OAuth2\Request();
		$response = $oauth->server->handleTokenRequest($request->createFromGlobals());
		$code = $response->getStatusCode();
		$body = json_decode($response->getResponseBody(), true);

		if ($code != 200)
			return $this->fail($body, $code);

		setcookie('access_token', $body['access_token'], time() + 3600, '/', '', false, false);
		
		$user = $this->model->getUserId($body['access_token']);

		return $this->respond($user, $code);
	}

	public function create()
	{
		helper(['form']);

		$rules = [
			'nickname' => 'required|min_length[6]|max_length[20]|is_unique[users.nickname]',
			'email' => 'required|valid_email|is_unique[users.email]',
			'password' => 'required|min_length[8]|max_length[255]',
			'confirm_password' => 'matches[password]',
		];

		$errors = [
			'nickname' => [
				'is_unique' => 'Ce pseudo est déjà pris.',
				'max_length' => 'Le pseudo est trop long.',
				'min_length' => 'Le pseudo est trop court.',
				'required' => 'Le pseudo est requis.'
			],
			'email' => [
				'is_unique' => 'Cet email est déjà utilisé.',
				'valid_email' => 'Cet email n\'est pas valide.',
				'required' => 'L\'email est requis.'
			],
			'password' => [
				'max_length' => 'Le mot de passe est trop long.',
				'min_length' => 'Le mot de passe est trop court.',
				'required' => 'Le mot de passe est requis.'
			],
			'confirm_password' => [
				'matches' => 'Les mots de passe ne correspondent pas.',
				'required' => 'La confirmation du mot de passe est requise.'
			],
		];

		if (!$this->validate($rules, $errors))
			return $this->fail([
				'message' => $this->validator->getErrors(),
			]);

		$data = [
			'nickname' => $this->request->getVar('nickname'),
			'email' => $this->request->getVar('email'),
			'password' => $this->request->getVar('password'),
		];
		$userId = $this->model->insert($data);
		$data['id'] = $userId;
		unset($data['password']);

		return $this->respond(['message' => 'User created', 'data' => $data]);


		// return $this->respond([
		//     'message' => 'Create user',
		// ]);
	}
}
