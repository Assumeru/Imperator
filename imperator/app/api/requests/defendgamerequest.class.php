<?php
namespace imperator\api\requests;
use imperator\Imperator;

class DefendGameRequest extends GameRequest {
	private $units;
	private $to;
	private $from;

	public function __construct($gid, $units, $to, $from) {
		parent::__construct($gid);
		$this->units = (int)$units;
		$this->to = $to;
		$this->from = $from;
	}

	public function getType() {
		return 'defend';
	}

	protected function getUnits() {
		return $this->units;
	}

	protected function getTo() {
		return $this->to;
	}

	protected function getFrom() {
		return $this->from;
	}

	public function handle(\imperator\User $user) {
		parent::handle($user);
		$to = $this->getGame()->getMap()->getTerritoryById($this->getTo());
		$from = $this->getGame()->getMap()->getTerritoryById($this->getFrom());
		if(!$to || !$from) {
			throw new \imperator\exceptions\InvalidRequestException('Territory "%1$s" or "%2$s" not found in %3$d', $this->getTo(), $this->getFrom(), $game->getId());
		}
		$this->getGame()->loadMap();
		$this->getGame()->getMap()->setGame($this->getGame());
		$db = Imperator::getDatabaseManager();
		$attacks = $db->getAttacksTable();
		$attack = $attacks->getAttack($from, $to);
		if(!$to->getOwner()->getUser()->equals($user)) {
			throw new \imperator\exceptions\InvalidRequestException('User %1$d does not own "%2$s" in %3$d', $user->getId(), $to->getId(), $this->getGame()->getId());
		} else if(!$attack) {
			throw new \imperator\exceptions\InvalidRequestException('Attack not found for "%1$s" and "%2$s" in %3$d', $this->getTo(), $this->getFrom(), $this->getGame()->getId());
		}
		$attack->rollDefence($this->getUnits());
		$this->getGame()->executeAttack($attack);
		$attacks->deleteAttack($attack);
		$this->getGame()->setTime(time());
		$db->getGamesTable()->updateTime($this->getGame());
		return array(
			'territories' => array(
				$to->getId() => array(
					'uid' => $to->getOwner()->getId(),
					'units' => $to->getUnits()
				),
				$from->getId() => array(
					'uid' => $from->getOwner()->getId(),
					'units' => $from->getUnits()
				)
			),
			'state' => $this->getGame()->getState(),
			'update' => $this->getGame()->getTime(),
			'attack' => $this->getAttackJSON($attack)
		);
	}
}