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
	 * @var game\Player[]
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
	/**
	 * @var game\Attack
	 */
	private $attacks;

	public function __construct($id, game\Player $owner, $name, $mapId, $state = 0, $turn = 0, $numPlayers = 1, $password = null, $time = 0, $conquered = false, $units = 0) {
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
	 * @return game\Player The player whose turn it is
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
	 * @return game\Player[] An array of players
	 */
	public function getPlayers() {
		return $this->users;
	}

	/**
	 * @param game\Player[] $players
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
	 * @return game\Player
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * @param game\Player $owner
	 */
	public function setOwner(game\Player $owner) {
		$this->owner = $owner;
	}

	/**
	 * @return bool
	 */
	public function hasStarted() {
		return $this->turn !== 0;
	}

	/**
	 * @return bool
	 */
	public function hasEnded() {
		return $this->state == self::STATE_FINISHED;
	}

	/**
	 * @param Member $user
	 * @return bool
	 */
	public function containsPlayer(Member $user) {
		foreach($this->users as $player) {
			if($player->getId() == $user->getId()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param User $user
	 * @return game\Player
	 */
	public function getPlayerByUser(User $user) {
		foreach($this->users as $player) {
			if($player->getUser()->equals($user)) {
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
	 * @param game\Player $user
	 * @param int $mapId
	 * @param string $name
	 * @param string $password
	 * @return \imperator\Game
	 */
	public static function create(game\Player $user, $mapId, $name, $password = null) {
		$game = new Game(-1, $user, $name, $mapId);
		$game->setPassword($password);
		$user->setGame($game);
		Imperator::getDatabaseManager()->getTable('Games')->createNewGame($game);
		$game->addUser($user);
		return $game;
	}

	/**
	 * Adds a user to a game in the database.
	 * 
	 * @param game\Player $user
	 */
	public function addUser(game\Player $user) {
		Imperator::getDatabaseManager()->getTable('GamesJoined')->addUserToGame($user);
		Imperator::getDatabaseManager()->getTable('Games')->updateTime($this);
		$this->users[] = $user;
	}

	/**
	 * Removes a user from a game in the database.
	 * 
	 * @param game\Player $user
	 */
	public function removeUser(game\Player $user) {
		Imperator::getDatabaseManager()->getTable('GamesJoined')->removeUserFromGame($user);
		Imperator::getDatabaseManager()->getTable('Games')->updateTime($this);
		foreach($this->users as $key => $player) {
			if($user == $player) {
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
	 * @return game\Player
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
	 * @param game\Player $user
	 * @return bool True if the player needs to defend against an attack
	 */
	public function playerHasToDefend(game\Player $user) {
		foreach($attacks as $attack) {
			if($user == $attack->getDefender()->getOwner()) {
				return true;
			}
		}
		return Imperator::getDatabaseManager()->getTable('Attacks')->playerHasToDefend($user);
	}

	/**
	 * Makes a player surrender.
	 * Calls victory() if there are no other players left.
	 * Calls nextTurn() if it was the forfeiting player's turn.
	 * 
	 * @param game\Player $user
	 */
	public function forfeit(game\Player $user) {
		Imperator::getDatabaseManager()->getTable('GamesJoined')->forfeit($user);
		//TODO combat log
		$numPlayers = 0;
		$lastPlayer = $user;
		foreach($this->users as $player) {
			if($player == $user) {
				$player->setState(game\Player::STATE_GAME_OVER);
				$player->setAutoRoll(true);
			} else if($player->getState() != game\Player::STATE_GAME_OVER) {
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
	 * @param game\Player $user
	 */
	public function victory(game\Player $user) {
		$db = Imperator::getDatabaseManager();
		$db->getTable('CombatLog')->deleteGame($this);
		$db->getTable('Territories')->removeTerritoriesFromGame($this);
		$users = $db->getTable('Users');
		$user->setState(game\Player::STATE_VICTORIOUS);
		$db->getTable('GamesJoined')->saveState($user);
		$this->turn = 0;
		$this->time = time();
		$this->state = static::STATE_FINISHED;
		$db->getTable('Games')->updateStateAndTurn($this);
		foreach($this->getPlayers() as $player) {
			if($player == $user) {
				$users->addWin($player->getUser(), $this->getNumberOfPlayers() - 1);
			} else {
				$users->addLoss($player->getUser());
			}
		}
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
			} else if($player->getState() != game\Player::STATE_GAME_OVER) {
				$uids[] = $player->getId();
			}
		}
		if($currentPlayer->getMission()->hasBeenCompleted($currentPlayer)) {
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
			$this->state = static::STATE_TURN_START;
			$this->time = time();
			$this->units = $players[$this->turn]->getUnitsFromRegionsPerTurn();
			$this->conquered = false;
			Imperator::getDatabaseManager()->getTable('Games')->nextTurn($this);
		}
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
	 * @param game\Player $user
	 * @param int $card
	 * @return int
	 */
	public function giveCard(game\Player $user, $card) {
		$cards = $user->getCards();
		if(($card != game\Cards::CARD_NONE && $cards->getNumberOf($card) > 0) || $cards->getNumberOfCards() < game\Cards::MAX_CARDS) {
			$gj = Imperator::getDatabaseManager()->getTable('GamesJoined');
			$possibleCards = array(
				game\Cards::CARD_ARTILLERY, game\Cards::CARD_ARTILLERY, game\Cards::CARD_ARTILLERY,
				game\Cards::CARD_CAVALRY, game\Cards::CARD_CAVALRY, game\Cards::CARD_CAVALRY,
				game\Cards::CARD_INFANTRY, game\Cards::CARD_INFANTRY, game\Cards::CARD_INFANTRY
			);
			if($gj->getNumberOfJokers($this) < game\Cards::MAX_JOKERS) {
				$possibleCards[] = game\Cards::CARD_JOKER;
			}
			$newCard = $possibleCards[mt_rand(0, count($possibleCards) - 1)];
			if($cards->getNumberOfCards() >= game\Cards::MAX_CARDS) {
				$cards->setNumberOf($card, $cards->getNumberOf($card) - 1);
			}
			$cards->setNumberOf($newCard, $cards->getNumberOf($newCard) + 1);
			$gj->saveCards($user);
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
	 * @param game\Player $user
	 */
	public function fortify(game\Player $user) {
		$this->units += $user->getUnitsFromTerritoriesPerTurn();
		$this->state = static::STATE_FORTIFY;
		Imperator::getDatabaseManager()->getTable('Games')->updateUnitsAndState($this);
	}

	/**
	 * Makes the player trade cards for units.
	 * 
	 * @param game\Player $user
	 * @param int $units
	 */
	public function playCardCombination(game\Player $user, $units) {
		$cards = $user->getCards();
		$cards->removeCombination($units);
		$this->units += $units;
		$db = Imperator::getDatabaseManager();
		$db->getTable('GamesJoined')->saveCards($user);
		$db->getTable('Games')->updateUnits($this);
	}

	/**
	 * Places a number of units in a territory.
	 * 
	 * @param \imperator\map\Territory $territory
	 * @param int $amount
	 */
	public function placeUnits(\imperator\map\Territory $territory, $amount) {
		$this->time = time();
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
		if($defenderUnits === 0) {
			$this->time = time();
			$this->conquered = true;
			$move = $attack->getMove();
			if($move >= $attackerUnits) {
				$move = $attackerUnits - 1;
			}
			$defender = $defendingTerritory->getOwner();
			$attackingTerritory->setUnits($attackerUnits - $move);
			$defendingTerritory->setUnits($move);
			$defendingTerritory->setOwner($attackingTerritory->getOwner());
			$missions = $this->map->getMissions();
			if(!$this->map->playerHasTerritories($defender)) {
				$defender->setState(game\Player::STATE_GAME_OVER);
				$gjTable->saveState($defender);
				$playersWithNewMissions = array();
				foreach($missions as $mission) {
					if($mission->containsEliminate()) {
						foreach($this->users as $player) {
							$playerMission = $player->getMission();
							if($mission->equals($playerMission) && $playerMission->getUid() == $defender->getId()) {
								if($player == $attackingTerritory->getOwner()) {
									$player->setState(game\Player::STATE_DESTROYED_RIVAL);
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
				$gjTable->saveMissions($playersWithNewMissions);
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