<?php
namespace imperator;

class Logger {
	const LEVEL_FATAL = 1;
	const LEVEL_WARNING = 2;
	const LEVEL_INFO = 4;
	const EOL = "\n";
	private static $LEVELS = array(
		self::LEVEL_WARNING	=> 'WARNING',
		self::LEVEL_INFO	=> 'INFO',
		self::LEVEL_FATAL	=> 'FATAL'
	);

	public function log($level, $message) {
		if($message instanceof \Exception) {
			$message = $this->parseException($message);
		}
		echo $this->getHead($level).$message.self::EOL.self::EOL;
	}

	private function getHead($level) {
		return $this->getTimestamp().' '.$this->LEVELS[$level].self::EOL;
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