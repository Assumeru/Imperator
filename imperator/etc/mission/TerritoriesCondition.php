<?php
namespace imperator\mission;

class TerritoriesCondition implements WinCondition {
	private $numTerritories;

	public function __construct($numTerritories) {
		$this->numTerritories = $numTerritories;
	}

	public function isFulfilled(PlayerMission $mission) {
		return count($mission->getPlayer()->getTerritories()) >= $this->numTerritories;
	}
}