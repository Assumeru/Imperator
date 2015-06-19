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

	public function getCard() {
		return $this->card;
	}
}