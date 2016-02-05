<?php
namespace imperator\exceptions;

class InvalidRequestException extends ImperatorException {
	private $args;

	public function __construct($message) {
		$prev = null;
		$this->args = array($message);
		if(func_num_args() > 1) {
			$args = func_get_args();
			$this->args = $args;
			array_shift($args);
			if($args[0] instanceof \Exception) {
				$prev = $args[0];
				array_shift($args);
			}
			$message = vsprintf($message, $args);
		}
		parent::__construct($message, 0, $prev);
	}

	public function getUserFriendlyMessage(\imperator\User $user) {
		return call_user_func_array(array($user->getLanguage(), 'translate'), $this->args);
	}
}