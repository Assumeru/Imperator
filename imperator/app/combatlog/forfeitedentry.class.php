<?php
namespace imperator\combatlog;

class ForfeitedEntry extends LogEntry {
	const TYPE = 3;

	public function getMessage(\imperator\Language $language) {
		return $language->translate('%1$s has forfeited the game.', \imperator\page\DefaultPage::getProfileLink($this->getUser()));
	}
}