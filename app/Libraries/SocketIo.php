<?php

namespace App\Libraries;

use ElephantIO\Client;

class SocketIo
{
		public $client;

		public function __construct()
		{


		$url = 'http://localhost:3000';

		// if client option is omitted then it will use latest client available,
		// aka. version 4.x
		$options = [
			'client' => Client::CLIENT_4X,
			'headers' => [
				'Authorization' => env('SOCKET_IO_AUTHORIZATION'),
			]
		];

		$this->client = Client::create($url, $options);
		$this->client->connect();
		$this->client->of('/'); // can be omitted if connecting to default namespace
		}

		public function __destruct()
		{
			$this->client->disconnect();
		}
			
		public function test() {
			$this->client->emit('sendEventToUser', ['userId' => 19, 'event' => 'test']);
		}

		public function sendPlayerFound($playerId) {
			$this->client->emit('sendEventToUser', ['userId' => (int)$playerId, 'event' => 'playerFound']);
		}

		public function sendAttack($attackId, $battleId, $stat) {

			$this->client->emit('sendEventToRoom', ['event' => 'attack', 'battleId' => $battleId, 'data' => $stat]);
		}

		// public function createRoom(array $userIds, int $battleId) {
		// 	$this->client->emit('createRoom', ['userIds' => $userIds, 'battleId' => $battleId]);
		// }
}
