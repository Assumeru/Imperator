<?php
namespace imperator\websocket;

class Connection {
	const OPCODE_CONTINUATION_FRAME = 0;
	const OPCODE_TEXT_FRAME = 1;
	const OPCODE_BINARY_FRAME = 2;
	const OPCODE_NON_CONTROL_FRAME0 = 3;
	const OPCODE_NON_CONTROL_FRAME1 = 4;
	const OPCODE_NON_CONTROL_FRAME2 = 5;
	const OPCODE_NON_CONTROL_FRAME3 = 6;
	const OPCODE_NON_CONTROL_FRAME4 = 7;
	const OPCODE_CONNECTION_CLOSE = 8;
	const OPCODE_PING = 9;
	const OPCODE_PONG = 10;
	const OPCODE_CONTROL_FRAME0 = 11;
	const OPCODE_CONTROL_FRAME1 = 12;
	const OPCODE_CONTROL_FRAME2 = 13;
	const OPCODE_CONTROL_FRAME3 = 14;
	const OPCODE_CONTROL_FRAME4 = 15;
	const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

	private $socket;
	private $websocket;
	private $headers;
	private $currentMessage = null;
	private $closed = false;
	private $connected = false;
	private $inputBuffer = '';

	/**
	 * Creates a new connection.
	 * 
	 * @internal For internal use only
	 * @param resource $socket The socket to read from
	 * @param WebSocket $websocket The websocket that spawned this connection
	 */
	public function __construct($socket, WebSocket $websocket) {
		$this->socket = $socket;
		$this->websocket = $websocket;
	}

	/**
	 * Checks if this connection has been closed.
	 * 
	 * @return bool True if this connection has been closed
	 */
	public function isClosed() {
		return $this->closed;
	}

	/**
	 * Returns the headers sent by the user when the connection was made.
	 * 
	 * @return \imperator\HttpHeaders The user's headers
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Returns the logger used by this websocket.
	 * 
	 * @return \imperator\Logger The logger used by this connection
	 */
	public function getLogger() {
		return $this->websocket->getLogger();
	}

	/**
	 * Returns the socket underlying this connection.
	 * 
	 * @internal For internal use only
	 * @param WebSocket $parent The websocket that spawned this connection
	 */
	public function getSocket(WebSocket $parent = null) {
		if($parent == $this->websocket) {
			return $this->socket;
		}
		throw new \Exception('Invalid method invocation.');
	}

	/**
	 * Runs this connection, parsing any incoming data.
	 * 
	 * @internal For internal use only
	 */
	public function run() {
		if($this->closed) {
			return;
		}
		$buffer = $this->read();
		if($buffer === false) {
			$this->close();
		} else if(!empty($buffer)) {
			$this->inputBuffer .= $buffer;
			if(!$this->connected) {
				if($this->isIncompleteHeader()) {
					return;
				}
				$this->doHandShake($this->inputBuffer);
				if(!$this->closed) {
					$this->connected = true;
					$this->inputBuffer = '';
					$this->websocket->handleConnectionOpen($this);
				}
			} else {
				if($this->isIncompleteMessage()) {
					return;
				}
				$this->readAndHandleMessage($this->inputBuffer);
				$this->inputBuffer = '';
			}
		}
	}

	/**
	 * Closes the connection, sending a close frame to the client, and calling the connection handler.
	 */
	public function close() {
		if($this->closed) {
			return;
		}
		$this->closed = true;
		$this->sendClose();
		fclose($this->socket);
		if($this->connected) {
			$this->websocket->handleConnectionClose($this);
		}
	}

	private function read($length = 4048) {
		$output = false;
		if($buffer = fread($this->socket, $length)) {
			$output .= $buffer;
		}
		return $output;
	}

	private function write($message) {
		$length = strlen($message);
		$written = 0;
		while($written < $length) {
			$written = @fwrite($this->socket, $message, $length);
			if($written === false || $written === 0) {
				return false;
			} else if($written < $length) {
				$message = substr($message, $written);
				$length -= $written;
			} else {
				fflush($this->socket);
				return true;
			}
		}
	}

	private function isIncompleteHeader() {
		return preg_match("/([\n|\r]){4,}/", $this->inputBuffer) == false
			&& strpos($this->inputBuffer, "\n\n") === false
			&& strpos($this->inputBuffer, "\r\r") === false;
	}

	private function isIncompleteMessage() {
		$length = strlen($this->inputBuffer);
		if($length < 2 || $length < $this->getMessageLength()) {
			return true;
		}
		return false;
	}

	private function getMessageLength() {
		$length = 1;
		$byte = ord($this->inputBuffer[1]);
		if($byte >> 7) {
			$length += 4;
		}
		$payloadLen = $byte & 0x7f;
		if($payloadLen > 125) {
			$bufferLength = 0;
			if($payloadLen == 126) {
				$bufferLength += 2;
			} else if ($payloadLen == 127) {
				$bufferLength += 8;
			}
			$buffer = substr($this->inputBuffer, 2, $bufferLength);
			if($buffer && strlen($buffer) == $bufferLength) {
				$length += $this->getPayloadLength($byte, $buffer);
			} else {
				$length += $payloadLen;
			}
			$length += $bufferLength;
		} else {
			$length += $payloadLen;
		}
		return $length;
	}

	private function readFromBuffer(&$buffer, $length = 1, $format = 'l') {
		if($buffer !== false) {
			$output = substr($buffer, 0, $length);
			if($output !== false) {
				$buffer = substr($buffer, $length);
				if($format !== false) {
					if($length === 1) {
						$output = ord($output);
					} else {
						$output = unpack($format, $output);
						$output = $output[1];
					}
				}
				return $output;
			}
		}
		return false;
	}

	private function doHandShake($buffer) {
		if($this->parseHeaders($buffer) && $this->isWebSocketRequest()) {
			$this->sendResponseHeader();
		} else {
			$this->close();
		}
	}

	private function parseHeaders($buffer) {
		$get = 'GET '.$this->websocket->getPath().' HTTP/1.1';
		$lines = explode("\n", trim($buffer), 2);
		$line = trim($lines[0]);
		$headers = $lines[1];
		if($line == $get) {
			$this->headers = new \imperator\HttpHeaders($headers);
			return true;
		}
		return false;
	}

	private function isWebSocketRequest() {
		return $this->headers->keyEquals('host', $this->websocket->getAddress().':'.$this->websocket->getPort())
			&& $this->headers->keyEquals('upgrade', 'websocket')
			&& $this->headers->keyEquals('sec-websocket-version', '13')
			&& $this->headers->keyEquals('connection', 'Upgrade')
			&& $this->headers->keyContains('origin', '://'.$this->websocket->getAddress());
	}

	private function sendResponseHeader() {
		$msg = "HTTP/1.1 101 Switching Protocols\r\n".
				"Upgrade: websocket\r\n".
				"Connection: Upgrade\r\n".
				"Sec-WebSocket-Accept: ".$this->getResponseKey()."\r\n\r\n";
		$this->write($msg);
	}

	private function getResponseKey() {
		return base64_encode(hash('sha1', $this->headers->getSingleton('sec-websocket-key').self::GUID, true));
	}

	private function readAndHandleMessage($buffer) {
		$byte = $this->readFromBuffer($buffer);
		$fin = $byte >> 7;
		if($this->checkRSV($byte)) {
			$this->close();
			return;
		}
		$opcode = $byte & 0xf;
		if(($this->currentMessage === null || $opcode != self::OPCODE_CONTINUATION_FRAME) && $opcode < self::OPCODE_CONNECTION_CLOSE) {
			$this->currentMessage = new Message($this, $opcode);
		}
		$byte = $this->readFromBuffer($buffer);
		$mask = $byte >> 7;
		$payloadLen = $this->getPayloadLength($byte, $buffer);
		$maskingKey = $this->getMaskingKey($mask, $buffer);
		$this->handleMessage($opcode, $fin, $payloadLen, $maskingKey, $buffer);
	}

	private function checkRSV($byte) {
		$rsv1 = $byte & 64;
		$rsv2 = $byte & 32;
		$rsv3 = $byte & 16;
		return ($rsv1 | $rsv2 | $rsv3) !== 0;
	}

	private function getPayloadLength($byte, &$buffer) {
		$payloadLen = $byte & 0x7f;
		if($payloadLen == 126) {
			$payloadLen = $this->readFromBuffer($buffer, 2, 'n');
		} else if ($payloadLen == 127) {
			$payloadLen = $this->readFromBuffer($buffer, 8, 'N');
		}
		return $payloadLen;
	}

	private function getMaskingKey($masked, &$buffer) {
		$maskingKey = array(0,0,0,0);
		if($masked) {
			for($n=0; $n < 4;$n++) {
				$maskingKey[$n] = $this->readFromBuffer($buffer, 1, false);
			}
		}
		return $maskingKey;
	}

	private function handleMessage($opcode, $fin, $payloadLen, array $maskingKey, &$buffer) {
		if($opcode < self::OPCODE_CONNECTION_CLOSE) {
			$this->handleContinuedFrame($fin, $payloadLen, $maskingKey, $buffer);
		} else {
			$this->handleControlFrame($opcode, $payloadLen, $maskingKey, $buffer);
		}
	}

	private function handleContinuedFrame($fin, $payloadLen, array $maskingKey, &$buffer) {
		$this->currentMessage->add($this->readFromBuffer($buffer, $payloadLen, false), $payloadLen, $maskingKey);
		if($fin) {
			$this->currentMessage->complete();
		}
		$this->websocket->handleMessage($this->currentMessage);
		$this->currentMessage = null;
	}

	private function handleControlFrame($opcode, $payloadLen, $maskingKey, &$buffer) {
		$message = new Message($this, $opcode);
		$message->add($this->readFromBuffer($buffer, $payloadLen, false), $payloadLen, $maskingKey);
		$message->complete();
		$this->websocket->handleControlFrame($message);
	}

	/**
	 * Sends a pong frame to the client.
	 * 
	 * @param string $message The message contained in the frame
	 */
	public function sendPong($message) {
		return $this->sendFullMessage(true, self::OPCODE_PONG, null, $message);
	}

	/**
	 * Sends a close frame to the client.
	 * 
	 * @deprecated Consider using close instead
	 */
	public function sendClose() {
		return $this->sendFullMessage(true, self::OPCODE_CONNECTION_CLOSE, null, '0');
	}

	/**
	 * Sends a message to the client.
	 * 
	 * @param string $message The message to send
	 */
	public function sendMessage($message) {
		return $this->sendFullMessage(true, self::OPCODE_TEXT_FRAME, null, $message);
	}

	/**
	 * Sends a message to the client.
	 * 
	 * @param bool $fin True if this is a complete message
	 * @param int $opcode The opcode to send with the message
	 * @param array|null $mask The mask used to encode the message
	 * @param string $message The masked message to send
	 */
	public function sendFullMessage($fin, $opcode, array $mask = null, $message) {
		$frame = $this->encapsulateMessage($fin, $opcode, $mask, $message);
		$this->write($frame);
	}

	private function encapsulateMessage($fin, $opcode, array $mask = null, $message) {
		ob_start();
		echo chr($this->getMessageFirstByte($fin, $opcode));
		$length = $this->getMessageLengthBytes($mask !== null, strlen($message));
		for($n=0;$n < count($length);$n++) {
			echo chr($length[$n]);
		}
		if($mask !== null) {
			for($n=0;$n < 4;$n++) {
				echo chr($mask[$n]);
			}
		}
		echo $message;
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}

	private function getMessageFirstByte($fin, $opcode) {
		return ($fin << 7) | $opcode;
	}

	private function getMessageLengthBytes($masked, $length) {
		$mask = $masked << 7;
		if($length < 126) {
			return $this->getMessageSmallLengthBytes($mask, $length);
		} else if($length < 65536) {
			return $this->getMessageMediumLengthBytes($mask, $length);
		} else {
			return $this->getMessageLargeLengthBytes($mask, $length);
		}
	}

	private function getMessageSmallLengthBytes($mask, $length) {
		return array($mask | $length);
	}

	private function getMessageMediumLengthBytes($mask, $length) {
		return array($mask | 126, $length >> 8, $length & 0xff);
	}

	private function getMessageLargeLengthBytes($mask, $length) {
		$out = array($mask | 127, 0, 0, 0, 0, 0, 0, 0, 0);
		$bytes = pack('J', $length);
		for($n=8, $i=strlen($bytes)-1;$i >= 0;$n--) {
			$out[$n] = ord($bytes[$i]);
		}
		return $out;
	}
}