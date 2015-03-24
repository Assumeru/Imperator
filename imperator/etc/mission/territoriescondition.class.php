<?php
namespace imperator\mission;

class TerritoriesCondition implements WinCondition {
	private $numTerritories;

	public function __construct($numTerritories) {
		$this->numTerritories = $numTerritories;
	}

	public function isFulfilled(\imperator\Game $game, \imperator\User $user) {
		return count($game->getMap()->getTerritoriesFor($user)) >= $this->numTerritories;
	}
}