<?php
namespace imperator\api\requests;
use imperator\Imperator;

class PlaceUnitsGameRequest extends GameRequest {
	private $units;
	private $territory;

	public function __construct($gid, $units, $territory) {
		parent::__construct($gid);
		$this->units = (int)$units;
		$this->territory = $territory;
	}

	public function getUnits() {
		return $this->units;
	}

	public function getTerritory() {
		return $this->territory;
	}
}