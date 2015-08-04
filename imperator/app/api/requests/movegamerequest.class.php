<?php
namespace imperator\api\requests;
use imperator\Imperator;

class MoveGameRequest extends GameRequest {
	private $move;
	private $to;
	private $from;

	public function __construct($gid, $move, $to, $from) {
		parent::__construct($gid);
		$this->move = (int)$move;
		$this->to = $to;
		$this->from = $from;
	}

	public function getType() {
		return 'move';
	}

	protected function getMove() {
		return $this->move;
	}

	protected function getTo() {
		return $this->to;
	}

	protected function getFrom() {
		return $this->from;
	}

	public function handle(\imperator\User $user) {
		parent::handle($user);
		$this->throwIfNotMyTurn($user);
		$game = $this->getGame();
		if($game->getState() != \imperator\Game::STATE_POST_COMBAT) {
			throw new \imperator\exceptions\InvalidRequestException('Cannot move now.');
		} else if($game->getUnits() < $this->getMove()) {
			throw new \imperator\exceptions\InvalidRequestException('Cannot move more than %1$d units.', $game->getUnits());
		}
		$to = $game->getMap()->getTerritoryById($this->getTo());
		$from = $game->getMap()->getTerritoryById($this->getFrom());
		if(!$to || !$from) {
			throw new \imperator\exceptions\InvalidRequestException('Territory "%1$s" or "%2$s" not found in %3$d', $this->getTo(), $this->getFrom(), $game->getId());
		}
		$game->loadMap();
		$game->getMap()->setGame($game);
		$move = $this->getMove();
		if($from->getUnits() <= $move || !$from->borders($to) || $from->getOwner() != $to->getOwner() || !$from->getOwner()->getUser()->equals($user)) {
			throw new \imperator\exceptions\InvalidRequestException('Invalid move');
		}
		$db = Imperator::getDatabaseManager();
		$territories = $db->getTerritoriesTable();
		$game->setUnits($game->getUnits() - $move);
		$from->setUnits($from->getUnits() - $move);
		$game->setTime(time());
		$to->setUnits($to->getUnits() + $move);
		$db->startTransaction();
		$db->getGamesTable()->updateUnits($game);
		$territories->updateUnits($to);
		$territories->updateUnits($from);
		$db->commitTransaction();
		return array(
			'units' => $game->getUnits(),
			'update' => $game->getTime(),
			'territories' => array(
				$to->getId() => array(
					'units' => $to->getUnits()
				),
				$from->getId() => array(
					'units' => $from->getUnits()
				)
			)
		);
	}
}