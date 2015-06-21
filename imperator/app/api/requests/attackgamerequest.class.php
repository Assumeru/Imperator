<?php
namespace imperator\api\requests;
use imperator\Imperator;

class AttackGameRequest extends GameRequest {
	private $units;
	private $to;
	private $from;
	private $move;

	public function __construct($gid, $units, $to, $from, $move) {
		parent::__construct($gid);
		$this->units = (int)$units;
		$this->to = $to;
		$this->from = $from;
		$this->move = (int)$move;
	}

	public function getType() {
		return 'attack';
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

	protected function getMove() {
		return $this->move;
	}

	public function handle(\imperator\User $user) {
		parent::handle($user);
		$this->throwIfNotMyTurn($user);
		$game = $this->getGame();
		if($game->getState() != \imperator\Game::STATE_TURN_START && $game->getState() != \imperator\Game::STATE_COMBAT) {
			throw new \imperator\exceptions\InvalidRequestException('Cannot attack now.');
		}
		$to = $game->getMap()->getTerritoryById($this->getTo());
		$from = $game->getMap()->getTerritoryById($this->getFrom());
		if(!$to || !$from) {
			throw new \imperator\exceptions\InvalidRequestException('Territory "'.$this->getTo().'" or "'.$this->getFrom().'" not found in '.$game->getId());
		}
		$game->loadMap();
		if(!$from->getOwner()->equals($user) || $to->getOwner()->equals($user) || $this->getUnits() >= $from->getUnits() || $this->getMove() >= $from->getUnits() || !$from->borders($to)) {
			throw new \imperator\exceptions\InvalidRequestException('Invalid attack');
		} else if($game->territoriesAreInCombat($to, $from)) {
			throw new \imperator\exceptions\InvalidRequestException($user->getLanguage()->translate('One of these territories is already engaged in combat.'));
		}
		$attack = new \imperator\game\Attack($from, $to, $this->getMove());
		$attack->rollAttack($this->getUnits());
		$game->setState(\imperator\Game::STATE_COMBAT);
		$game->setTime(time());
		Imperator::getDatabaseManager()->getTable('Games')->updateState($game);
		if($to->getUnits() === 1 || $to->getOwner()->getAutoRoll() || $attack->attackerCannotWin()) {
			$attack->autoRollDefence();
			$game->executeAttack($attack);
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
				'state' => $game->getState(),
				'time' => $game->getTime(),
				'attack' => $this->getAttackJSON($attack)
			);
		}
		$attack->save();
		return array(
			'attacks' => $this->getAttacks($game),
			'attack' => $this->getAttackJSON($attack)
		);
	}
}