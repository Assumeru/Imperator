<?php
use \imperator\websocket\DefaultConnectionHandler;
use \imperator\websocket\Message;
use \imperator\websocket\WebSocket;
use \imperator\websocket\Connection;
use \imperator\Logger;

require_once './app/websocket/websocket.class.php';
require_once './app/httpheaders.class.php';
require_once './app/logger.class.php';

class EchoServer extends DefaultConnectionHandler {
	public function onClose(Connection $connection) {
		$connection->getLogger(Logger::LEVEL_INFO, 'Closed connection.');
	}

	public function onOpen(Connection $connection) {
		$connection->getLogger(Logger::LEVEL_INFO, 'Opened connection.');
		$connection->sendMessage('Hello world!');
	}

	public function onMessage(Message $message) {
		$connection->getLogger(Logger::LEVEL_INFO, 'MSG: '.$message);
		$message->getConnection()->sendMessage($message->__toString());
	}
}

header('Content-type: text/plain; charset=utf-8');
ob_implicit_flush();
set_time_limit(30);

(new WebSocket('127.0.0.1', 8080, '/test', new EchoServer(), new Logger()))->start();