<?php
namespace imperator\combatlog;
use imperator\Imperator;

class CardsPlayedEntry extends LogEntry {
	const TYPE = 4;
	private $cards;
	private $units;

	public function __construct($time, \imperator\game\Player $user, array $cards, $units) {
		parent::__construct($time, $user);
		$this->cards = $cards;
		$this->units = $units;
	}

	public function getCards() {
		return $this->cards;
	}

	public function getUnits() {
		return $this->units;
	}

	public function getMessage(\imperator\Language $language) {
		return $language->translate(
			'%1$s played %2$s for %3$d.',
			\imperator\page\DefaultPage::getProfileLink($this->getUser()),
			\imperator\page\Template::getInstance('game_cards')->setVariables(array(
				'url' => Imperator::getSettings()->getBaseURL().'/img/cards/%1$s.png',
				'names' => \imperator\game\Cards::getCardNames($language),
				'cards' => $this->cards
			))->execute(),
			$this->units
		);
	}

	public function save() {
		Imperator::getDatabaseManager()->getTable('CombatLog')->saveCardsEntry($this);
	}
}