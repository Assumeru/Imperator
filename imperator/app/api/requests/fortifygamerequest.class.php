<?php
namespace imperator\api\requests;
use imperator\Imperator;

class FortifyGameRequest extends GameRequest {
	public function getType() {
		return 'fortify';
	}

	public function handle(\imperator\User $user) {
		parent::handle($user);
		$this->throwIfNotMyTurn($user);
		$game = $this->getGame();
		if($game->getState() != \imperator\Game::STATE_TURN_START) {
			throw new \imperator\exceptions\InvalidRequestException('Cannot fortify after attacking.');
		}
		$game->fortify($game->getPlayerByUser($user));
		return array(
			'units' => $this->getGame()->getUnits(),
			'state' => $this->getGame()->getState()
		);
	}
}