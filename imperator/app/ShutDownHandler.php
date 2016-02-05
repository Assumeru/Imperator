<?php
namespace imperator;

class ShutDownHandler {
	const MODE_OUTPUT_NOTHING = 0;
	const MODE_OUTPUT_PAGE = 1;
	const MODE_OUTPUT_JSON = 2;
	private $mode;

	public function setMode($mode) {
		$this->mode = $mode;
	}

	public function shutdown() {
		$error = error_get_last();
		if($error !== null && ($error['type'] == E_ERROR || $error['type'] == E_CORE_ERROR || $error['type'] == E_COMPILE_ERROR)) {
			Imperator::getLogger()->log(Logger::LEVEL_FATAL, $error);
			while(ob_get_level() > 0) {
				ob_end_clean();
			}
			if($this->mode == self::MODE_OUTPUT_PAGE) {
				Imperator::renderErrorPage(Imperator::getCurrentUser());
			} else if($this->mode == self::MODE_OUTPUT_JSON) {
				header('Content-Type: application/json');
				header('HTTP/1.0 500 Internal server error');
				echo '{"error": "Fatal error"}';
			}
		}
	}

	public function error($errno, $errstr, $errfile, $errline) {
		$exception = new \ErrorException($errstr, $errno, 0, $errfile, $errline);
		if($errno == E_RECOVERABLE_ERROR) {
			throw $exception;
		}
		switch($errno) {
			case E_RECOVERABLE_ERROR:
				throw $exception;
				break;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
				$level = Logger::LEVEL_WARNING;
				break;
			case E_PARSE:
				$level = Logger::LEVEL_DEBUG;
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
			case E_STRICT:
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$level = Logger::LEVEL_INFO;
				break;
			default:
				return false;
		}
		Imperator::getLogger()->log($level, $exception);
		return false;
	}

	public function register() {
		register_shutdown_function(array($this, 'shutdown'));
		$this->resetErrorHandler();
	}

	public function resetErrorHandler() {
		set_error_handler(array($this, 'error'));
	}
}