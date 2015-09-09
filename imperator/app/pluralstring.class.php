<?php
namespace imperator;

class PluralString {
	private $singular;
	private $plural;
	private $amount;

	/**
	 * @param string $singular
	 * @param string $plural
	 * @param int $amount
	 */
	public function __construct($singular, $plural, $amount) {
		$this->singular = $singular;
		$this->plural = $plural;
		$this->amount = $amount;
	}

	public function __toString() {
		return $this->plural;
	}
}