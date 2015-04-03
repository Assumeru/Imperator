<?php
namespace imperator\page;

class LongPolling extends Page {
	const URL = 'ajax';

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	public function render(\imperator\User $user) {
		echo 'You made a request';
	}
}