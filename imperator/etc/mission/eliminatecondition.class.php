<?php
namespace imperator\mission;

class EliminateCondition implements WinCondition, UidCondition {
	private $uid;

	public function __construct($uid = null) {
		$this->uid = $uid;
	}

	public function isFulfilled(\imperator\Game $game, \imperator\User $user) {
		return false;
	}

	public function getUid() {
		return $this->uid;
	}

	public function setUid() {
		return $this->uid;
	}
}