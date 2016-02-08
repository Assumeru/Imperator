<?php
namespace imperator;

class Logger {
	const LEVEL_FATAL = 1;
	const LEVEL_WARNING = 2;
	const LEVEL_DEBUG = 3;
	const LEVEL_INFO = 4;
	const EOL = "\n";
	private static $LEVELS = array(
		self::LEVEL_FATAL	=> 'FATAL',
		self::LEVEL_WARNING	=> 'WARNING',
		self::LEVEL_DEBUG	=> 'DEBUG',
		self::LEVEL_INFO	=> 'INFO'
	);

	private $path;
	private $level;

	public function __construct($path, $level) {
		$this->path = $path;
		$this->level = $level;
	}

	private function output($message, $level) {
		if(!$this->path) {
			echo $message;
		} else if($level <= $this->level) {
			if($level <= self::LEVEL_WARNING) {
				$file = $this->path.'/error.log';
			} else {
				$file = $this->path.'/output.log';
			}
			file_put_contents($file, $message, FILE_APPEND | LOCK_EX);
		}
	}

	public function log($level, $message) {
		if($message instanceof \Exception) {
			$message = $this->parseException($message);
		} else if(is_array($message)) {
			$message = var_export($message, true);
		}
		$this->output($this->getHead($level).$message.self::EOL.self::EOL, $level);
	}

	public function e($message) {
		$this->log(static::LEVEL_FATAL, $message);
	}

	public function w($message) {
		$this->log(static::LEVEL_WARNING, $message);
	}

	public function d($message) {
		$this->log(static::LEVEL_DEBUG, $message);
	}

	public function i($message) {
		$this->log(static::LEVEL_INFO, $message);
	}

	private function getHead($level) {
		return $this->getTimestamp().' '.self::$LEVELS[$level].self::EOL;
	}

	private function getTimestamp() {
		return date(\DateTime::ATOM);
	}

	private function parseException(\Exception $exception, $depth = 0) {
		$out = array();
		$out[] = 'Exception: '.get_class($exception);
		$out[] = $exception->getMessage();
		for($n = 0; $n < $depth; $n++) {
			$out[0] = '	'.$out[0];
			$out[1] = '	'.$out[1];
		}
		$trace = $exception->getTrace();
		$out[] = $this->getExceptionSourceLine($trace[0], $exception->getLine(), $depth);
		for($n = 1; $n < count($trace); $n++) {
			$out[] = $this->getExceptionSourceLine($trace[$n], $trace[($n-1)]['line'], $depth);
		}
		if($exception->getPrevious() !== null && $depth < 5) {
			$out[] = 'Caused by:';
			$out[] = $this->parseException($exception->getPrevious(), $depth + 1);
		}
		return implode(self::EOL, $out);
	}

	private function getExceptionSourceLine(array $trace, $line, $depth) {
		$out = '	';
		for($n = 0; $n < $depth; $n++) {
			$out .= '	';
		}
		$out .= 'at ';
		if(!empty($trace['class'])) {
			$out .= $trace['class'].' ';
			if(!empty($trace['type'])) {
				$out .= $trace['type'].' ';
			}
		}
		if(!empty($trace['function'])) {
			$out .= $trace['function'].' ';
		}
		$out .= '('.$trace['file'].' : '.$line.')';
		return $out;
	}
}