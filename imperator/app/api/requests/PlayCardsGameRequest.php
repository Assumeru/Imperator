<?php
namespace imperator\api\requests;

class PlayCardsGameRequest extends GameRequest {
	private $units;

	public function __construct($gid, $units) {
		parent::__construct($gid);
		$this->units = (int)$units;
	}

	public function getType() {
		return 'play-cards';
	}

	protected function getUnits() {
		return $this->units;
	}

	public function handle(\imperator\User $user) {
		parent::handle($user);
		$this->throwIfNotMyTurn($user);
		if($this->getGame()->getState() != \imperator\Game::STATE_TURN_START && $this->getGame()->getState() != \imperator\Game::STATE_FORTIFY) {
			throw new \imperator\exceptions\InvalidRequestException('Cannot play cards after attacking.');
		}
		$player = $this->getGame()->getPlayerByUser($user);
		$cards = $player->getCards();
		if(!$cards->canPlayCombination($this->getUnits())) {
			throw new \imperator\exceptions\InvalidRequestException('You do not have the required cards to place %1$d units.', $this->getUnits());
		}
		$this->getGame()->playCardCombination($player, $this->getUnits());
		return array(
			'units' => $this->getGame()->getUnits(),
			'cards' => array(
				\imperator\game\Cards::CARD_ARTILLERY => $cards->getArtillery(),
				\imperator\game\Cards::CARD_CAVALRY => $cards->getCavalry(),
				\imperator\game\Cards::CARD_INFANTRY => $cards->getInfantry(),
				\imperator\game\Cards::CARD_JOKER => $cards->getJokers()
			),
			'update' => $this->getGame()->getTime()
		);
	}
}