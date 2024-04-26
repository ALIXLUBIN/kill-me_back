<?php

namespace App\Libraries;

use ElephantIO\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LogLevel;

class SocketIo
{
	public $client;
	public $logger;

	public function __construct()
	{


		$url = 'http://localhost:3000';

		$logfile = WRITEPATH . 'logs/socketio.log';

		$this->logger = new Logger('elephant.io');
		$this->logger->pushHandler(new StreamHandler($logfile, LogLevel::DEBUG));

		// if client option is omitted then it will use latest client available,
		// aka. version 4.x
		$options = [
			'client' => Client::CLIENT_4X,
			'logger' => $this->logger,
			'headers' => [
				'Authorization' => env('SOCKET_IO_AUTHORIZATION'),
			]
		];

		$this->client = Client::create($url, $options);
		$this->client->connect();
		$this->client->of('/socket.io'); // can be omitted if connecting to default namespace

	}

	public function __destruct()
	{
		$this->client->disconnect();
	}

	public function test()
	{
		$this->client->emit('sendEventToUser', ['userId' => 19, 'event' => 'test']);
	}

	public function getLoggedUsers()
	{
		$this->client->emit('getLoggedUsers', ['message' => 'This is first message']);
		if ($retval = $this->client->wait('getLoggedUsers')) {
			return $retval->inspect();

			// echo sprintf("Got a reply for first message: %s\n", $retval->inspect());
		}
	}

	public function sendPlayerFound($playerId)
	{
		$this->client->emit('sendEventToUser', ['userId' => (int)$playerId, 'event' => 'playerFound']);
	}

	public function sendAttack($attackId, $battleId, $stat)
	{
		$this->client->emit('sendEventToRoom', ['event' => 'attack', 'battleId' => $battleId, 'data' => $stat]);
	}

	// public function createRoom(array $userIds, int $battleId) {
	// 	$this->client->emit('createRoom', ['userIds' => $userIds, 'battleId' => $battleId]);
	// }
}
