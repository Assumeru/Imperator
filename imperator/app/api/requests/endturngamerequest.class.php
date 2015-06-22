<?php
namespace imperator\api\requests;
use imperator\Imperator;

class EndTurnGameRequest extends GameRequest {
	private $card;

	public function __construct($gid, $card = \imperator\game\Cards::CARD_NONE) {
		parent::__construct($gid);
		if(\imperator\game\Cards::isCard($card)) {
			$this->card = $card;
		} else {
			$this->card = \imperator\game\Cards::CARD_NONE;
		}
	}

	public function getType() {
		return 'end-turn';
	}

	protected function getCard() {
		return $this->card;
	}

	public function handle(\imperator\User $user) {
		parent::handle($user);
		$this->throwIfNotMyTurn($user);
		if($this->getGame()->getState() == \imperator\Game::STATE_COMBAT && $this->getGame()->hasOngoingBattles()) {
			throw new \imperator\exceptions\InvalidRequestException('You cannot end your turn without finishing all battles.');
		}
		$this->getGame()->loadMap();
		$reply = array();
		if($this->getGame()->hasConquered()) {
			$reply['card'] = $this->getGame()->giveCard($this->getGame()->getPlayerByUser($user), $this->getCard());
		}
		$this->getGame()->nextTurn();
		$reply['turn'] = $this->getGame()->getTurn();
		$reply['time'] = $this->getGame()->getTime();
		$reply['state'] = $this->getGame()->getState();
		return $reply;
	}
}