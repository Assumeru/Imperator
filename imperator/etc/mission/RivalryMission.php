<?php
namespace imperator\mission;
use imperator\Imperator;

class RivalryMission extends MapMission {
	public function __construct($id) {
		parent::__construct($id, 'Rivalry', 'To win this game you will have to conquer the last of an opponent\'s territories.', array(
			new EliminateCondition()
		));
	}

	public function getDescription(\imperator\Language $language, PlayerMission $mission = null) {
		if($mission === null || ($player = $mission->getGame()->getPlayerById($mission->getUid())) === null) {
			return parent::getDescription($language);
		}
		return $language->translate('To win this game you will have to conquer the last of %1$s\'s territories.', $player->getName());
	}
}