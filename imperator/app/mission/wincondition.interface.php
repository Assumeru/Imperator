<?php
namespace imperator\mission;

interface WinCondition {

	/**
	 * Checks if a user meets a condition in a game.
	 * 
	 * @param Player $user The user to check for
	 * @return bool True if this condition has been met
	 */
	public function isFulfilled(\imperator\game\Player $user);
}