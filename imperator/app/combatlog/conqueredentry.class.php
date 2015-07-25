<?php
namespace imperator\combatlog;

class ConqueredEntry extends LogEntry {
	const TYPE = 0;
	private $territory;

	public function __construct($time, \imperator\game\Player $user, \imperator\map\Territory $territory) {
		parent::__construct($time, $user);
		$this->territory = $territory;
	}

	protected function getTerritory() {
		return $this->territory;
	}

	public function getMessage(\imperator\Language $language) {
		return $language->translate(
			'%1$s conquered %2$s.',
			\imperator\page\DefaultPage::getProfileLink($this->getUser()),
			\imperator\page\Template::getInstance('game_territory_link', $language)->setVariables(array('territory' => $this->territory))->execute()
		);
	}
}