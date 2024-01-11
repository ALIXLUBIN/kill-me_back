<?php

namespace App\Libraries;
// use OAuth2\Storage\Pdo;
use \App\Libraries\CustomOauthStorage;

class Oauth
{
	var $server;

	function __construct()
	{
		$this->init();
	}

	public function init()
	{
		$dsn = getenv('database.default.DNS');
		$username = getenv('database.default.username');
		$password = getenv('database.default.password');

		$storage = new CustomOauthStorage(['dsn' => $dsn, 'username' => $username, 'password' => $password]);
		$this->server = new \OAuth2\Server($storage);
		// $grantType = new OAuth2\GrantType\UserCredentials($storage);
		$this->server->addGrantType(new \OAuth2\GrantType\ClientCredentials($storage));
		$this->server->addGrantType(new \OAuth2\GrantType\UserCredentials($storage));
		// $this->server->addGrantType(new \	OAuth2\GrantType\AuthorizationCode($storage));
	}
}
