<?php
namespace imperator\mission;

class EliminateCondition implements WinCondition, UidCondition {
	private $uid;

	public function __construct($uid = null) {
		$this->uid = $uid;
	}

	public function isFulfilled(\imperator\game\Player $user) {
		return $user->getState() == \imperator\game\Player::STATE_DESTROYED_RIVAL;
	}

	public function getUid() {
		return $this->uid;
	}

	public function setUid($uid) {
		$this->uid = $uid;
	}
}