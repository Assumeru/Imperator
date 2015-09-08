<?php
namespace imperator\url;

class URL {
	private $path;

	protected function __construct($path) {
		$this->path = $path;
	}

	public function __toString() {
		return $path;
	}

	public function addArgument($key, $value) {
		if(strpos($this->path, '?') === false) {
			$this->path .= '?';
		} else {
			$this->path .= '&';
		}
		$this->path .= urlencode($key) . ' = ' . urlencode($value);
		return $this;
	}
}