<?php
namespace imperator\mission;

class DominationMission extends Mission {
	const DESCRIPTION = 'To win this game you will have to conquer %1$d territories.';
	private $numTerritories;

	public function __construct($id, $numTerritories) {
		parent::__construct($id, 'Domination', self::DESCRIPTION, array(
			new TerritoriesCondition($numTerritories)
		));
		$this->numTerritories = $numTerritories;
	}

	public function getDescription(\imperator\Language $language) {
		return $language->translate(self::DESCRIPTION, $this->numTerritories);
	}
}