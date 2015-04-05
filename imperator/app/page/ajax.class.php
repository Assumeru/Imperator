<?php
namespace imperator\page;

class Ajax extends Page {
	const URL = 'ajax';

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	public function render(\imperator\User $user) {
		\imperator\api\LongPolling::handleRequest(new \imperator\api\Request($_POST, $user));
	}
}