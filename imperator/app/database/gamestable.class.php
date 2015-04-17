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
				WHERE '.$gj::COLUMN_UID.' = '.((int)$user->getId()).'
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
		$gameId = (int)$gameId;
		$userClass = Imperator::getSettings()->getUserClass();
		$query = $this->getManager()->query('SELECT * FROM '.static::NAME.' WHERE '.static::COLUMN_GID.' = '.$gameId);
		if($result = $query->fetchResult()) {
			$game = new \imperator\Game(
				(int)$result[static::COLUMN_GID],
				new $userClass($result[static::COLUMN_UID]),
				$result[static::COLUMN_NAME],
				(int)$result[static::COLUMN_MAP],
				(int)$result[static::COLUMN_STATE],
				(int)$result[static::COLUMN_TURN],
				1,
				$result[static::COLUMN_PASSWORD],
				$result[static::COLUMN_TIME]
			);
			$players = $this->getManager()->getTable('GamesJoined')->getPlayersForGame($game);
			$game->setPlayers($players);
			foreach($players as $player) {
				if($player->equals($game->getOwner())) {
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
			$games[] = new \imperator\Game(
				(int)$result[static::COLUMN_GID],
				new $userClass($result[static::COLUMN_UID], $result[$u::COLUMN_USERNAME]),
				$result[static::COLUMN_NAME],
				(int)$result[static::COLUMN_MAP],
				(int)$result[static::COLUMN_STATE],
				(int)$result[static::COLUMN_TURN],
				(int)$result['players'],
				$result[static::COLUMN_PASSWORD]
			);
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

	public function gameOwnerEquals($gid, \imperator\User $user) {
		return $this->getManager()->rowExists(static::NAME,
			static::COLUMN_GID.' = '.$gid.'
			AND '.static::COLUMN_UID.' = '.$user->getId()
		);
	}

	public function timeIsAfter($gid, $time) {
		return $this->getManager()->rowExists(static::NAME,
			static::COLUMN_GID.' = '.$gid.'
			AND '.static::COLUMN_TIME.' > '.$time
		);
	}
}