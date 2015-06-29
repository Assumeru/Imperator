<?php
namespace imperator\database;
use imperator\Imperator;

class GamesTable extends Table {
	const NAME				= 'imperator_games';
	const COLUMN_GID		= 'gid';
	const COLUMN_MAP		= 'map';
	const COLUMN_NAME		= 'name';
	const COLUMN_UID		= 'uid';
	const COLUMN_TURN		= 'turn';
	const COLUMN_TIME		= 'time';
	const COLUMN_STATE		= 'state';
	const COLUMN_UNITS		= 'units';
	const COLUMN_CONQUERED	= 'conquered';
	const COLUMN_PASSWORD	= 'password';

	/**
	 * Deletes a game from the database.
	 * 
	 * @param \imperator\Game $game
	 */
	public function deleteGame(\imperator\Game $game) {
		$this->getManager()->delete(static::NAME, static::COLUMN_GID.' = '.$game->getId())->free();
	}

	/**
	 * Inserts a game into the database.
	 * 
	 * @param \imperator\Game $game
	 */
	public function createNewGame(\imperator\Game $game) {
		$array = array(
			static::COLUMN_MAP => $game->getMap()->getId(),
			static::COLUMN_NAME => $game->getName(),
			static::COLUMN_UID => $game->getOwner()->getId(),
			static::COLUMN_TIME => time()
		);
		if($game->hasPassword()) {
			$array[static::COLUMN_PASSWORD] = $game->getPassword();
		}
		$query = $this->getManager()->insert(static::NAME, $array);
		$game->setId($query->getInsertId());
		$query->free();
	}

	/**
	 * 
	 * @param \imperator\User $user
	 * @return \imperator\Game[]
	 */
	public function getGamesFor(\imperator\User $user) {
		$gj = $this->getManager()->getTable('GamesJoined');
		$u = $this->getManager()->getTable('OutsideUsers');
		$sql = 'SELECT
				g.'.static::COLUMN_GID.',
				g.'.static::COLUMN_NAME.',
				g.'.static::COLUMN_MAP.',
				COUNT(gj.'.$gj::COLUMN_UID.') AS players,
				g.'.static::COLUMN_UID.',
				g.'.static::COLUMN_STATE.',
				g.'.static::COLUMN_TURN.',
				g.'.static::COLUMN_PASSWORD.',
				u.'.$u::COLUMN_USERNAME.'
			FROM '.static::NAME.' AS g
			JOIN '.$gj::NAME.' AS gj ON(g.'.static::COLUMN_GID.' = gj.'.$gj::COLUMN_GID.')
			JOIN '.$u::NAME.' AS u ON(g.'.static::COLUMN_UID.' = u.'.$u::COLUMN_UID.')
			WHERE gj.'.$gj::COLUMN_GID.' IN(
				SELECT '.$gj::COLUMN_GID.'
				FROM '.$gj::NAME.'
				WHERE '.$gj::COLUMN_UID.' = '.$user->getId().'
			)
			GROUP BY gj.'.$gj::COLUMN_GID.'
			ORDER BY '.static::COLUMN_MAP.', players ASC';
		return $this->getGames($this->getManager()->query($sql), $u);
	}

	/**
	 * 
	 * @return \imperator\Game[]
	 */
	public function getAllGames() {
		$gj = $this->getManager()->getTable('GamesJoined');
		$u = $this->getManager()->getTable('OutsideUsers');
		$sql = 'SELECT
				g.'.static::COLUMN_GID.',
				g.'.static::COLUMN_NAME.',
				g.'.static::COLUMN_MAP.',
				COUNT(gj.'.$gj::COLUMN_UID.') AS players,
				g.'.static::COLUMN_UID.',
				g.'.static::COLUMN_STATE.',
				g.'.static::COLUMN_TURN.',
				g.'.static::COLUMN_PASSWORD.',
				u.'.$u::COLUMN_USERNAME.'
			FROM '.static::NAME.' AS g
			JOIN '.$gj::NAME.' AS gj ON(g.'.static::COLUMN_GID.' = gj.'.$gj::COLUMN_GID.')
			JOIN '.$u::NAME.' AS u ON(g.'.static::COLUMN_UID.' = u.'.$u::COLUMN_UID.')
			GROUP BY gj.'.$gj::COLUMN_GID.'
			ORDER BY '.static::COLUMN_MAP.', players ASC';
		return $this->getGames($this->getManager()->query($sql), $u);
	}

	/**
	 * 
	 * @param int $gameId
	 * @return \imperator\Game
	 */
	public function getGameById($gameId) {
		$game = null;
		$gameId = $gameId;
		$userClass = Imperator::getSettings()->getUserClass();
		$query = $this->getManager()->query('SELECT * FROM '.static::NAME.' WHERE '.static::COLUMN_GID.' = '.$gameId);
		if($result = $query->fetchResult()) {
			$game = new \imperator\Game(
				$result->getInt(static::COLUMN_GID),
				new \imperator\game\Player(new $userClass($result->getInt(static::COLUMN_UID))),
				$result->get(static::COLUMN_NAME),
				$result->getInt(static::COLUMN_MAP),
				$result->getInt(static::COLUMN_STATE),
				$result->getInt(static::COLUMN_TURN),
				1,
				$result->get(static::COLUMN_PASSWORD),
				$result->getInt(static::COLUMN_TIME),
				$result->getBool(static::COLUMN_CONQUERED),
				$result->getInt(static::COLUMN_UNITS)
			);
			$players = $this->getManager()->getTable('GamesJoined')->getPlayersForGame($game);
			$game->setPlayers($players);
			foreach($players as $player) {
				if($player->getUser()->equals($game->getOwner()->getUser())) {
					$game->setOwner($player);
					break;
				}
			}
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
	private function getGames(Query $query, OutsideUsersTable $u) {
		$games = array();
		$userClass = Imperator::getSettings()->getUserClass();
		while($result = $query->fetchResult()) {
			$player = new \imperator\game\Player(new $userClass($result->getInt(static::COLUMN_UID), $result->get($u::COLUMN_USERNAME)));
			$game = new \imperator\Game(
				$result->getInt(static::COLUMN_GID),
				$player,
				$result->get(static::COLUMN_NAME),
				$result->getInt(static::COLUMN_MAP),
				$result->getInt(static::COLUMN_STATE),
				$result->getInt(static::COLUMN_TURN),
				$result->getInt('players'),
				$result->get(static::COLUMN_PASSWORD)
			);
			$player->setGame($game);
			$games[] = $game;
		}
		$query->free();
		return $games;
	}

	public function updateTime(\imperator\Game $game) {
		$this->getManager()->update(static::NAME, array(
			static::COLUMN_TIME => time()
		), static::COLUMN_GID.' = '.$game->getId())->free();
	}

	public function updateTurn(\imperator\Game $game) {
		$this->getManager()->update(static::NAME, array(
			static::COLUMN_TURN => $game->getTurn(),
			static::COLUMN_TIME => time()
		), static::COLUMN_GID.' = '.$game->getId())->free();
	}

	/**
	 * @param int $gid
	 * @param \imperator\User $user
	 * @return bool
	 */
	public function gameOwnerEquals($gid, \imperator\User $user) {
		return $this->getManager()->rowExists(static::NAME,
			static::COLUMN_GID.' = '.$gid.'
			AND '.static::COLUMN_UID.' = '.$user->getId()
		);
	}

	/**
	 * @param int $gid
	 * @param int $time
	 * @return bool
	 */
	public function timeIsAfter($gid, $time) {
		return $this->getManager()->rowExists(static::NAME,
			static::COLUMN_GID.' = '.$gid.'
			AND '.static::COLUMN_TIME.' > '.$time
		);
	}

	public function nextTurn(\imperator\Game $game) {
		$this->getManager()->update(static::NAME, array(
			static::COLUMN_CONQUERED => (int)$game->hasConquered(),
			static::COLUMN_STATE => $game->getState(),
			static::COLUMN_TIME => $game->getTime(),
			static::COLUMN_TURN => $game->getTurn(),
			static::COLUMN_UNITS => $game->getUnits()
		), static::COLUMN_GID.' = '.$game->getId())->free();
	}

	public function updateUnitsAndState(\imperator\Game $game) {
		$this->getManager()->update(static::NAME, array(
			static::COLUMN_STATE => $game->getState(),
			static::COLUMN_UNITS => $game->getUnits(),
			static::COLUMN_TIME => $game->getTime()
		), static::COLUMN_GID.' = '.$game->getId())->free();
	}

	public function updateUnits(\imperator\Game $game) {
		$this->getManager()->update(static::NAME, array(
			static::COLUMN_UNITS => $game->getUnits(),
			static::COLUMN_TIME => $game->getTime()
		), static::COLUMN_GID.' = '.$game->getId())->free();
	}

	public function updateState(\imperator\Game $game) {
		$this->getManager()->update(static::NAME, array(
			static::COLUMN_STATE => $game->getState(),
			static::COLUMN_TIME => $game->getTime()
		), static::COLUMN_GID.' = '.$game->getId())->free();
	}

	public function updateStateAndTurn(\imperator\Game $game) {
		$this->getManager()->update(static::NAME, array(
			static::COLUMN_TURN => $game->getTurn(),
			static::COLUMN_STATE => $game->getState(),
			static::COLUMN_TIME => $game->getTime()
		), static::COLUMN_GID.' = '.$game->getId())->free();
	}

	public function updateConquered(\imperator\Game $game) {
		$this->getManager()->update(static::NAME, array(
			static::COLUMN_STATE => $game->getState(),
			static::COLUMN_TIME => $game->getTime(),
			static::COLUMN_CONQUERED => (int)$game->hasConquered()
		), static::COLUMN_GID.' = '.$game->getId())->free();
	}
}