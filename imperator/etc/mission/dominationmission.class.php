<?php
namespace imperator\mission;

class DominationMission extends Mission {
	private $numTerritories;

	public function __construct($id, $numTerritories) {
		parent::__construct($id, 'Domination', 'To win this game you will have to conquer %1$d territories.', array(
			new TerritoriesCondition($numTerritories)
		));
		$this->numTerritories = $numTerritories;
	}

	public function getDescription(\imperator\Language $language) {
		return $language->translate(parent::getDescription($language), $this->numTerritories);
	}
}