<?php
namespace imperator\mission;

class EliminateCondition implements WinCondition {
	private $uid;

	public function __construct($uid = null) {
		$this->uid = $uid;
	}

	public function isFulfilled(\imperator\Game $game, \imperator\User $user) {
		return false;
	}
}