<?php
namespace imperator\database;

class TerritoriesTable extends Table {
	const NAME				= 'imperator_territories';
	const COLUMN_GID		= 'gid';
	const COLUMN_TERRITORY	= 'territory';
	const COLUMN_UID		= 'uid';
	const COLUMN_UNITS		= 'units';

	public function distributeTerritories(\imperator\Game $game) {
		$territories = array_values($game->getMap()->getTerritories());
		shuffle($territories);
		$numNations = count($territories) / $game->getNumberOfPlayers();
		$players = $game->getPlayers();
		$n = 0;
		foreach($players as $player) {
			for($i=0; $i < $numNations; $i++, $n++) {
				$territories[$n]->setOwner($player);
				$territories[$n]->setUnits(3);
			}
		}
	}
}