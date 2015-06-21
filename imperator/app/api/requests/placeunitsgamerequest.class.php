<?php
namespace imperator\api\requests;
use imperator\Imperator;

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
		if($this->getGame()->getState() != \imperator\Game::STATE_TURN_START && $this->getGame()->getState() != \imperator\Game::STATE_FORTIFY) {
			throw new \imperator\exceptions\InvalidRequestException('Cannot place units after attacking.');
		} else if($this->getGame()->getUnits() >= $this->getUnits()) {
			throw new \imperator\exceptions\InvalidRequestException('Cannot place more than '.$this->getGame()->getUnits().' units.');
		}
		$territory = $this->getGame()->getMap()->getTerritoryById($this->getTerritory());
		if(!$territory) {
			throw new \imperator\exceptions\InvalidRequestException('Could not find territory "'.$this->getTerritory().'" in '.$this->getGame()->getId());
		}
		$this->getGame()->loadMap();
		if(!$territory->getOwner()->equals($user)) {
			throw new \imperator\exceptions\InvalidRequestException('Territory "'.$this->getTerritory().'" not owned by '.$user->getId().' in '.$this->getGame()->getId());
		}
		$this->getGame()->placeUnits($territory, $this->getUnits());
		return array(
			'state' => $this->getGame()->getState(),
			'time' => $this->getGame()->getTime(),
			'units' => $this->getGame()->getUnits(),
			'territories' => array(
				$territory->getId() => array(
					'units' => $territory->getUnits()
				)
			)
		);
	}
}