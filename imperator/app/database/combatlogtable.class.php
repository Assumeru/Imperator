<?php
namespace imperator\database;

class CombatLogTable extends Table {
	const NAME				= 'imperator_combatlog';
	const COLUMN_GID		= 'gid';
	const COLUMN_TIME		= 'time';
	const COLUMN_MESSAGE	= 'message';

	public function deleteGame(\imperator\Game $game) {
		$this->getManager()->delete(static::NAME, static::COLUMN_GID.' = '.$game->getId())->free();
	}
}