<?php
namespace imperator;

class Game {
	const STATE_TURN_START = 0;
	const STATE_FORTIFY = 1;
	const STATE_COMBAT = 2;
	const STATE_POST_COMBAT = 3;
	const STATE_FINISHED = 4;

	private $id;
	private $owner;
	private $name;
	private $map;
	private $users;
	private $numPlayers;
	private $state;
	private $turn;
	private $password;
	private $time;

	public function __construct($id, User $owner, $name, $mapId, $state = 0, $turn = 0, $numPlayers = 1, $password = null, $time = 0) {
		$this->id = $id;
		$this->owner = $owner;
		$this->name = $name;
		$this->map = \imperator\map\Map::getInstance($mapId);
		$this->numPlayers = $numPlayers;
		$this->state = $state;
		$this->turn = $turn;
		$this->password = $password;
		$this->time = $time;
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
	 * @return int
	 */
	public function getTime() {
		return $this->time;
	}

	/**
	 * @return int
	 */
	public function getState() {
		return $this->state;
	}

	public function setId($id) {
		$this->id = (int)$id;
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

	public function setPassword($password) {
		$this->password = $this->hashPassword($password);
	}

	/**
	 * @return bool
	 */
	public function hasPassword() {
		return $this->password !== null;
	}

	public function getPassword() {
		return $this->password;
	}

	/**
	 * 
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
		Imperator::getDatabaseManager()->getTable('Territories')->loadMap($this);
	}
}