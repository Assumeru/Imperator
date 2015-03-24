<?php
namespace imperator\page;

class Index extends Page {
	const NAME = 'Home';

	public function render(\imperator\User $user) {
		if($user->isLoggedIn()) {
			$page = new GameList();
		} else {
			$page = new LoginSplash();
		}
		$page->render($user);
	}

	public function canBeUsedBy(\imperator\User $user) {
		return true;
	}
}