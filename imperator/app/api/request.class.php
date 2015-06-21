<?php
namespace imperator\api;
use imperator\Imperator;

abstract class Request {
	public static function buildRequest(array $params) {
		if(isset($params['mode'])) {
			if($params['mode'] == 'update') {
				return requests\UpdateRequest::buildRequest($params);
			} else if($params['mode'] == 'game') {
				return requests\GameRequest::buildRequest($params);
			} else if($params['mode'] == 'chat') {
				return requests\ChatRequest::buildRequest($params);
			}
		}
		return new requests\InvalidRequest($params);
	}

	public function handle(\imperator\User $user) {
		throw new \imperator\exceptions\InvalidRequestException('Abstract request cannot be handled.');
	}

	public function getMode() {
		return null;
	}

	public function getType() {
		return null;
	}

	protected function canUseChat(\imperator\User $user, $gid) {
		return $gid === 0 || $this->isPlayerInGame($user, $gid);
	}

	protected function isPlayerInGame(\imperator\User $user, $gid) {
		return Imperator::getDatabaseManager()->getTable('GamesJoined')->gameContainsPlayer($gid, $user);
	}

	protected function isGameOwner(\imperator\User $user, $gid) {
		return Imperator::getDatabaseManager()->getTable('Games')->gameOwnerEquals($gid, $user);
	}

	protected function getAttacks(\imperator\Game $game) {
		$out = array();
		foreach($game->getAttacks() as $attack) {
			$out[] = $this->getAttackJSON($attack);
		}
		return $out;
	}

	protected function getAttackJSON(\imperator\game\Attack $attack) {
		$out = array(
			'attacker' => $attack->getAttacker()->getId(),
			'defender' => $attack->getDefender()->getId(),
			'attackroll' => $attack->getAttackRoll(),
			'move' => $attack->getMove()
		);
		if($attack->getDefenceRoll()) {
			$out['defendroll'] = $attack->getDefenceRoll();
		}
		return $out;
	}
}