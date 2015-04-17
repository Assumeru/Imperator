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
		} else {
			if($level <= $this->level) {
				if($level <= self::LEVEL_WARNING) {
					$file = $this->path.'/error.log';
				} else {
					$file = $this->path.'/output.log';
				}
				file_put_contents($file, $message, FILE_APPEND | LOCK_EX);
			}
		}
	}

	public function log($level, $message) {
		if($message instanceof \Exception) {
			$message = $this->parseException($message);
		}
		$this->output($this->getHead($level).$message.self::EOL.self::EOL, $level);
	}

	private function getHead($level) {
		return $this->getTimestamp().' '.self::$LEVELS[$level].self::EOL;
	}

	private function getTimestamp() {
		return date(\DateTime::ATOM);
	}

	private function parseException(\Exception $exception) {
		$out = array();
		$out[] = 'Exception: '.get_class($exception);
		$trace = $exception->getTrace();
		$out[] = $this->getExceptionSourceLine($trace[0], $exception->getLine());
		for($n=1; $n < count($trace); $n++) {
			$out[] = $this->getExceptionSourceLine($trace[$n], $trace[($n-1)]['line']);
		}
		return implode(self::EOL, $out);
	}

	private function getExceptionSourceLine(array $trace, $line) {
		$out = '	at ';
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