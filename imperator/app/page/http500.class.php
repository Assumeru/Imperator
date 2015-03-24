<?php
namespace imperator\page;
use imperator\Imperator;

class HTTP500 extends HTTPError {
	public function __construct() {
		parent::__construct('500 Internal Server Error', 'An error occurred while loading this page.');
	}

	public function render(\imperator\User $user, \Exception $e = null) {
		parent::render($user);
		if($e) {
			echo '<!-- ';
			Imperator::getLogger()->log(\imperator\Logger::LEVEL_FATAL, $e);
			echo ' -->';
		}
	}
}