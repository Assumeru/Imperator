<?php
namespace imperator\page;

class LoginSplash extends DefaultPage {
	const NAME = 'Home';

	public function render(\imperator\User $user) {
		$language = $user->getLanguage();
		$this->setTitle($language->translate(self::NAME));
		$this->setBodyContents(Template::getInstance('splash', $language));
		parent::render($user);
	}

	public function canBeUsedBy(\imperator\User $user) {
		return true;
	}
}