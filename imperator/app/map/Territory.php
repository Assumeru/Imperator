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

	public function getName() {
		return $this->name;
	}

	public function getGame() {
		return $this->game;
	}

	public function setGame(\imperator\Game $game = null) {
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

	public function setOwner(\imperator\game\Player $owner) {
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

	/**
	 * @return Region[]:
	 */
	public function getRegions() {
		return $this->regions;
	}

	/**
	 * @return Territory[]
	 */
	public function getBorders() {
		return $this->borders;
	}

	public function borders(Territory $territory) {
		foreach($this->borders as $border) {
			if($border == $territory) {
				return true;
			}
		}
		return false;
	}
}