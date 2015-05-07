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

	/**
	 * Returns the number of cards of a specific type.
	 * 
	 * @param int $card One of CARD_ARTILLERY, CARD_CAVALRY, CARD_INFANTRY, or CARD_JOKER
	 * @return int
	 */
	public function getNumberOf($card) {
		if($card == static::CARD_ARTILLERY) {
			return $this->artillery;
		} else if($card == static::CARD_CAVALRY) {
			return $this->cavalry;
		} else if($card == static::CARD_INFANTRY) {
			return $this->infantry;
		}
		return $this->jokers;
	}

	/**
	 * Sets the number of cards of the specified type to the given value.
	 * 
	 * @param int $card One of CARD_ARTILLERY, CARD_CAVALRY, CARD_INFANTRY, or CARD_JOKER
	 * @param int $value
	 */
	public function setNumberOf($card, $value) {
		if($card == static::CARD_ARTILLERY) {
			$this->artillery = $value;
		} else if($card == static::CARD_CAVALRY) {
			$this->cavalry = $value;
		} else if($card == static::CARD_INFANTRY) {
			$this->infantry = $value;
		} else if($card == static::CARD_JOKER) {
			$this->jokers = $value;
		}
	}

	public function getArtillery() {
		return $this->artillery;
	}

	public function getCavalry() {
		return $this->cavalry;
	}

	public function getInfantry() {
		return $this->infantry;
	}

	public function getJokers() {
		return $this->jokers;
	}

	public function setArtillery($artillery) {
		$this->artillery = $artillery;
	}

	public function setCavalry($cavalry) {
		$this->cavalry = $cavalry;
	}

	public function setInfantry($infantry) {
		$this->infantry = $infantry;
	}

	public function setJokers($jokers) {
		$this->jokers = $jokers;
	}

	public static function isCard($card) {
		return $card == static::CARD_NONE
			|| $card == static::CARD_ARTILLERY
			|| $card == static::CARD_CAVALRY
			|| $card == static::CARD_INFANTRY
			|| $card == static::CARD_JOKER;
	}
}