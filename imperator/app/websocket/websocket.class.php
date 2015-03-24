<?php
namespace imperator\websocket;

class WebSocket {
	private $address;
	private $port;
	private $path;
	private $connectionHandler;
	private $timeout = array('sec' => 3600, 'usec' => 0);
	private $running = false;
	private $socket;
	private $connections = array();
	private $sockets;
	private $containsClosed = false;
	private $logger;

	/**
	 * Creates a new websocket.
	 * 
	 * @param string $address The URL to receive connections on
	 * @param int $port The port to receive connections on
	 * @param string $path The path to receive connections on
	 * @param ConnectionHandler $connectionHandler The optional connection handler
	 * @param \imperator\Logger $logger The logger to use
	 */
	public function __construct($address, $port, $path, ConnectionHandler $connectionHandler = null, \imperator\Logger $logger) {
		$this->address = $address;
		$this->port = (int)$port;
		$this->path = $path;
		$this->logger = $logger;
		if($connectionHandler === null) {
			$this->connectionHandler = new DefaultConnectionHandler();
		} else {
			$this->connectionHandler = $connectionHandler;
		}
	}

	/**
	 * Sets the timeout in milliseconds.
	 * 
	 * @param int $timeout The timeout
	 * @return \imperator\websocket\WebSocket The websocket, for chaining
	 */
	public function setTimeout($timeout) {
		$this->timeout['sec'] = (int)($timeout/1000);
		$this->timeout['usec'] = $timeout - $this->timeout['sec']*1000;
		return $this;
	}

	/**
	 * Returns the URL to receive connections on.
	 * 
	 * @return string The URL
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * Returns the port to receive connections on.
	 * 
	 * @return int The port number
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * Returns the path to receive connections on.
	 * 
	 * @return string The path
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Returns the logger used by this websocket.
	 * 
	 * @return \imperator\Logger The logger
	 */
	public function getLogger() {
		return $this->logger;
	}

	/**
	 * Makes this websocket start handling connections.
	 */
	public function start() {
		if(!$this->running) {
			$this->running = true;
			$this->connect();
			$this->run();
		}
	}

	/**
	 * Stops this websocket from handling connections permanently.
	 */
	public function stop() {
		$this->running = false;
		socket_close($this->socket);
	}

	/**
	 * Invokes the connection handler.
	 * 
	 * @internal For internal use only
	 * @param Message $message The received message
	 */
	public function handleMessage(Message $message) {
		try {
			$this->connectionHandler->onMessage($message);
		} catch(\Exception $e) {
			$this->logger->log(\imperator\Logger::WARNING, $e);
		}
	}

	/**
	 * Invokes the connection handler.
	 * 
	 * @internal For internal use only
	 * @param Message $message The received control frame
	 */
	public function handleControlFrame(Message $message) {
		try {
			$this->connectionHandler->onControlFrame($message);
		} catch(\Exception $e) {
			$this->logger->log(\imperator\Logger::WARNING, $e);
		}
	}

	/**
	 * Invokes the connection handler.
	 * 
	 * @internal For internal use only
	 * @param Connection $connection The connection that was opened
	 */
	public function handleConnectionOpen(Connection $connection) {
		try {
			$this->connectionHandler->onOpen($connection);
		} catch(\Exception $e) {
			$this->logger->log(\imperator\Logger::WARNING, $e);
		}
	}

	/**
	 * Invokes the connection handler.
	 * 
	 * @internal For internal use only
	 * @param Connection $connection The connection that was closed
	 */
	public function handleConnectionClose(Connection $connection) {
		try {
			$this->connectionHandler->onClose($connection);
		} catch(\Exception $e) {
			$this->logger->log(\imperator\Logger::WARNING, $e);
		}
	}

	private function connect() {
		$this->socket = stream_socket_server('tcp://'.$this->address.':'.$this->port, $err, $errStr/*, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN*/);
		if(!$this->socket) {
			$this->logger->log(\imperator\Logger::FATAL, $errStr.' ('.$err.')');
			$this->stop();
		}
		$this->sockets = array($this->socket);
	}

	private function run() {
		while($this->running) {
			$this->readConnections();
			$this->cleanUpConnections();
		}
	}

	private function readConnections() {
		$read = $this->sockets;
		$write = $except = null;
		stream_select($read, $write, $except, 10);
		foreach($read as $key => $socket) {
			if($socket == $this->socket) {
				$this->acceptConnection();
			} else {
				$connection = $this->connections[$key-1];
				$this->runConnection($connection);
				if($connection->isClosed()) {
					$this->containsClosed = true;
				}
			}
		}
	}

	private function acceptConnection() {
		$client = stream_socket_accept($this->socket);
		if($client !== false) {
			stream_set_timeout($client, $this->timeout['sec'], $this->timeout['usec']);
			$this->connections[] = new Connection($client, $this);
			$this->sockets[] = $client;
		}
	}

	private function runConnection(Connection $connection) {
		try {
			$connection->run();
		} catch(Exception $e) {
			$this->logger->log(\imperator\Logger::WARNING, $e);
			$connection->close();
		}
	}

	private function cleanUpConnections() {
		if($this->containsClosed) {
			$this->sockets = array($this->socket);
			$connections = array_filter($this->connections, function($conn) {
				return !$conn->isClosed();
			});
			$this->connections = array();
			foreach($connections as $conn) {
				$this->sockets[] = $conn->getSocket($this);
				$this->connections[] = $conn;
			}
		}
	}
}