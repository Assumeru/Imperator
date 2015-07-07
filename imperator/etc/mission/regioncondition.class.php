<?php
namespace imperator\mission;

class RegionCondition implements WinCondition {
	private $regions;

	public function __construct($regions) {
		if(is_array($regions)) {
			$this->regions = $regions;
		} else {
			$this->regions = func_get_args();
		}
	}

	public function isFulfilled(\imperator\game\Player $user) {
		$regions = $user->getGame()->getMap()->getRegions();
		foreach($this->regions as $id) {
			if(!$regions[$id]->isOwnedBy($user)) {
				return false;
			}
		}
		return true;
	}
}