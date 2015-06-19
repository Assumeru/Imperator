<?php
namespace imperator\api\requests;
use imperator\Imperator;

class GameRequest extends \imperator\api\Request {
	private $gid;

	public static function buildRequest(array $params) {
		if(isset($params['gid']) && is_numeric($params['gid']) && isset($params['type'])) {
			if($params['type'] == 'forfeit') {
				return new ForfeitGameRequest($params['gid']);
			} else if($params['type'] == 'fortify') {
				return new FortifyGameRequest($params['gid']);
			} else if($params['type'] == 'start-move') {
				return new StartMoveGameRequest($params['gid']);
			} else if($params['type'] == 'end-turn') {
				return new EndTurnGameRequest($params['gid'], isset($params['card']) && is_numeric($params['card']) ? $params['card'] : \imperator\game\Cards::CARD_NONE);
			} else if($params['type'] == 'play-cards' && isset($params['units']) && is_numeric($params['units']) && \imperator\game\Cards::isValidUnitAmount($params['units'])) {
				return new PlayCardsGameRequest($params['gid'], $params['units']);
			} else if($params['type'] == 'place-units' && isset($params['units']) && is_numeric($params['units']) && $params['units'] > 0 && isset($params['territory'])) {
				return new PlaceUnitsGameRequest($params['gid'], $params['units'], $params['territory']);
			} else if($params['type'] == 'attack' && isset($params['units']) && is_numeric($params['units']) && $params['units'] > 0 && $params['units'] <= \imperator\game\Attack::MAX_ATTACKERS && isset($params['to']) && isset($params['from']) && isset($params['move']) && is_numeric($params['move']) && $params['move'] > 0) {
				return new AttackGameRequest($params['gid'], $params['units'], $params['to'], $params['from'], $params['move']);
			} else if($params['type'] == 'autoroll' && isset($params['autoroll'])) {
				return new AutoRollRequest($params['gid'], $params['autoroll']);
			} else if($params['type'] == 'defend' && isset($params['units']) && is_numeric($params['units']) && $params['units'] > 0 && $params['units'] <= \imperator\game\Attack::MAX_DEFENDERS && isset($params['to']) && isset($params['from'])) {
				return new DefendGameRequest($params['gid'], $params['units'], $params['to'], $params['from']);
			} else if($params['type'] == 'move' && isset($params['to']) && isset($params['from']) && isset($params['move']) && is_numeric($params['move']) && $params['move'] > 0) {
				return new MoveGameRequest($params['gid'], $params['move'], $params['to'], $params['from']);
			}
		}
		return new InvalidRequest($params);
	}

	public function __construct($gid) {
		$this->gid = (int)$gid;
	}

	public function getGid() {
		return $this->gid;
	}
}