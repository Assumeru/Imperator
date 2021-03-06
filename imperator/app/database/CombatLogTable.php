<?php
namespace imperator\database;

class CombatLogTable extends Table {
	protected function register(DatabaseManager $manager) {
		$manager->registerTable('LOG', 'imperator_combatlog', array(
			'GAME' => 'gid',
			'TYPE' => '`type`',
			'TIME' => '`time`',
			'USER' => 'uid',
			'DEFENDER' => 'num',
			'ATTACK_ROLL' => 'char_three',
			'DEFEND_ROLL' => 'd_roll',
			'CARDS' => 'char_three',
			'UNITS' => 'units',
			'ATTACKING_TERRITORY' => 'territory',
			'DEFENDING_TERRITORY' => 'd_territory',
			'CONQUERED_TERRITORY' => 'territory'
		));
	}

	public function drop() {
		$this->getManager()->preparedStatement('DROP TABLE IF EXISTS @LOG');
	}

	public function create() {
		$db = $this->getManager();
		$db->preparedStatement(
			'CREATE TABLE @LOG (
				@-LOG.GAME INT,
				@-LOG.TYPE SMALLINT,
				@-LOG.TIME INT,
				@-LOG.USER INT,
				@-LOG.DEFENDER INT,
				@-LOG.ATTACK_ROLL CHAR(3),
				@-LOG.DEFEND_ROLL CHAR(2),
				@-LOG.UNITS INT,
				@-LOG.ATTACKING_TERRITORY VARCHAR(150),
				@-LOG.DEFENDING_TERRITORY VARCHAR(150),
				PRIMARY KEY(@-LOG.GAME, @-LOG.TYPE, @-LOG.TIME, @-LOG.USER),
				FOREIGN KEY (@-LOG.GAME) REFERENCES @GAMES(@-GAMES.GAME) ON DELETE CASCADE
			) CHARACTER SET %s COLLATE %s ENGINE = %s',
			$db->getCharset(), $db->getCollation(), $db->getEngine()
		);
	}

	public function deleteGame(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'DELETE FROM @LOG WHERE @LOG.GAME = %d',
			$game->getId()
		)->free();
	}

	public function saveLogEntry(\imperator\combatlog\LogEntry $entry) {
		$this->getManager()->insert('@LOG', array(
			'@LOG.GAME' => $entry->getUser()->getGame()->getId(),
			'@LOG.TYPE' => $entry::TYPE,
			'@LOG.TIME' => $entry->getTime(),
			'@LOG.USER' => $entry->getUser()->getId()
		));
	}

	public function saveAttackedEntry(\imperator\combatlog\AttackedEntry $entry) {
		$this->getManager()->insert('@LOG', array(
			'@LOG.GAME' => $entry->getUser()->getGame()->getId(),
			'@LOG.TYPE' => $entry::TYPE,
			'@LOG.TIME' => $entry->getTime(),
			'@LOG.USER' => $entry->getUser()->getId(),
			'@LOG.DEFENDER' => $entry->getDefender()->getId(),
			'@LOG.ATTACK_ROLL' => implode('', $entry->getAttackRoll()),
			'@LOG.DEFEND_ROLL' => implode('', $entry->getDefendRoll()),
			'@LOG.ATTACKING_TERRITORY' => $entry->getAttackingTerritory()->getId(),
			'@LOG.DEFENDING_TERRITORY' => $entry->getDefendingTerritory()->getId()
		));
	}

	public function saveCardsEntry(\imperator\combatlog\CardsPlayedEntry $entry) {
		$this->getManager()->insert('@LOG', array(
			'@LOG.GAME' => $entry->getUser()->getGame()->getId(),
			'@LOG.TYPE' => $entry::TYPE,
			'@LOG.TIME' => $entry->getTime(),
			'@LOG.USER' => $entry->getUser()->getId(),
			'@LOG.CARDS' => implode('', $entry->getCards()),
			'@LOG.UNITS' => $entry->getUnits()
		));
	}

	public function saveConqueredEntry(\imperator\combatlog\ConqueredEntry $entry) {
		$this->getManager()->insert('@LOG', array(
			'@LOG.GAME' => $entry->getUser()->getGame()->getId(),
			'@LOG.TYPE' => $entry::TYPE,
			'@LOG.TIME' => $entry->getTime(),
			'@LOG.USER' => $entry->getUser()->getId(),
			'@LOG.CONQUERED_TERRITORY' => $entry->getTerritory()->getId()
		));
	}

	public function getLogsAfter(\imperator\Game $game, $time) {
		$logs = array();
		$query = $this->getManager()->preparedStatement('SELECT * FROM @LOG WHERE @LOG.TIME > %d AND @LOG.GAME = %d ORDER BY @LOG.TIME ASC', $time, $game->getId());
		$map = $game->getMap();
		while($result = $query->fetchResult()) {
			$type = $result->getInt(1);
			$user = $game->getPlayerById($result->getInt(3));
			$time = $result->getInt(2);
			if($type == \imperator\combatlog\AttackedEntry::TYPE) {
				$logs[] = new \imperator\combatlog\AttackedEntry(
					$time,
					$user,
					$game->getPlayerById($result->getInt(4)),
					str_split($result->get(5)),
					str_split($result->get(6)),
					$map->getTerritoryById($result->get(8)),
					$map->getTerritoryById($result->get(9))
				);
			} else if($type == \imperator\combatlog\CardsPlayedEntry::TYPE) {
				$logs[] = new \imperator\combatlog\CardsPlayedEntry(
					$time,
					$user,
					str_split($result->get(5)),
					$result->getInt(7)
				);
			} else if($type == \imperator\combatlog\EndedTurnEntry::TYPE) {
				$logs[] = new \imperator\combatlog\EndedTurnEntry($time, $user);
			} else if($type == \imperator\combatlog\ForfeitedEntry::TYPE) {
				$logs[] = new \imperator\combatlog\ForfeitedEntry($time, $user);
			} else if($type == \imperator\combatlog\ConqueredEntry::TYPE) {
				$logs[] = new \imperator\combatlog\ConqueredEntry(
					$time,
					$user,
					$map->getTerritoryById($result->get(8))
				);
			}
		}
		$query->free();
		return $logs;
	}
}