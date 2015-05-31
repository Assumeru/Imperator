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

	public function updateUnits(\imperator\map\Territory $territory) {
		$this->getManager()->update(static::NAME, array(
			static::COLUMN_UNITS => $territory->getUnits()
		), static::COLUMN_TERRITORY.' = \''.$this->getManager()->escape($territory->getId()).'\' AND '.static::COLUMN_GID.' = '.$territory->getGame()->getId())->free();
	}

	public function updateUnitsAndOwner(\imperator\map\Territory $territory) {
		$this->getManager()->update(static::NAME, array(
			static::COLUMN_UNITS => $territory->getUnits(),
			static::COLUMN_UID => $territory->getOwner()->getId()
		), static::COLUMN_TERRITORY.' = \''.$this->getManager()->escape($territory->getId()).'\' AND '.static::COLUMN_GID.' = '.$territory->getGame()->getId())->free();
	}

	/**
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

	public function loadMap(\imperator\Game $game) {
		$players = array();
		foreach($game->getPlayers() as $player) {
			$players[$player->getId()] = $player;
		}
		$territories = $game->getMap()->getTerritories();
		$query = $this->getManager()->query('SELECT
			'.static::COLUMN_TERRITORY.',
			'.static::COLUMN_UID.',
			'.static::COLUMN_UNITS.'
			FROM '.static::NAME.'
			WHERE '.static::COLUMN_GID.' = '.$game->getId()
		);
		while($result = $query->fetchResult()) {
			$id = $result->get(static::COLUMN_TERRITORY);
			$uid = $result->getInt(static::COLUMN_UID);
			$units = $result->getInt(static::COLUMN_UNITS);
			$territory = $territories[$id];
			$territory->setUnits($units);
			$territory->setOwner($players[$uid]);
		}
		$query->free();
	}
}