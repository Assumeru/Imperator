<?php
namespace imperator\database;

class TerritoriesTable extends Table {
	protected function register(DatabaseManager $manager) {
		$manager->registerTable('TERRITORIES', 'imperator_territories', array(
			'GAME' => 'gid',
			'TERRITORY' => 'territory',
			'USER' => 'uid',
			'UNITS' => 'units'
		));
	}

	public function create() {
		$db = $this->getManager();
		$db->preparedStatement(
			'CREATE TABLE @TERRITORIES (
				@-TERRITORIES.GAME INT,
				@-TERRITORIES.TERRITORY VARCHAR(150),
				@-TERRITORIES.USER INT,
				@-TERRITORIES.UNITS INT NOT NULL,
				PRIMARY KEY(@-TERRITORIES.GAME, @-TERRITORIES.TERRITORY),
				FOREIGN KEY (@-TERRITORIES.GAME) REFERENCES @GAMES(@-GAMES.GAME) ON DELETE CASCADE
			) CHARACTER SET %s COLLATE %s ENGINE = %s',
			$db->getCharset(), $db->getCollation(), $db->getEngine()
		);
	}

	public function drop() {
		$this->getManager()->preparedStatement('DROP TABLE IF EXISTS @TERRITORIES');
	}

	public function removeTerritoriesFromGame(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'DELETE FROM @TERRITORIES WHERE @TERRITORIES.GAME = %d',
			$game->getId()
		)->free();
	}

	public function updateUnits(\imperator\map\Territory $territory) {
		$this->getManager()->preparedStatement(
			'UPDATE @TERRITORIES SET @TERRITORIES.UNITS = %d WHERE @TERRITORIES.GAME = %d AND @TERRITORIES.TERRITORY = %s',
			$territory->getUnits(), $territory->getGame()->getId(), $territory->getId()
		)->free();
	}

	public function updateUnitsAndOwner(\imperator\map\Territory $territory) {
		$this->getManager()->preparedStatement(
			'UPDATE @TERRITORIES SET
			@TERRITORIES.USER = %d,
			@TERRITORIES.UNITS = %d
			WHERE @TERRITORIES.GAME = %d AND @TERRITORIES.TERRITORY = %s',
			$territory->getOwner()->getId(), $territory->getUnits(), $territory->getGame()->getId(), $territory->getId()
		)->free();
	}

	/**
	 * @param \imperator\map\Territory[] $territories
	 */
	public function saveTerritories(array $territories) {
		$insert = array();
		foreach($territories as $territory) {
			$insert[] = array(
				'@TERRITORIES.GAME' => $territory->getGame()->getId(),
				'@TERRITORIES.TERRITORY' => $territory->getId(),
				'@TERRITORIES.USER' => $territory->getOwner()->getId(),
				'@TERRITORIES.UNITS' => $territory->getUnits()
			);
		}
		$this->getManager()->insertMultiple('@TERRITORIES', $insert)->free();
	}

	public function loadMap(\imperator\Game $game) {
		$players = array();
		foreach($game->getPlayers() as $player) {
			$players[$player->getId()] = $player;
		}
		$territories = $game->getMap()->getTerritories();
		$query = $this->getManager()->preparedStatement(
			'SELECT @TERRITORIES.TERRITORY, @TERRITORIES.USER, @TERRITORIES.UNITS
			FROM @TERRITORIES WHERE @TERRITORIES.GAME = %d',
			$game->getId()
		);
		while($result = $query->fetchResult()) {
			$id = $result->get(0);
			$uid = $result->getInt(1);
			$units = $result->getInt(2);
			$territory = $territories[$id];
			$territory->setUnits($units);
			$territory->setOwner($players[$uid]);
		}
		$query->free();
	}
}