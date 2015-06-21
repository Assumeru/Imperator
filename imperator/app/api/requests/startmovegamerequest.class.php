<?php
namespace imperator\api\requests;
use imperator\Imperator;

class StartMoveGameRequest extends GameRequest {
	public function getType() {
		return 'start-move';
	}

	public function handle(\imperator\User $user) {
		parent::handle($user);
		$this->throwIfNotMyTurn($user);
		if($this->getGame()->getState() != \imperator\Game::STATE_COMBAT) {
			throw new \imperator\exceptions\InvalidRequestException('Cannot move before attacking.');
		}
		if($this->getGame()->hasOngoingBattles()) {
			throw new \imperator\exceptions\InvalidRequestException($user->getLanguage()->translate('All battles need to finish before units can be moved.'));
		}
		$this->getGame()->startMove();
		return array(
			'state' => $this->getGame()->getState(),
			'units' => $this->getGame()->getUnits()
		);
	}
}