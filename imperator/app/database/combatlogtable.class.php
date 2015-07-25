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
	const COLUMN_ATTACKING_TERRITORY= 'a_territory';
	const COLUMN_DEFENDING_TERRITORY= 'd_territory';

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
}