<?php
namespace imperator\test;

class TestLogger extends \imperator\Logger {
	public function __construct() {
		parent::__construct(false, static::LEVEL_INFO);
	}

	public function log($level, $message) {
		if($level <= static::LEVEL_WARNING) {
			echo '<span style="color:red">';
		}
		parent::log($level, $message);
		if($level <= static::LEVEL_WARNING) {
			echo '</span>';
		}
	}
}