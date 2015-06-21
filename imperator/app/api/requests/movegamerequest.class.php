<?php
namespace imperator\api\requests;
use imperator\Imperator;

class MoveGameRequest extends GameRequest {
	private $to;
	private $from;

	public function __construct($gid, $to, $from) {
		parent::__construct($gid);
		$this->to = $to;
		$this->from = $from;
	}

	public function getType() {
		return 'move';
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
			throw new \imperator\exceptions\InvalidRequestException('Cannot move more than '.$game->getUnits().' units.');
		}
		$to = $game->getMap()->getTerritoryById($this->getTo());
		$from = $game->getMap()->getTerritoryById($this->getFrom());
		if(!$to || !$from) {
			throw new \imperator\exceptions\InvalidRequestException('Territory "'.$this->getTo().'" or "'.$this->getFrom().'" not found in '.$game->getId());
		}
		$game->loadMap();
		$game->getMap()->setGame($game);
		$move = $this->getMove();
		if($from->getUnits() <= $move || !$from->borders($to) || !$from->getOwner()->equals($to->getOwner()) || !$from->getOwner()->equals($user)) {
			throw new \imperator\exceptions\InvalidRequestException('Invalid move');
		}
		$db = Imperator::getDatabaseManager();
		$territories = $db->getTable('Territories');
		$game->setUnits($game->getUnits() - $move);
		$from->setUnits($from->getUnits() - $move);
		$to->setUnits($to->getUnits() + $move);
		$db->getTable('Games')->updateUnits($game);
		$territories->updateUnits($to);
		$territories->updateUnits($from);
		return array(
			'units' => $game->getUnits(),
			'time' => $game->getTime(),
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