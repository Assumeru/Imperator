<?php
namespace imperator\websocket;

interface ConnectionHandler {
	/**
	 * Called when a connection is closed.
	 * 
	 * @param Connection $conn The connection that closed
	 */
	public function onClose(Connection $conn);

	/**
	 * Called when a connection is made.
	 * 
	 * @param Connection $conn The new connection
	 */
	public function onOpen(Connection $conn);

	/**
	 * Called when a message is received.
	 * 
	 * @param Message $message The message received
	 */
	public function onMessage(Message $message);

	/**
	 * Called when a control frame is received.
	 * 
	 * @param Message $message The message received
	 */
	public function onControlFrame(Message $message);
}