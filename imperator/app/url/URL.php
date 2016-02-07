<?php
namespace imperator\url;

class URL {
	private $path;

	protected function __construct($path) {
		$this->path = $path;
	}

	public function __toString() {
		return $this->path;
	}

	public function addArgument($key, $value) {
		if(strpos($this->path, '?') === false) {
			$this->path .= '?';
		} else {
			$this->path .= '&';
		}
		$this->path .= str_replace('%2F', '/', urlencode($key) . '=' . urlencode($value));
		return $this;
	}

	protected function addDirect($string) {
		$this->path .= $string;
	}
}