<?php
namespace imperator\websocket;

class Message {
	private $connection;
	private $type;
	private $complete = false;
	private $message = '';

	/**
	 * Creates a new message.
	 * 
	 * @internal For internal use only
	 * @param Connection $connection The connection this message was received on
	 * @param int $type The opcode sent with this message
	 */
	public function __construct(Connection $connection, $type) {
		$this->connection = $connection;
		$this->type = $type;
	}

	/**
	 * Checks if this message is complete.
	 * 
	 * @internal For internal use only
	 * @return bool True if this message is complete
	 */
	public function isComplete() {
		return $this->complete;
	}

	/**
	 * Returns the connection this message was received on.
	 * 
	 * @return Connection The connection
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * Returns the opcode.
	 * 
	 * @return int The opcode received with this message
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Appends bytes to a message.
	 * 
	 * @internal For internal use only
	 * @param string $input The bytes to append
	 * @param int $length The number of bytes to append
	 * @param array $mask The mask to use to decode the bytes
	 */
	public function add($input, $length, array $mask) {
		if($this->complete) {
			return;
		}
		ob_start();
		for($n=0;$n < $length;$n++) {
			echo $input[$n] ^ $mask[$n%4];
		}
		$this->message .= ob_get_contents();
		ob_end_clean();
	}

	/**
	 * Marks a message as complete.
	 * 
	 * @internal For internal use only
	 */
	public function complete() {
		$this->complete = true;
	}

	/**
	 * Returns this message
	 * 
	 * @return string The contents of the message
	 */
	public function __toString() {
		return $this->message;
	}
}

?>