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
		if($error !== null && $error['type'] == E_ERROR) {
			Imperator::getLogger()->log(Logger::LEVEL_FATAL, $error);
			if($this->mode == self::MODE_OUTPUT_PAGE) {
				Imperator::renderErrorPage(Imperator::getCurrentUser());
			} else if($this->mode == self::MODE_OUTPUT_JSON) {
				echo '{"error": "Fatal error"}';
			}
		}
	}

	public function error($errno, $errstr, $errfile, $errline) {
		if($errno == E_RECOVERABLE_ERROR) {
			throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
		}
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