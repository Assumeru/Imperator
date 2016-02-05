<?php
namespace imperator\mission;

class TerritoryCondition implements WinCondition {
	private $territories;

	public function __construct($territories) {
		if(is_array($territories)) {
			$this->territories = $territories;
		} else {
			$this->territories = func_get_args();
		}
	}

	public function isFulfilled(PlayerMission $mission) {
		$user = $mission->getPlayer();
		$territories = $user->getGame()->getMap()->getTerritories();
		foreach($this->territories as $id) {
			if($territories[$id]->getOwner() != $user) {
				return false;
			}
		}
		return true;
	}
}