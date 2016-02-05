<?php
namespace imperator\database;
use imperator\Imperator;

class GamesTable extends Table {
	protected function register(DatabaseManager $manager) {
		$manager->registerTable('GAMES', 'imperator_games', array(
			'GAME' => 'gid',
			'MAP' => 'map',
			'NAME' => 'name',
			'USER' => 'uid',
			'TURN' => 'turn',
			'TIME' => 'time',
			'STATE' => 'state',
			'UNITS' => 'units',
			'CONQUERED' => 'conquered',
			'PASSWORD' => 'password'
		));
	}

	public function create() {
		$this->getManager()->preparedStatement(
			'CREATE TABLE @GAMES (
				@-GAMES.GAME INT AUTO_INCREMENT PRIMARY KEY,
				@-GAMES.MAP INT NOT NULL,
				@-GAMES.NAME VARCHAR(255) NOT NULL,
				@-GAMES.USER INT REFERENCES @OUTSIDEUSERS(@-OUTSIDEUSERS.USER),
				@-GAMES.TURN INT NOT NULL DEFAULT "0",
				@-GAMES.TIME INT NOT NULL,
				@-GAMES.STATE SMALLINT NOT NULL DEFAULT "0",
				@-GAMES.UNITS INT NOT NULL DEFAULT "0",
				@-GAMES.CONQUERED SMALLINT NOT NULL DEFAULT "0",
				@-GAMES.PASSWORD VARCHAR(255)
			)'
		);
	}

	public function drop() {
		$this->getManager()->preparedStatement('DROP TABLE IF EXISTS @GAMES');
	}

	/**
	 * Deletes a game from the database.
	 * 
	 * @param \imperator\Game $game
	 */
	public function deleteGame(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'DELETE FROM @GAMES WHERE @GAMES.GAME = %d',
			$game->getId()
		)->free();
	}

	/**
	 * Inserts a game into the database.
	 * 
	 * @param \imperator\Game $game
	 */
	public function createNewGame(\imperator\Game $game) {
		$array = array(
			'@GAMES.MAP' => $game->getMap()->getId(),
			'@GAMES.NAME' => $game->getName(false),
			'@GAMES.USER' => $game->getOwner()->getId(),
			'@GAMES.TIME' => time()
		);
		if($game->hasPassword()) {
			$array['@GAMES.PASSWORD'] = $game->getPassword();
		}
		$query = $this->getManager()->insert('@GAMES', $array);
		$game->setId($query->getInsertId());
		$query->free();
	}

	/**
	 * 
	 * @param \imperator\User $user
	 * @return \imperator\Game[]
	 */
	public function getGamesFor(\imperator\User $user) {
		return $this->getGames($this->getManager()->preparedStatement(
			'SELECT
				@GAMES.GAME,
				@GAMES.NAME,
				@GAMES.MAP,
				COUNT(@GAMESJOINED.USER) AS players,
				@GAMES.USER,
				@GAMES.STATE,
				@GAMES.TURN,
				@GAMES.PASSWORD,
				@OUTSIDEUSERS.USERNAME
			FROM @GAMES
			JOIN @GAMESJOINED ON(@GAMES.GAME = @GAMESJOINED.GAME)
			JOIN @OUTSIDEUSERS ON(@GAMES.USER = @OUTSIDEUSERS.USER)
			WHERE @GAMESJOINED.GAME IN(
				SELECT @GAMESJOINED.GAME
				FROM @GAMESJOINED
				WHERE @GAMESJOINED.USER = %d
			)
			GROUP BY @GAMESJOINED.GAME
			ORDER BY @GAMES.MAP, players ASC', $user->getId()));
	}

	/**
	 * 
	 * @return \imperator\Game[]
	 */
	public function getAllGames() {
		return $this->getGames($this->getManager()->preparedStatement(
			'SELECT
				@GAMES.GAME,
				@GAMES.NAME,
				@GAMES.MAP,
				COUNT(@GAMESJOINED.USER) AS players,
				@GAMES.USER,
				@GAMES.STATE,
				@GAMES.TURN,
				@GAMES.PASSWORD,
				@OUTSIDEUSERS.USERNAME
			FROM @GAMES
			JOIN @GAMESJOINED ON(@GAMES.GAME = @GAMESJOINED.GAME)
			JOIN @OUTSIDEUSERS ON(@GAMES.USER = @OUTSIDEUSERS.USER)
			GROUP BY @GAMESJOINED.GAME
			ORDER BY @GAMES.MAP, players ASC'));
	}

	/**
	 * 
	 * @param int $gameId
	 * @return \imperator\Game
	 */
	public function getGameById($gameId) {
		$game = null;
		$userClass = Imperator::getSettings()->getUserClass();
		$query = $this->getManager()->preparedStatement('SELECT * FROM @GAMES WHERE @GAMES.GAME = %d', $gameId);
		if($result = $query->fetchResult()) {
			$game = new \imperator\Game(
				$result->getInt(0),
				new \imperator\game\Player(new $userClass($result->getInt(3))),
				$result->get(2),
				$result->getInt(1),
				$result->getInt(6),
				$result->getInt(4),
				1,
				$result->get(9),
				$result->getInt(5),
				$result->getBool(8),
				$result->getInt(7)
			);
			$players = $this->getManager()->getGamesJoinedTable()->getPlayersForGame($game);
			$game->setPlayers($players);
			$game->setOwner($game->getPlayerByUser($game->getOwner()->getUser()));
		}
		$query->free();
		return $game;
	}

	/**
	 * 
	 * @param Query $query
	 * @param OutsideUsersTable $u
	 * @return \imperator\Game[]
	 */
	private function getGames(Query $query) {
		$games = array();
		$userClass = Imperator::getSettings()->getUserClass();
		while($result = $query->fetchResult()) {
			$player = new \imperator\game\Player(new $userClass($result->getInt(4), $result->get(8)));
			$game = new \imperator\Game(
				$result->getInt(0),
				$player,
				$result->get(1),
				$result->getInt(2),
				$result->getInt(5),
				$result->getInt(6),
				$result->getInt(3),
				$result->get(7)
			);
			$player->setGame($game);
			$games[] = $game;
		}
		$query->free();
		return $games;
	}

	public function updateTime(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'UPDATE @GAMES SET @GAMES.TIME = %d WHERE @GAMES.GAME = %d',
			time(), $game->getId()
		)->free();
	}

	public function updateTurn(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'UPDATE @GAMES SET @GAMES.TIME = %d, @GAMES.TURN = %d WHERE @GAMES.GAME = %d',
			time(), $game->getTurn(), $game->getId()
		)->free();
	}

	/**
	 * @param int $gid
	 * @param \imperator\User $user
	 * @return bool
	 */
	public function gameOwnerEquals($gid, \imperator\User $user) {
		$db = $this->getManager();
		return $db->exists($db->preparedStatement(
			'SELECT 1 FROM @GAMES WHERE @GAMES.GAME = %d AND @GAMES.USER = %d',
			$gid, $user->getId()
		));
	}

	/**
	 * @param int $gid
	 * @param int $time
	 * @return bool
	 */
	public function timeIsAfter($gid, $time) {
		$db = $this->getManager();
		return $db->exists($db->preparedStatement(
			'SELECT 1 FROM @GAMES WHERE @GAMES.GAME = %d AND @GAMES.TIME > %d',
			$gid, $time
		));
	}

	public function nextTurn(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'UPDATE @GAMES SET
			@GAMES.CONQUERED = %b,
			@GAMES.STATE = %d,
			@GAMES.TIME = %d,
			@GAMES.TURN = %d,
			@GAMES.UNITS = %d
			WHERE @GAMES.GAME = %d',
			$game->hasConquered(), $game->getState(), $game->getTime(),
			$game->getTurn(), $game->getUnits(), $game->getId()
		)->free();
	}

	public function updateUnitsAndState(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'UPDATE @GAMES SET
			@GAMES.STATE = %d,
			@GAMES.TIME = %d,
			@GAMES.UNITS = %d
			WHERE @GAMES.GAME = %d',
			$game->getState(), $game->getTime(), $game->getUnits(), $game->getId()
		)->free();
	}

	public function updateUnits(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'UPDATE @GAMES SET
			@GAMES.TIME = %d,
			@GAMES.UNITS = %d
			WHERE @GAMES.GAME = %d',
			$game->getTime(), $game->getUnits(), $game->getId()
		)->free();
	}

	public function updateState(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'UPDATE @GAMES SET
			@GAMES.STATE = %d,
			@GAMES.TIME = %d
			WHERE @GAMES.GAME = %d',
			$game->getState(), $game->getTime(), $game->getId()
		)->free();
	}

	public function updateStateAndTurn(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'UPDATE @GAMES SET
			@GAMES.STATE = %d,
			@GAMES.TURN = %d,
			@GAMES.TIME = %d
			WHERE @GAMES.GAME = %d',
			$game->getState(), $game->getTurn(), $game->getTime(), $game->getId()
		)->free();
	}

	public function updateConquered(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'UPDATE @GAMES SET
			@GAMES.STATE = %d,
			@GAMES.CONQUERED = %b,
			@GAMES.TIME = %d
			WHERE @GAMES.GAME = %d',
			$game->getState(), $game->hasConquered(), $game->getTime(), $game->getId()
		)->free();
	}

	public function gameExists($gid) {
		$db = $this->getManager();
		return $db->exists($db->preparedStatement('SELECT 1 FROM @GAMES WHERE @GAMES.GAME = %d', $gid));
	}

	public function deleteOldGames($finishedTime, $time) {
		$db = $this->getManager();
		$query = $db->preparedStatement('SELECT @GAMES.GAME FROM @GAMES WHERE (@GAMES.STATE = %d AND @GAMES.TIME < %d) OR @GAMES.TIME < %d', \imperator\Game::STATE_FINISHED, $finishedTime, $time);
		$games = array();
		while($result = $query->fetchResult()) {
			$games[] = $result->getInt(0);
		}
		$query->free();
		\imperator\Game::delete($games);
		return count($games);
	}

	/**
	 * Deletes multiple games.
	 * 
	 * @param int[] $games
	 */
	public function deleteGames(array $games) {
		Imperator::getLogger()->log(\imperator\Logger::LEVEL_DEBUG, 'Deleting games ['.implode(', ', $games).'].');
		$db = $this->getManager();
		array_unshift($games, 'DELETE FROM @GAMES WHERE @GAMES.GAME '.$db->createIn(count($games), '%d'));
		call_user_func_array(array($db, 'preparedStatement'), $games)->free();
	}
}