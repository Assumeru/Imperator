<?php
namespace imperator\game;

class Cards {
	const CARD_NONE = -1;
	const CARD_ARTILLERY = 0;
	const CARD_CAVALRY = 1;
	const CARD_INFANTRY = 2;
	const CARD_JOKER = 3;
	const MAX_CARDS = 5;
	const MAX_JOKERS = 2;

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
		$this->calculateNumberOfCards();
	}

	private function calculateNumberOfCards() {
		$this->numCards = $this->artillery + $this->cavalry + $this->infantry + $this->jokers;
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
		$this->calculateNumberOfCards();
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
		$this->calculateNumberOfCards();
	}

	public function setCavalry($cavalry) {
		$this->cavalry = $cavalry;
		$this->calculateNumberOfCards();
	}

	public function setInfantry($infantry) {
		$this->infantry = $infantry;
		$this->calculateNumberOfCards();
	}

	public function setJokers($jokers) {
		$this->jokers = $jokers;
		$this->calculateNumberOfCards();
	}

	/**
	 * Returns an array of CARD_?s.
	 * 
	 * @return int[]
	 */
	public function getCards() {
		$cards = array();
		for($n = 0; $n < $this->artillery; $n++) {
			$cards[] = static::CARD_ARTILLERY;
		}
		for($n = 0; $n < $this->infantry; $n++) {
			$cards[] = static::CARD_INFANTRY;
		}
		for($n = 0; $n < $this->cavalry; $n++) {
			$cards[] = static::CARD_CAVALRY;
		}
		for($n = 0; $n < $this->jokers; $n++) {
			$cards[] = static::CARD_JOKER;
		}
		return $cards;
	}

	/**
	 * @param int $combination
	 * @return boolean
	 */
	public function canPlayCombination($combination) {
		if($combination == 4) {
			return $this->artillery + $this->jokers >= 3;
		} else if($combination == 6) {
			return $this->infantry + $this->jokers >= 3;
		} else if($combination == 8) {
			return $this->cavalry + $this->jokers >= 3;
		}
		return ($this->artillery + $this->infantry + $this->cavalry >= 1 && $this->jokers >= 2)
			|| ($this->artillery >= 1 && $this->infantry >= 1 && $this->cavalry >= 1)
			|| ($this->jokers >= 1
				&& (($this->artillery >= 1 && $this->infantry >= 1)
				|| ($this->artillery >= 1 && $this->cavalry >= 1)
				|| ($this->infantry >= 1 && $this->cavalry >= 1)));
	}

	private function removeBestCombination($card) {
		$num = $this->getNumberOf($card);
		if($num >= 3) {
			$this->setNumberOf($card, $num - 3);
			return array($card, $card, $card);
		} else if($num == 2) {
			$this->setNumberOf($card, $num - 2);
			$this->jokers -= 1;
			return array($card, $card, static::CARD_JOKER);
		}
		$this->setNumberOf($card, $num - 1);
		$this->jokers -= 2;
		return array($card, static::CARD_JOKER, static::CARD_JOKER);
	}

	/**
	 * @param int $units
	 * @return int[] The combination of cards that was removed
	 */
	public function removeCombination($units) {
		if($units == 4) {
			return $this->removeBestCombination(static::CARD_ARTILLERY);
		} else if($units == 6) {
			return $this->removeBestCombination(static::CARD_INFANTRY);
		} else if($units == 8) {
			return $this->removeBestCombination(static::CARD_CAVALRY);
		}
		if($this->artillery >= 1 && $this->cavalry >= 1 && $this->infantry >= 1) {
			$this->artillery--;
			$this->cavalry--;
			$this->infantry--;
			return array(static::CARD_ARTILLERY, static::CARD_INFANTRY, static::CARD_CAVALRY);
		} else if($this->artillery >= 1 && $this->cavalry >= 1) {
			$this->artillery--;
			$this->cavalry--;
			$this->jokers--;
			return array(static::CARD_ARTILLERY, static::CARD_JOKER, static::CARD_CAVALRY);
		} else if($this->cavalry >= 1 && $this->infantry >= 1) {
			$this->cavalry--;
			$this->infantry--;
			$this->jokers--;
			return array(static::CARD_JOKER, static::CARD_INFANTRY, static::CARD_CAVALRY);
		} else if($this->artillery >= 1 && $this->infantry >= 1) {
			$this->artillery--;
			$this->infantry--;
			$this->jokers--;
			return array(static::CARD_ARTILLERY, static::CARD_INFANTRY, static::CARD_JOKER);
		} else if($this->artillery >= 1) {
			$this->artillery--;
			$this->jokers -= 2;
			return array(static::CARD_ARTILLERY, static::CARD_JOKER, static::CARD_JOKER);
		} else if($this->cavalry >= 1) {
			$this->cavalry--;
			$this->jokers -= 2;
			return array(static::CARD_JOKER, static::CARD_JOKER, static::CARD_CAVALRY);
		}
		$this->infantry--;
		$this->jokers -= 2;
		return array(static::CARD_JOKER, static::CARD_INFANTRY, static::CARD_JOKER);
	}

	public static function isCard($card) {
		return $card == static::CARD_NONE
			|| $card == static::CARD_ARTILLERY
			|| $card == static::CARD_CAVALRY
			|| $card == static::CARD_INFANTRY
			|| $card == static::CARD_JOKER;
	}

	public static function getCardNames(\imperator\Language $language) {
		return array(
			static::CARD_ARTILLERY => $language->translate('Artillery'),
			static::CARD_CAVALRY => $language->translate('Cavalry'),
			static::CARD_INFANTRY => $language->translate('Infantry'),
			static::CARD_JOKER => $language->translate('Joker'),
			static::CARD_NONE => $language->translate('None')
		);
	}

	public static function isValidUnitAmount($units) {
		return $units == 4
			|| $units == 6
			|| $units == 8
			|| $units == 10;
	}
}