<?php
namespace imperator\map;

class Territory {
	private $id;
	private $game;
	private $name;
	private $owner;
	private $units = 0;
	private $regions = array();
	private $borders = array();

	public function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
	}

	public function getGame() {
		return $this->game;
	}

	public function setGame(\imperator\Game $game) {
		$this->game = $game;
	}

	public function getId() {
		return $this->id;
	}

	public function getOwner() {
		return $this->owner;
	}

	public function getUnits() {
		return $this->units;
	}

	public function setOwner(\imperator\User $user) {
		$this->owner = $owner;
	}

	public function setUnits($units) {
		$this->units = $units;
	}

	public function addRegion(Region $region) {
		$this->regions[] = $region;
	}

	public function addBorder(Territory $territory) {
		$this->borders[] = $territory;
	}
}