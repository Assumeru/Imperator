<?php
namespace imperator\websocket;

class DefaultConnectionHandler implements ConnectionHandler {
	public function onClose(Connection $conn) {}

	public function onOpen(Connection $conn) {}

	public function onMessage(Message $message) {}

	/**
	 * Closes the connection when a close frame is received and sends a pong when a ping frame is received.
	 * 
	 * @see \imperator\websocket\ConnectionHandler::onControlFrame()
	 */
	public function onControlFrame(Message $message) {
		if($message->getType() == Connection::OPCODE_CONNECTION_CLOSE) {
			$message->getConnection()->getLogger()->log(\imperator\Logger::LEVEL_INFO, 'Closing connection.');
			$message->getConnection()->close();
		} else if($message->getType() == Connection::OPCODE_PING) {
			$message->getConnection()->getLogger()->log(\imperator\Logger::LEVEL_INFO, 'Sending pong.');
			$message->getConnection()->sendPong($message->__toString());
		}
	}
}