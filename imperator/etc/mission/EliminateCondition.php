<?php
namespace imperator\mission;

class EliminateCondition implements WinCondition {
	public function isFulfilled(PlayerMission $mission) {
		return $mission->getPlayer()->getState() == \imperator\game\Player::STATE_DESTROYED_RIVAL;
	}
}