<?php
namespace imperator\database;

class TerritoriesTable extends Table {
	const NAME				= 'imperator_territories';
	const COLUMN_GID		= 'gid';
	const COLUMN_TERRITORY	= 'territory';
	const COLUMN_UID		= 'uid';
	const COLUMN_UNITS		= 'units';

	public function removeTerritoriesFromGame(\imperator\Game $game) {
		$this->getManager()->delete(static::NAME, static::COLUMN_GID.' = '.$game->getId());
	}
}