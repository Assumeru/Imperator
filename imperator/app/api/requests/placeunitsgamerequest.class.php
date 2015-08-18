<?php
namespace imperator\api\requests;

class PlaceUnitsGameRequest extends GameRequest {
	private $units;
	private $territory;

	public function __construct($gid, $units, $territory) {
		parent::__construct($gid);
		$this->units = (int)$units;
		$this->territory = $territory;
	}

	public function getType() {
		return 'place-units';
	}

	protected function getUnits() {
		return $this->units;
	}

	protected function getTerritory() {
		return $this->territory;
	}

	public function handle(\imperator\User $user) {
		parent::handle($user);
		$this->throwIfNotMyTurn($user);
		$game = $this->getGame();
		if($game->getState() != \imperator\Game::STATE_TURN_START && $game->getState() != \imperator\Game::STATE_FORTIFY) {
			throw new \imperator\exceptions\InvalidRequestException('Cannot place units after attacking.');
		} else if($game->getUnits() < $this->getUnits()) {
			throw new \imperator\exceptions\InvalidRequestException('Cannot place more than %1$d units.', $game->getUnits());
		}
		$territory = $game->getMap()->getTerritoryById($this->getTerritory());
		if(!$territory) {
			throw new \imperator\exceptions\InvalidRequestException('Could not find territory "%1$s" in %2$d', $this->getTerritory(), $game->getId());
		}
		$game->loadMap();
		if(!$territory->getOwner()->equals($user)) {
			throw new \imperator\exceptions\InvalidRequestException('Territory "%1$s" not owned by %2$d in %3$d', $this->getTerritory(), $user->getId(), $game->getId());
		}
		$game->placeUnits($territory, $this->getUnits());
		return array(
			'state' => $game->getState(),
			'update' => $game->getTime(),
			'units' => $game->getUnits(),
			'territories' => array(
				$territory->getId() => array(
					'units' => $territory->getUnits()
				)
			)
		);
	}
}