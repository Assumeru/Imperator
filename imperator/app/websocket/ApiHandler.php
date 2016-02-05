<?php
namespace imperator\websocket;
use imperator\Imperator;

class ApiHandler extends DefaultConnectionHandler {
	private $connections;

	public function __construct() {
		$this->connections = new \SplObjectStorage();
	}

	public function onClose(Connection $connection) {
		$this->connections->detach($connection);
	}

	public function onOpen(Connection $connection) {
		$user = \imperator\User::getUserByHeaders($connection->getHeaders());
		if($user) {
			$this->connections[$connection] = $user;
		} else {
			$connection->close();
		}
	}

	public function onMessage(Message $message) {
		$user = $this->connections[$message->getConnection()];
		$json = json_decode($message->__toString(), true);
		if($user && $json) {
			$message->getConnection()->sendMessage(Imperator::handleApiRequest(
				\imperator\api\Api::WEBSOCKET,
				\imperator\api\Request::buildRequest($json),
				$user, $this
			));
		}
	}

	public function sendChatToConnections($gid, $jsonString) {
		//TODO
	}
}