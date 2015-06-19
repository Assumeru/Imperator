<?php
namespace imperator\api\requests;
use imperator\Imperator;

class PlayCardsGameRequest extends GameRequest {
	private $units;

	public function __construct($gid, $units) {
		parent::__construct($gid);
		$this->units = (int)$units;
	}

	public function getUnits() {
		return $this->units;
	}
}