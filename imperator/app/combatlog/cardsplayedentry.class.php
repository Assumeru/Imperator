<?php
namespace imperator\combatlog;

class CardsPlayedEntry extends LogEntry {
	private $cards;

	public function __construct($time, \imperator\game\Player $user, array $cards) {
		parent::__construct($time, $user);
		$this->cards = $cards;
	}

	protected function getCards() {
		return $this->cards;
	}
}