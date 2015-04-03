<?php
namespace imperator\mission;

class RivalryMission extends Mission {
	public function __construct($id, $uid) {
		parent::__construct($id, 'Rivalry', 'Two win this game you will have to conquer the last of %1$s\'s territories.', array(
			new EliminateCondition($uid)
		));
	}
}