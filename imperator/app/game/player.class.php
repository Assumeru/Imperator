<?php
namespace imperator\game;
use imperator\Imperator;

class Player implements \imperator\Member {
	const STATE_PLAYING = 0;
	const STATE_GAME_OVER = 1;
	const STATE_DESTROYED_RIVAL = 2;
	const STATE_VICTORIOUS = 3;

	private $user;
	private $game;
	private $color;
	private $mission;
	private $state;
	private $autoroll;
	private $cards;

	public function __construct(\imperator\User $user, \imperator\Game $game = null) {
		$this->user = $user;
		$this->game = $game;
	}

	public function getId() {
		return $this->user->getId();
	}

	public function getName() {
		return $this->user->getName();
	}

	public function getProfileLink() {
		return $this->user->getProfileLink();
	}

	public function setGame(\imperator\Game $game) {
		$this->game = $game;
	}

	public function getUser() {
		return $this->user;
	}

	public function getGame() {
		return $this->game;
	}

	public function setColor($color) {
		$this->color = $color;
	}

	public function getColor() {
		return $this->color;
	}

	public function setState($state) {
		$this->state = $state;
	}

	public function getState() {
		return $this->state;
	}

	public function setMission(\imperator\mission\PlayerMission $mission) {
		$this->mission = $mission;
	}

	public function getMission() {
		return $this->mission;
	}

	public function setAutoRoll($autoroll) {
		$this->autoroll = $autoroll;
	}

	public function getAutoRoll() {
		return $this->autoroll;
	}

	public function setCards(\imperator\game\Cards $cards) {
		$this->cards = $cards;
	}

	/**
	 * @return \imperator\game\Cards
	 */
	public function getCards() {
		if(!$this->cards) {
			$this->cards = Imperator::getDatabaseManager()->getGamesJoinedTable()->getCardsFor($this);
		}
		return $this->cards;
	}

	/**
	 * @return \imperator\map\Territory[]
	 */
	public function getTerritories() {
		return $this->game->getMap()->getTerritoriesFor($this);
	}

	/**
	 * @return int
	 */
	public function getUnitsFromRegionsPerTurn() {
		$this->game->loadMap();
		$units = 0;
		foreach($this->game->getMap()->getRegions() as $region) {
			if($region->isOwnedBy($this)) {
				$units += $region->getUnitsPerTurn();
			}
		}
		return $units;
	}

	/**
	 * @return int
	 */
	public function getUnitsFromTerritoriesPerTurn() {
		$this->game->loadMap();
		$units = count($this->getTerritories()) / 3;
		return max(floor($units), 3);
	}
}