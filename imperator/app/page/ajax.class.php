<?php
namespace imperator\page;
use imperator\Imperator;

class Ajax extends Page {
	const URL = 'ajax';

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	public function render(\imperator\User $user) {
		echo Imperator::handleApiRequest(
			\imperator\api\Api::LONGPOLLING,
			new \imperator\api\Request($_POST),
			$user
		);
	}
}