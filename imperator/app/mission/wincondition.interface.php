<?php
namespace imperator\mission;

interface WinCondition {

	/**
	 * Checks if a user meets a condition in a game.
	 * 
	 * @param Game $game The game to check in
	 * @param User $user The user to check for
	 * @return bool True if this condition has been met
	 */
	public function isFulfilled(\imperator\Game $game, \imperator\User $user);
}