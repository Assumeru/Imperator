<?php
namespace imperator\database;

class AttacksTable extends Table {
	protected function register(DatabaseManager $manager) {
		$manager->registerTable('ATTACKS', 'imperator_attacks', array(
			'GAME' => 'gid',
			'ATTACKING_TERRITORY' => 'a_territory',
			'DEFENDING_TERRITORY' => 'd_territory',
			'DICE_ROLL' => 'a_roll',
			'TRANSFERING_UNITS' => 'transfer'
		));
	}

	public function create() {
		$this->getManager()->preparedStatement(
			'CREATE TABLE @ATTACKS (
				@-ATTACKS.GAME INT REFERENCES @GAMES(@-GAMES.GAME) ON DELETE CASCADE,
				@-ATTACKS.ATTACKING_TERRITORY VARCHAR(150),
				@-ATTACKS.DEFENDING_TERRITORY VARCHAR(150),
				@-ATTACKS.DICE_ROLL CHAR(3) NOT NULL,
				@-ATTACKS.TRANSFERING_UNITS INT NOT NULL DEFAULT "1",
				PRIMARY KEY(@-ATTACKS.GAME, @-ATTACKS.ATTACKING_TERRITORY, @-ATTACKS.DEFENDING_TERRITORY)
			)'
		);
	}

	public function drop() {
		$this->getManager()->preparedStatement('DROP TABLE IF EXISTS @ATTACKS');
	}

	/**
	 * @return bool
	 */
	public function playerHasToDefend(\imperator\game\Player $user) {
		$db = $this->getManager();
		return $db->exists($db->preparedStatement(
			'SELECT 1
			FROM @ATTACKS
			JOIN @TERRITORIES
			ON (@ATTACKS.GAME = @TERRITORIES.GAME AND @ATTACKS.DEFENDING_TERRITORY = @TERRITORIES.TERRITORY)
			WHERE @ATTACKS.GAME = %d AND @TERRITORIES.USER = %d',
			$user->getGame()->getId(),
			$user->getId()
		));
	}

	/**
	 * @param \imperator\Game $game
	 * @return bool
	 */
	public function gameHasAttacks(\imperator\Game $game) {
		$db = $this->getManager();
		return $db->exists($db->preparedStatement('SELECT 1 FROM @ATTACKS WHERE @ATTACKS.GAME = %d', $game->getId()));
	}

	/**
	 * @param \imperator\map\Territory $attacker
	 * @param \imperator\map\Territory $defender
	 * @return bool
	 */
	public function territoriesAreInCombat(\imperator\map\Territory $attacker, \imperator\map\Territory $defender) {
		$db = $this->getManager();
		$db->exists($db->preparedStatement(
			'SELECT 1 FROM @ATTACKS WHERE @ATTACKS.GAME = %d AND @ATTACKS.ATTACKING_TERRITORY = %s AND @ATTACKS.DEFENDING_TERRITORY = %s',
			$attacker->getGame()->getId(), $attacker->getId(), $defender->getId()
		));
	}

	public function insertAttack(\imperator\game\Attack $attack) {
		$this->getManager()->insert('@ATTACKS', array(
			'@ATTACKS.GAME' => $attack->getAttacker()->getGame()->getId(),
			'@ATTACKS.ATTACKING_TERRITORY' => $attack->getAttacker()->getId(),
			'@ATTACKS.DEFENDING_TERRITORY' => $attack->getDefender()->getId(),
			'@ATTACKS.TRANSFERING_UNITS' => $attack->getMove(),
			'@ATTACKS.DICE_ROLL' => implode('', $attack->getAttackRoll())
		))->free();
	}

	/**
	 * @param \imperator\Game $game
	 * @return \imperator\game\Attack[]
	 */
	public function getAttacksFor(\imperator\Game $game) {
		$attacks = array();
		$query = $this->getManager()->preparedStatement(
			'SELECT @ATTACKS.ATTACKING_TERRITORY,
				@ATTACKS.DEFENDING_TERRITORY,
				@ATTACKS.TRANSFERING_UNITS,
				@ATTACKS.DICE_ROLL
			FROM @ATTACKS
			WHERE @ATTACKS.GAME = %d',
			$game->getId()
		);
		while($result = $query->fetchResult()) {
			$attacks[] = new \imperator\game\Attack(
				$game->getMap()->getTerritoryById($result->get(0)),
				$game->getMap()->getTerritoryById($result->get(1)),
				$result->getInt(2),
				str_split($result->get(3))
			);
		}
		$query->free();
		return $attacks;
	}

	/**
	 * @param \imperator\map\Territory $attacker
	 * @param \imperator\map\Territory $defender
	 * @return \imperator\game\Attack
	 */
	public function getAttack(\imperator\map\Territory $attacker, \imperator\map\Territory $defender) {
		$attack = null;
		$query = $this->getManager()->preparedStatement(
			'SELECT @ATTACKS.DICE_ROLL, @ATTACKS.TRANSFERING_UNITS
			FROM @ATTACKS
			WHERE @ATTACKS.GAME = %d
			AND @ATTACKS.ATTACKING_TERRITORY = %s
			AND @ATTACKS.DEFENDING_TERRITORY = %s',
			$attacker->getGame()->getId(), $attacker->getId(), $defender->getId()
		);
		if($result = $query->fetchResult()) {
			$attack = new \imperator\game\Attack(
				$attacker, $defender, 
				$result->getInt(1),
				str_split($result->get(0))
			);
		}
		$query->free();
		return $attack;
	}

	public function deleteAttack(\imperator\game\Attack $attack) {
		$this->getManager()->preparedStatement(
			'DELETE FROM @ATTACKS WHERE @ATTACKS.GAME = %d AND @ATTACKS.ATTACKING_TERRITORY = %s AND @ATTACKS.DEFENDING_TERRITORY = %s',
			$attack->getAttacker()->getGame()->getId(), $attack->getAttacker()->getId(), $attack->getDefender()->getId()
		)->free();
	}

	public function removeAttacksFromGame(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'DELETE FROM @ATTACKS WHERE @ATTACKS.GAME = %d',
			$game->getId()
		)->free();
	}
}