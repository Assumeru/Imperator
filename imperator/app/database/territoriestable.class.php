<?php
namespace imperator\database;

class TerritoriesTable extends Table {
	const NAME				= 'imperator_territories';
	const COLUMN_GID		= 'gid';
	const COLUMN_TERRITORY	= 'territory';
	const COLUMN_UID		= 'uid';
	const COLUMN_UNITS		= 'units';

	public function removeTerritoriesFromGame(\imperator\Game $game) {
		$this->getManager()->delete(static::NAME, static::COLUMN_GID.' = '.$game->getId())->free();
	}

	/**
	 * 
	 * @param \imperator\map\Territory[] $territories
	 */
	public function saveTerritories(array $territories) {
		$insert = array();
		foreach($territories as $territory) {
			$insert[] = array(
				static::COLUMN_GID => $territory->getGame()->getId(),
				static::COLUMN_TERRITORY => $territory->getId(),
				static::COLUMN_UID => $territory->getOwner()->getId(),
				static::COLUMN_UNITS => $territory->getUnits()
			);
		}
		$this->getManager()->insertMultiple(static::NAME, $insert)->free();
	}
}