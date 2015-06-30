<?php
namespace imperator\api\requests;
use imperator\Imperator;

class ForfeitGameRequest extends GameRequest {
	public function getType() {
		return 'forfeit';
	}

	public function handle(\imperator\User $user) {
		parent::handle($user);
		$game = $this->getGame();
		if($game->getState() == \imperator\Game::STATE_COMBAT && $game->playerHasToDefend($user)) {
			throw new \imperator\exceptions\InvalidRequestException('You cannot forfeit without finishing all battles.');
		}
		$this->getGame()->forfeit($game->getPlayerByUser($user));
	}
}