<?php
namespace imperator\websocket;

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
			$api = new \imperator\api\WebSocket(\imperator\api\Request::buildRequest($json), $user);
			$message->getConnection()->sendMessage($api->handleRequest());
		}
	}
}