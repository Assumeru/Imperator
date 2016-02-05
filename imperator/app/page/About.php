<?php
namespace imperator\page;

class About extends DefaultPage {
	const NAME = 'About';
	const URL = 'about';

	public function canBeUsedBy(\imperator\User $user) {
		return true;
	}

	public function render(\imperator\User $user) {
		$this->setTitle($user->getLanguage()->translate(self::NAME));
		$this->setBodyContents(Template::getInstance('about', $user->getLanguage()));
		parent::render($user);
	}
}