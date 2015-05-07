<?php
namespace imperator\database;

use imperator\Imperator;
class GamesJoinedTable extends Table {
	const NAME					= 'imperator_gamesjoined';
	const COLUMN_UID			= 'uid';
	const COLUMN_GID			= 'gid';
	const COLUMN_COLOR			= 'color';
	const COLUMN_AUTOROLL		= 'autoroll';
	const COLUMN_MISSION		= 'mission';
	const COLUMN_MISSION_UID	= 'm_uid';
	const COLUMN_STATE			= 'state';
	const COLUMN_CARD_ARTILLERY	= 'c_art';
	const COLUMN_CARD_CAVALRY	= 'c_cav';
	const COLUMN_CARD_INFANTRY	= 'c_inf';
	const COLUMN_CARD_JOKER		= 'c_jok';

	public function removeUsersFromGame(\imperator\Game $game) {
		$this->getManager()->delete(static::NAME, static::COLUMN_GID.' = '.$game->getId())->free();
	}

	public function addUserToGame(\imperator\User $user, \imperator\Game $game) {
		$this->getManager()->insert(static::NAME, array(
			static::COLUMN_GID => $game->getId(),
			static::COLUMN_UID => $user->getId(),
			static::COLUMN_COLOR => $user->getColor()
		))->free();
	}

	public function removeUserFromGame(\imperator\User $user, \imperator\Game $game) {
		$query = $this->getManager()->delete(static::NAME,
			static::COLUMN_GID.' = '.$game->getId().' AND '.static::COLUMN_UID.' = '.$user->getId());
		$query->free();
	}

	/**
	 * 
	 * @param \imperator\Game $game
	 * @return \imperator\User[]
	 */
	public function getPlayersForGame(\imperator\Game $game) {
		$gid = (int)$game->getId();
		$u = $this->getManager()->getTable('OutsideUsers');
		$sql = 'SELECT
			u.'.$u::COLUMN_USERNAME.', u.'.$u::COLUMN_UID.', g.'.static::COLUMN_COLOR.', g.'.static::COLUMN_STATE.',
			g.'.static::COLUMN_MISSION.', g.'.static::COLUMN_MISSION_UID.', g.'.static::COLUMN_AUTOROLL.'
			FROM '.$u::NAME.' AS u
			JOIN '.static::NAME.' AS g ON(g.'.static::COLUMN_UID.' = u.'.$u::COLUMN_UID.')
			WHERE g.'.static::COLUMN_GID.' = '.$gid.'
			ORDER BY u.'.$u::COLUMN_USERNAME;
		$players = array();
		$query = $this->getManager()->query($sql);
		$userClass = Imperator::getSettings()->getUserClass();
		$missions = $game->getMap()->getMissions();
		while($result = $query->fetchResult()) {
			$player = new $userClass(
				(int)$result[$u::COLUMN_UID],
				$result[$u::COLUMN_USERNAME]
			);
			$player->setColor($result[static::COLUMN_COLOR]);
			$player->setState($result[static::COLUMN_STATE]);
			$player->setAutoRoll($result[static::COLUMN_AUTOROLL]);
			$mission = $missions[$result[static::COLUMN_MISSION]];
			$mission->setUid($result[static::COLUMN_MISSION_UID]);
			$player->setMission($mission);
			$players[] = $player;
		}
		$query->free();
		return $players;
	}

	public function saveMissions(array $players) {
		foreach($players as $player) {
			$mission = $player->getMission();
			$this->getManager()->update(static::NAME, array(
				static::COLUMN_MISSION => $mission->getId(),
				static::COLUMN_MISSION_UID => $mission->getUid()
			), static::COLUMN_UID.' = '.$player->getId())->free();
		}
	}

	/**
	 * @param int $gid
	 * @param \imperator\User $user
	 * @return bool
	 */
	public function gameContainsPlayer($gid, \imperator\User $user) {
		return $this->getManager()->rowExists(static::NAME,
			static::COLUMN_GID.' = '.$gid.' AND '.
			static::COLUMN_UID.' = '.$user->getId());
	}

	public function forfeit(\imperator\Game $game, \imperator\User $user) {
		$this->getManager()->update(static::NAME, array(
				static::COLUMN_STATE => \imperator\User::STATE_GAME_OVER,
				static::COLUMN_AUTOROLL => true
			), static::COLUMN_GID.' = '.$game->getId().'
			AND '.static::COLUMN_UID.' = '.$user->getId()
		)->free();
	}

	/**
	 * @param \imperator\Game $game
	 * @param \imperator\User $user
	 * @return \imperator\game\Cards
	 */
	public function getCardsFor(\imperator\Game $game, \imperator\User $user) {
		$query = $this->getManager()->query('SELECT '.
			static::COLUMN_CARD_ARTILLERY.','.
			static::COLUMN_CARD_CAVALRY.','.
			static::COLUMN_CARD_INFANTRY.','.
			static::COLUMN_CARD_JOKER.'
			FROM '.static::NAME.'
			WHERE '.static::COLUMN_GID.' = '.$game->getId().'
			AND '.static::COLUMN_UID.' = '.$user->getId());
		$result = $query->fetchResult();
		$query->free();
		return new \imperator\game\Cards(
			$result[static::COLUMN_CARD_ARTILLERY],
			$result[static::COLUMN_CARD_CAVALRY],
			$result[static::COLUMN_CARD_INFANTRY],
			$result[static::COLUMN_CARD_JOKER]
		);
	}

	public function getNumberOfJokers(\imperator\Game $game) {
		$query = $this->getManager()->query('SELECT SUM('.static::COLUMN_CARD_JOKER.') AS jokers
			FROM '.static::NAME.'
			WHERE '.static::COLUMN_GID.' = '.$game->getId());
		$result = $query->fetchResult();
		$query->free();
		return (int)$result['jokers'];
	}
}