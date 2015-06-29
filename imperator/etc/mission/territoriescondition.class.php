<?php
namespace imperator\mission;

class TerritoriesCondition implements WinCondition {
	private $numTerritories;

	public function __construct($numTerritories) {
		$this->numTerritories = $numTerritories;
	}

	public function isFulfilled(\imperator\game\Player $user) {
		return count($user->getTerritories()) >= $this->numTerritories;
	}
}