<?php
namespace imperator\api\requests;

class KickGameRequest extends GameRequest {
	private $uid;

	public function __construct($gid, $uid) {
		parent::__construct($gid);
		$this->uid = (int)$uid;
	}

	protected function getUid() {
		return $this->uid;
	}

	public function handle(\imperator\User $user) {
		$game = $this->getGame();
		if($game->hasStarted() && !$game->hasEnded()) {
		} else if(!$game->getOwner()->equals($user)) {
			throw new \imperator\exceptions\InvalidRequestException('Only the game owner can kick players.');
		} else if($user->getId() == $this->uid) {
			throw new \imperator\exceptions\InvalidRequestException('You cannot kick yourself.');
		}
		$game->removeUser($game->getPlayerById($this->uid));
	}
}