<?php
namespace imperator;

class Game {
	const STATE_TURN_START = 0;
	const STATE_FORTIFY = 1;
	const STATE_COMBAT = 2;
	const STATE_POST_COMBAT = 3;
	const STATE_FINISHED = 4;
	const MAX_MOVE_UNITS = 7;

	private $id;
	private $owner;
	private $name;
	private $map;
	/**
	 * @var User[]
	 */
	private $users;
	private $numPlayers;
	private $state;
	private $turn;
	private $password;
	private $time;
	private $conquered;
	private $units;
	private $mapLoaded = false;
	private $attacks;

	public function __construct($id, User $owner, $name, $mapId, $state = 0, $turn = 0, $numPlayers = 1, $password = null, $time = 0, $conquered = false, $units = 0) {
		$this->id = $id;
		$this->owner = $owner;
		$this->name = $name;
		$this->map = \imperator\map\Map::getInstance($mapId);
		$this->numPlayers = $numPlayers;
		$this->state = $state;
		$this->turn = $turn;
		$this->password = $password;
		$this->time = $time;
		$this->conquered = $conquered;
		$this->units = $units;
	}

	/**
	 * @param int $units
	 */
	public function setUnits($units) {
		$this->units = $units;
	}

	/**
	 * @return int
	 */
	public function getUnits() {
		return $this->units;
	}

	/**
	 * @param bool $conquered
	 */
	public function setConquered($conquered) {
		$this->conquered = $conquered;
	}

	/**
	 * @return bool
	 */
	public function hasConquered() {
		return $this->conquered;
	}

	/**
	 * @param bool $entities Convert characters to HTML entities
	 * @return string
	 */
	public function getName($entities = true) {
		if($entities) {
			return htmlentities($this->name);
		}
		return $this->name;
	}

	/**
	 * @param int $time
	 */
	public function setTime($time) {
		$this->time = $time;
	}

	/**
	 * @return int
	 */
	public function getTime() {
		return $this->time;
	}

	/**
	 * @param int $state
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * @return int
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return int A user ID
	 */
	public function getTurn() {
		return $this->turn;
	}

	/**
	 * @return User The player whose turn it is
	 */
	public function getCurrentPlayer() {
		foreach($this->users as $player) {
			if($player->getId() == $this->turn) {
				return $player;
			}
		}
		return null;
	}

	/**
	 * @return User[] An array of users
	 */
	public function getPlayers() {
		return $this->users;
	}

	/**
	 * @param User[] $players
	 */
	public function setPlayers(array $players) {
		$this->users = $players;
		$this->numPlayers = count($players);
	}

	/**
	 * @return int
	 */
	public function getNumberOfPlayers() {
		return $this->numPlayers;
	}

	/**
	 * @return \imperator\map\Map
	 */
	public function getMap() {
		return $this->map;
	}

	/**
	 * @return User
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * @param User $owner
	 */
	public function setOwner(User $owner) {
		$this->owner = $owner;
	}

	/**
	 * @return bool
	 */
	public function hasStarted() {
		return $this->turn !== 0 && !$this->hasEnded();
	}

	/**
	 * @return bool
	 */
	public function hasEnded() {
		return $this->state == self::STATE_FINISHED;
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function containsPlayer(User $user) {
		foreach($this->users as $player) {
			if($player->equals($user)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param User $user
	 * @return User
	 */
	public function getPlayerByUser(User $user) {
		foreach($this->users as $player) {
			if($player->equals($user)) {
				return $player;
			}
		}
		return null;
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $this->hashPassword($password);
	}

	/**
	 * @return bool
	 */
	public function hasPassword() {
		return $this->password !== null;
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param string $password
	 * @return string|null
	 */
	private function hashPassword($password = null) {
		if($password !== null) {
			if(function_exists('password_hash')) {
				return password_hash($password, PASSWORD_DEFAULT);
			}
			return sha1($password);
		}
		return null;
	}

	/**
	 * @param string $password
	 * @return bool
	 */
	public function isValidPassword($password) {
		if($this->password === null) {
			return true;
		} else if(function_exists('password_hash')) {
			return password_verify($password, $this->password);
		}
		return sha1($password) == $this->password;
	}

	/**
	 * @return string
	 */
	public function getInviteCode() {
		if($this->password === null) {
			return '';
		}
		return md5($this->password);
	}

	/**
	 * @param string $code
	 * @return bool
	 */
	public function isValidInviteCode($code) {
		return $code == md5($this->password);
	}

	/**
	 * @return array
	 */
	public function getRemainingColors() {
		$colors = Imperator::getSettings()->getPlayerColors();
		foreach($this->users as $player) {
			unset($colors[$player->getColor()]);
		}
		return $colors;
	}

	/**
	 * @return \imperator\game\Attack[]
	 */
	public function getAttacks() {
		if($this->attacks === null) {
			$this->attacks = Imperator::getDatabaseManager()->getTable('Attacks')->getAttacksFor($this);
		}
		return $this->attacks;
	}

	/**
	 * Creates a new game in the database.
	 * 
	 * @param User $user
	 * @param int $mapId
	 * @param string $name
	 * @param string $password
	 * @return \imperator\Game
	 */
	public static function create(User $user, $mapId, $name, $password = null) {
		$game = new Game(-1, $user, $name, $mapId);
		$game->setPassword($password);
		Imperator::getDatabaseManager()->getTable('Games')->createNewGame($game);
		$game->addUser($user);
		return $game;
	}

	/**
	 * Adds a user to a game in the database.
	 * 
	 * @param User $user
	 */
	public function addUser(User $user) {
		Imperator::getDatabaseManager()->getTable('GamesJoined')->addUserToGame($user, $this);
		Imperator::getDatabaseManager()->getTable('Games')->updateTime($this);
		$this->users[] = $user;
	}

	/**
	 * Removes a user from a game in the database.
	 * 
	 * @param User $user
	 */
	public function removeUser(User $user) {
		Imperator::getDatabaseManager()->getTable('GamesJoined')->removeUserFromGame($user, $this);
		Imperator::getDatabaseManager()->getTable('Games')->updateTime($this);
		foreach($this->users as $key => $player) {
			if($user->equals($player)) {
				unset($this->users[$key]);
				break;
			}
		}
		$this->setPlayers(array_values($this->users));
	}

	/**
	 * Disbands a game permanently.
	 */
	public function disband() {
		$dbManager = Imperator::getDatabaseManager();
		$dbManager->getTable('GamesJoined')->removeUsersFromGame($this);
		$dbManager->getTable('Chat')->removeMessagesFromGame($this);
	}

	/**
	 * Distributes territories and missions before starting the game.
	 */
	public function start() {
		$this->map->setGame($this);
		$this->map->distributeTerritories($this->users);
		$this->map->distributeMissions($this->users);
		$this->turn = $this->getRandomUser()->getId();
		Imperator::getDatabaseManager()->getTable('Games')->updateTurn($this);
	}

	/**
	 * @return User
	 */
	private function getRandomUser() {
		$index = mt_rand(0, $this->getNumberOfPlayers()-1);
		return $this->users[$index];
	}

	/**
	 * Loads the map from the database.
	 */
	public function loadMap() {
		if(!$this->mapLoaded) {
			$this->mapLoaded = true;
			Imperator::getDatabaseManager()->getTable('Territories')->loadMap($this);
		}
	}

	/**
	 * @param User $user
	 * @return bool True if the player needs to defend against an attack
	 */
	public function playerHasToDefend(User $user) {
		foreach($attacks as $attack) {
			if($user->equals($attack->getDefender())) {
				return true;
			}
		}
		return Imperator::getDatabaseManager()->getTable('Attacks')->playerHasToDefend($this, $user);
	}

	/**
	 * Makes a player surrender.
	 * Calls victory() if there are no other players left.
	 * Calls nextTurn() if it was the forfeiting player's turn.
	 * 
	 * @param User $user
	 */
	public function forfeit(User $user) {
		Imperator::getDatabaseManager()->getTable('GamesJoined')->forfeit($this, $user);
		//TODO combat log
		$numPlayers = 0;
		$lastPlayer = $user;
		foreach($this->users as $player) {
			if($player->equals($user)) {
				$player->setState(User::STATE_GAME_OVER);
				$player->setAutoRoll(true);
			} else if($player->getState() != User::STATE_GAME_OVER) {
				$numPlayers++;
				$lastPlayer = $player;
			}
		}
		if($numPlayers < 2) {
			$this->victory($lastPlayer);
		} else if($this->turn == $user->getId()) {
			$this->nextTurn();
		}
	}

	/**
	 * @param User $user
	 */
	public function victory(User $user) {
		//TODO
	}

	/**
	 * Starts the next turn after checking victory conditions.
	 */
	public function nextTurn() {
		$uids = array();
		$players = array();
		foreach($this->users as $player) {
			$players[$player->getId()] = $player;
			if($player->getId() == $this->turn) {
				$currentPlayer = $player;
			} else if($player->getState() != User::STATE_GAME_OVER) {
				$uids[] = $player->getId();
			}
		}
		if($currentPlayer->getMission()->hasBeenCompleted($this, $currentPlayer)) {
			$this->victory($currentPlayer);
		} else {
			$numPlayers = count($uids);
			if($numPlayers < 1) {
				//probably not possible
				$this->victory($currentPlayer);
				return;
			} else if($numPlayers == 1) {
				$this->turn = $uids[0];
			} else {
				sort($uids);
				for($n = 0; $n < $numPlayers; $n++) {
					if($uids[$n] > $this->turn) {
						$this->turn = $uids[$n];
						break;
					}
				}
				if($this->turn == $currentPlayer->getId()) {
					$this->turn = $uids[0];
				}
			}
			$this->state = Game::STATE_TURN_START;
			$this->time = time();
			$this->units = $this->getUnitsFromRegionsPerTurn($players[$this->turn]);
			$this->conquered = false;
			Imperator::getDatabaseManager()->getTable('Games')->nextTurn($this);
		}
	}

	/**
	 * @param User $user
	 * @return int
	 */
	public function getUnitsFromRegionsPerTurn(User $user) {
		$this->loadMap();
		$units = 0;
		foreach($this->map->getRegions() as $region) {
			if($region->isOwnedBy($user)) {
				$units += $region->getUnitsPerTurn();
			}
		}
		return $units;
	}

	/**
	 * @param User $user
	 * @return int
	 */
	public function getUnitsFromTerritoriesPerTurn(User $user) {
		$this->loadMap();
		$units = count($this->map->getTerritoriesFor($user)) / 3;
		return max(floor($units), 3);
	}

	/**
	 * @param bool
	 */
	public function hasOngoingBattles() {
		if(!empty($this->attacks)) {
			return true;
		}
		return Imperator::getDatabaseManager()->getTable('Attacks')->gameHasAttacks($this);
	}

	/**
	 * @param User $user
	 * @param int $card
	 * @return int
	 */
	public function giveCard(User $user, $card) {
		$cards = $user->getCards($this);
		if(($card != game\Cards::CARD_NONE && $cards->getNumberOf($card) > 0) || $cards->getNumberOfCards() < game\Cards::MAX_CARDS) {
			$gj = Imperator::getDatabaseManager()->getTable('GamesJoined');
			$possibleCards = array(
				game\Cards::CARD_ARTILLERY, game\Cards::CARD_ARTILLERY, game\Cards::CARD_ARTILLERY,
				game\Cards::CARD_CAVALRY, game\Cards::CARD_CAVALRY, game\Cards::CARD_CAVALRY,
				game\Cards::CARD_INFANTRY, game\Cards::CARD_INFANTRY, game\Cards::CARD_INFANTRY
			);
			if($gj->getNumberOfJokers() < game\Cards::MAX_JOKERS) {
				$possibleCards[] = game\Cards::CARD_JOKER;
			}
			$newCard = $possibleCards[mt_rand(0, count($possibleCards) - 1)];
			if($cards->getNumberOfCards() >= game\Cards::MAX_CARDS) {
				$cards->setNumberOf($card, $cards->getNumberOf($card) - 1);
			}
			$cards->setNumberOf($newCard, $cards->getNumberOf($newCard) + 1);
			$gj->saveCards($this, $user, $cards);
			return $newCard;
		}
		return game\Cards::CARD_NONE;
	}

	/**
	 * Allows the current player to move units.
	 */
	public function startMove() {
		$this->setUnits(static::MAX_MOVE_UNITS);
		$this->setState(static::STATE_POST_COMBAT);
		Imperator::getDatabaseManager()->getTable('Games')->updateUnitsAndState($this);
	}

	/**
	 * Makes the player passively stack units.
	 * 
	 * @param User $user
	 */
	public function fortify(User $user) {
		$this->units += $this->getUnitsFromTerritoriesPerTurn($user);
		$this->state = static::STATE_FORTIFY;
		Imperator::getDatabaseManager()->getTable('Games')->updateUnitsAndState($this);
	}

	/**
	 * Makes the player trade cards for units.
	 * 
	 * @param User $user
	 * @param int $units
	 */
	public function playCardCombination(User $user, $units) {
		$cards = $user->getCards($this);
		$cards->removeCombination($units);
		$this->units += $units;
		$db = Imperator::getDatabaseManager();
		$db->getTable('GamesJoined')->saveCards($this, $user, $cards);
		$db->getTable('Games')->updateUnits($this);
	}

	/**
	 * Places a number of units in a territory.
	 * 
	 * @param \imperator\map\Territory $territory
	 * @param int $amount
	 */
	public function placeUnits(\imperator\map\Territory $territory, $amount) {
		$this->map->setGame($this);
		$territory->setUnits($territory->getUnits() + $amount);
		$this->units -= $amount;
		$db = Imperator::getDatabaseManager();
		$db->getTable('Games')->updateUnits($this);
		$db->getTable('Territories')->updateUnits($territory);
	}

	/**
	 * True if the attacking territory is attacking or the defending territory is defending.
	 * 
	 * @param \imperator\map\Territory $attacker
	 * @param \imperator\map\Territory $defender
	 * @return bool
	 */
	public function territoriesAreInCombat(\imperator\map\Territory $attacker, \imperator\map\Territory $defender) {
		$this->map->setGame($this);
		return Imperator::getDatabaseManager()->getTable('Attacks')->territoriesAreInCombat($attacker, $defender);
	}

	/**
	 * Executes an attack.
	 * 
	 * @param \imperator\game\Attack $attack
	 */
	public function executeAttack(\imperator\game\Attack $attack) {
		$db = Imperator::getDatabaseManager();
		$territoriesTable = $db->getTable('Territories');
		$gjTable = $db->getTable('GamesJoined');
		$attackingTerritory = $attack->getAttacker();
		$defendingTerritory = $attack->getDefender();
		//TODO combat log
		$attackerUnits = $attackingTerritory->getUnits() - $attack->getAttackerLosses();
		$defenderUnits = $defendingTerritory->getUnits() - $attack->getDefenderLosses();
		if($defendUnits === 0) {
			$this->conquered = true;
			$move = $attack->getMove();
			if($move >= $attackUnits) {
				$move = $attackUnits - 1;
			}
			$defender = $defendingTerritory->getOwner();
			$attackingTerritory->setUnits($attackUnits - $move);
			$defendingTerritory->setUnits($move);
			$defendingTerritory->setOwner($attackingTerritory->getOwner());
			$missions = $this->map->getMissions();
			if($this->map->playerHasTerritories($defender)) {
				$defender->setState(User::STATE_GAME_OVER);
				$gjTable->saveState($defender);
				$playersWithNewMissions = array();
				foreach($missions as $mission) {
					if($mission->containsEliminate()) {
						foreach($this->users as $player) {
							$playerMission = $player->getMission();
							if($mission->equals($playerMission) && $playerMission->getUid() == $defender->getId()) {
								if($player->equals($attackingTerritory->getOwner())) {
									$player->setState(User::STATE_DESTROYED_RIVAL);
									$gjTable->saveState($player);
								} else {
									$newMission = clone $missions[$mission->getFallback()];
									$newMission->setUid($playerMission->getUid());
									$player->setMission($newMission);
									$playersWithNewMissions[] = $player;
								}
							}
						}
					}
				}
				$gj->saveMissions($this, $playersWithNewMissions);
			}
			$db->getTable('Games')->updateConquered($this);
			$territoriesTable->updateUnitsAndOwner($attackingTerritory);
			$territoriesTable->updateUnitsAndOwner($defendingTerritory);
		} else {
			$attackingTerritory->setUnits($attackerUnits);
			$defendingTerritory->setUnits($defenderUnits);
			$territoriesTable->updateUnits($attackingTerritory);
			$territoriesTable->updateUnits($defendingTerritory);
		}
	}
}