<?php
namespace imperator\page;

class LoginSplash extends DefaultPage {
	const NAME = 'Home';

	public function render(\imperator\User $user) {
		$this->setTitle($user->getLanguage()->translate(self::NAME));
		$this->setBodyContents($this->getSplash($user));
		parent::render($user);
	}

	public function canBeUsedBy(\imperator\User $user) {
		return true;
	}

	private function getSplash(\imperator\User $user) {
		$lang = $user->getLanguage();
		return Template::getInstance('splash')->replace(array(
			'welcome' => $lang->translate('Welcome to Imperator'),
			'login' => $lang->translate('Please login to continue.')
		))->getData();
	}
}