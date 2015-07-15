<?php
namespace imperator\mission;

interface WinCondition {

	/**
	 * Checks if a user meets a condition in a game.
	 * 
	 * @param PlayerMission $mission The user's mission
	 * @return bool True if this condition has been met
	 */
	public function isFulfilled(PlayerMission $mission);
}