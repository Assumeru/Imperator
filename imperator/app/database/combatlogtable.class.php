<?php
namespace imperator\database;
use imperator\Imperator;

class CombatLogTable extends Table {
	const NAME						= 'imperator_combatlog';
	const COLUMN_GID				= 'gid';
	const COLUMN_TYPE				= 'type';
	const COLUMN_TIME				= 'time';
	const COLUMN_UID				= 'uid';
	const COLUMN_DEFENDER			= 'num';
	const COLUMN_ATTACK_ROLL		= 'char_three';
	const COLUMN_DEFEND_ROLL		= 'd_roll';
	const COLUMN_CARDS				= 'char_three';
	const COLUMN_UNITS				= 'num';
	const COLUMN_ATTACKING_TERRITORY= 'territory';
	const COLUMN_DEFENDING_TERRITORY= 'd_territory';
	const COLUMN_CONQUERED_TERRITORY= 'territory';

	public function deleteGame(\imperator\Game $game) {
		$this->getManager()->delete(static::NAME, static::COLUMN_GID.' = '.$game->getId())->free();
	}

	public function saveLogEntry(\imperator\combatlog\LogEntry $entry) {
		$this->getManager()->insert(static::NAME, array(
			static::COLUMN_GID => $entry->getUser()->getGame()->getId(),
			static::COLUMN_TYPE => $entry::TYPE,
			static::COLUMN_TIME => $entry->getTime(),
			static::COLUMN_UID => $entry->getUser()->getId()
		));
	}

	public function saveAttackedEntry(\imperator\combatlog\AttackedEntry $entry) {
		$this->getManager()->insert(static::NAME, array(
			static::COLUMN_GID => $entry->getUser()->getGame()->getId(),
			static::COLUMN_TYPE => $entry::TYPE,
			static::COLUMN_TIME => $entry->getTime(),
			static::COLUMN_UID => $entry->getUser()->getId(),
			static::COLUMN_DEFENDER => $entry->getDefender()->getId(),
			static::COLUMN_ATTACK_ROLL => implode('', $entry->getAttackRoll()),
			static::COLUMN_DEFEND_ROLL => implode('', $entry->getDefendRoll()),
			static::COLUMN_ATTACKING_TERRITORY => $entry->getAttackingTerritory()->getId(),
			static::COLUMN_DEFENDING_TERRITORY => $entry->getDefendingTerritory()->getId()
		));
	}

	public function saveCardsEntry(\imperator\combatlog\CardsPlayedEntry $entry) {
		$this->getManager()->insert(static::NAME, array(
			static::COLUMN_GID => $entry->getUser()->getGame()->getId(),
			static::COLUMN_TYPE => $entry::TYPE,
			static::COLUMN_TIME => $entry->getTime(),
			static::COLUMN_UID => $entry->getUser()->getId(),
			static::COLUMN_CARDS => implode('', $entry->getCards()),
			static::COLUMN_UNITS => $entry->getUnits()
		));
	}

	public function saveConqueredEntry(\imperator\combatlog\ConqueredEntry $entry) {
		$this->getManager()->insert(static::NAME, array(
			static::COLUMN_GID => $entry->getUser()->getGame()->getId(),
			static::COLUMN_TYPE => $entry::TYPE,
			static::COLUMN_TIME => $entry->getTime(),
			static::COLUMN_UID => $entry->getUser()->getId(),
			static::COLUMN_CONQUERED_TERRITORY => $entry->getTerritory()->getId()
		));
	}

	public function getLogsAfter(\imperator\Game $game, $time) {
		$logs = array();
		$query = $this->getManager()->query('SELECT * FROM '.static::NAME.' WHERE `'.static::COLUMN_TIME.'` > '.$time);
		$map = $game->getMap();
		while($result = $query->fetchResult()) {
			$type = $result->getInt(static::COLUMN_TYPE);
			$user = $game->getPlayerById($result->getInt(static::COLUMN_UID));
			$time = $result->getInt(static::COLUMN_TIME);
			if($type == \imperator\combatlog\AttackedEntry::TYPE) {
				$logs[] = new \imperator\combatlog\AttackedEntry(
					$time,
					$user,
					$game->getPlayerById($result->getInt(static::COLUMN_DEFENDER)),
					str_split($result->get(static::COLUMN_ATTACK_ROLL)),
					str_split($result->get(static::COLUMN_DEFEND_ROLL)),
					$map->getTerritoryById($result->get(static::COLUMN_ATTACKING_TERRITORY)),
					$map->getTerritoryById($result->get(static::COLUMN_DEFENDING_TERRITORY))
				);
			} else if($type == \imperator\combatlog\CardsPlayedEntry::TYPE) {
				$logs[] = new \imperator\combatlog\CardsPlayedEntry(
					$time,
					$user,
					str_split($result->get(static::COLUMN_CARDS)),
					$result->getInt(static::COLUMN_UNITS)
				);
			} else if($type == \imperator\combatlog\EndedTurnEntry::TYPE) {
				$logs[] = new \imperator\combatlog\EndedTurnEntry($time, $user);
			} else if($type == \imperator\combatlog\ForfeitedEntry::TYPE) {
				$logs[] = new \imperator\combatlog\ForfeitedEntry($time, $user);
			} else if($type == \imperator\combatlog\ConqueredEntry::TYPE) {
				$logs[] = new \imperator\combatlog\ConqueredEntry(
					$time,
					$user,
					$map->getTerritoryById($result->get(static::COLUMN_CONQUERED_TERRITORY))
				);
			}
		}
		$query->free();
		return $logs;
	}
}