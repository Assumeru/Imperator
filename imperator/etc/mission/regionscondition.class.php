<?php
namespace imperator\mission;

class RegionsCondition implements WinCondition {
	private $numRegions;

	public function __construct($numRegions) {
		$this->numRegions = $numRegions;
	}

	public function isFulfilled(\imperator\game\Player $user) {
		$regions = $user->getGame()->getMap()->getRegions();
		$numRegions = 0;
		foreach($regions as $region) {
			if($region->isOwnedBy($user)) {
				$numRegions++;
				if($numRegions >= $this->numRegions) {
					return true;
				}
			}
		}
		return false;
	}
}