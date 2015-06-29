<?php
namespace imperator\database;

class AttacksTable extends Table {
	const NAME						= 'imperator_attacks';
	const COLUMN_GID				= 'gid';
	const COLUMN_ATTACKING_TERRITORY= 'a_territory';
	const COLUMN_DEFENDING_TERRITORY= 'd_territory';
	const COLUMN_DICE_ROLL			= 'a_roll';
	const COLUMN_TRANSFERING_UNITS	= 'transfer';

	/**
	 * @return bool
	 */
	public function playerHasToDefend(\imperator\game\Player $user) {
		$t = $this->getManager()->getTable('Territories');
		return $this->getManager()->rowExists(static::NAME.' AS a
				JOIN '.$t::NAME.' AS t
				ON (a.'.static::COLUMN_GID.' = t.'.$t::COLUMN_GID.'
				AND a.'.static::COLUMN_DEFENDING_TERRITORY.' = t.'.$t::COLUMN_TERRITORY.')',
			'a.'.static::COLUMN_GID.' = '.$user->getGame()->getId().'
				AND '.$t::COLUMN_UID.' = '.$user->getId());
	}

	/**
	 * @param \imperator\Game $game
	 * @return bool
	 */
	public function gameHasAttacks(\imperator\Game $game) {
		return $this->getManager()->rowExists(static::NAME, static::COLUMN_GID.' = '.$game->getId());
	}

	/**
	 * @param \imperator\map\Territory $attacker
	 * @param \imperator\map\Territory $defender
	 * @return bool
	 */
	public function territoriesAreInCombat(\imperator\map\Territory $attacker, \imperator\map\Territory $defender) {
		$manager = $this->getManager();
		return $manager->rowExists(static::NAME, static::COLUMN_GID.' = '.$attacker->getGame()->getId().'
			AND ('.static::COLUMN_ATTACKING_TERRITORY.' = \''.$manager->escape($attacker->getId()).'\'
			OR '.static::COLUMN_DEFENDING_TERRITORY.' = \''.$manager->escape($defender->getId()).'\')');
	}

	public function insertAttack(\imperator\game\Attack $attack) {
		$this->getManager()->insert(static::NAME, array(
			static::COLUMN_GID => $attack->getAttacker()->getGame()->getId(),
			static::COLUMN_ATTACKING_TERRITORY => $attack->getAttacker()->getId(),
			static::COLUMN_DEFENDING_TERRITORY => $attack->getDefender()->getId(),
			static::COLUMN_TRANSFERING_UNITS => $attack->getMove(),
			static::COLUMN_DICE_ROLL => implode('', $attack->getAttackRoll())
		))->free();
	}

	/**
	 * @param \imperator\Game $game
	 * @return \imperator\game\Attack[]
	 */
	public function getAttacksFor(\imperator\Game $game) {
		$attacks = array();
		$query = $this->getManager()->query('SELECT * FROM '.static::NAME.' WHERE '.static::COLUMN_GID.' = '.$game->getId());
		while($result = $query->fetchResult()) {
			$attacks[] = new \imperator\game\Attack(
				$game->getMap()->getTerritoryById($result->get(static::COLUMN_ATTACKING_TERRITORY)),
				$game->getMap()->getTerritoryById($result->get(static::COLUMN_DEFENDING_TERRITORY)),
				$result->getInt(static::COLUMN_TRANSFERING_UNITS),
				str_split($result->get(static::COLUMN_DICE_ROLL))
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
		$manager = $this->getManager();
		$query = $manager->query('SELECT '.static::COLUMN_DICE_ROLL.', '.static::COLUMN_TRANSFERING_UNITS.'
			FROM '.static::NAME.'
			WHERE '.static::COLUMN_GID.' = '.$attacker->getGame()->getId().'
			AND '.static::COLUMN_ATTACKING_TERRITORY.' = \''.$manager->escape($attacker->getId()).'\'
			AND '.static::COLUMN_DEFENDING_TERRITORY.' = \''.$manager->escape($defender->getId()).'\'');
		if($result = $query->fetchResult()) {
			$attack = new \imperator\game\Attack(
				$attacker, $defender, 
				$result->getInt(static::COLUMN_TRANSFERING_UNITS),
				str_split($result->get(static::COLUMN_DICE_ROLL))
			);
		}
		$query->free();
		return $attack;
	}

	public function deleteAttack(\imperator\game\Attack $attack) {
		$manager = $this->getManager();
		$manager->delete(static::NAME, static::COLUMN_GID.' = '.$attack->getAttacker()->getGame()->getId().'
			AND '.static::COLUMN_ATTACKING_TERRITORY.' = \''.$manager->escape($attack->getAttacker()->getId()).'\'
			AND '.static::COLUMN_DEFENDING_TERRITORY.' = \''.$manager->escape($attack->getDefender()->getId()).'\'')->free();
	}

	public function removeAttacksFromGame(\imperator\Game $game) {
		$this->getManager()->delete(static::NAME, static::COLUMN_GID.' = '.$game->getId())->free();
	}
}