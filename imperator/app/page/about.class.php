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
		$this->setBodyContents($this->getAbout($user));
		parent::render($user);
	}

	private function getAbout(\imperator\User $user) {
		$language = $user->getLanguage();
		return Template::getInstance('about')->replace(array(
			'title' => $language->translate(self::NAME)
		))->getData();
	}
}