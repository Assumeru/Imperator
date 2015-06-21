<?php
namespace imperator\api\requests;
use imperator\Imperator;

class ForfeitGameRequest extends GameRequest {
	public function getType() {
		return 'forfeit';
	}

	public function handle(\imperator\User $user) {
		parent::handle($user);
		if($this->getGame()->getState() == \imperator\Game::STATE_COMBAT && $this->getGame()->playerHasToDefend($user)) {
			throw new \imperator\exceptions\InvalidRequestException($user->getLanguage()->translate('You cannot forfeit without finishing all battles.'));
		}
		$this->getGame()->forfeit($user);
	}
}