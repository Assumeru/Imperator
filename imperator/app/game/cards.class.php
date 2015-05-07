<?php
namespace imperator\game;

class Cards {
	const CARD_NONE = -1;
	const CARD_ARTILLERY = 0;
	const CARD_CAVALRY = 1;
	const CARD_INFANTRY = 2;
	const CARD_JOKER = 3;
	const MAX_CARDS = 5;

	private $artillery;
	private $cavalry;
	private $infantry;
	private $jokers;
	private $numCards;

	public function __construct($artillery, $cavalry, $infantry, $jokers) {
		$this->artillery = $artillery;
		$this->cavalry = $cavalry;
		$this->infantry = $infantry;
		$this->jokers = $jokers;
		$this->numCards = $artillery + $cavalry + $infantry + $jokers;
	}

	public function getNumberOfCards() {
		return $this->numCards;
	}

	public static function isCard($card) {
		return $card == static::CARD_NONE
			|| $card == static::CARD_ARTILLERY
			|| $card == static::CARD_CAVALRY
			|| $card == static::CARD_INFANTRY
			|| $card == static::CARD_JOKER;
	}
}