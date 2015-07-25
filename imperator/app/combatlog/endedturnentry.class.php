<?php
namespace imperator\combatlog;

class EndedTurnEntry extends LogEntry {
	const TYPE = 2;

	public function getMessage(\imperator\Language $language) {
		return $language->translate('%1$s\'s turn has ended.', \imperator\page\DefaultPage::getProfileLink($this->getUser()));
	}
}