<?php
namespace imperator\map;

class Region {
	private $id;
	private $name;
	private $units;
	private $territories = array();

	public function __construct($id, $name, $units) {
		$this->id = $id;
		$this->name = $name;
		$this->units = $units;
	}

	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function getUnitsPerTurn() {
		return $this->units;
	}

	public function addTerritory(Territory $territory) {
		$this->territories[] = $territory;
	}
}