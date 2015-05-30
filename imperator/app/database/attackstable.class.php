<?php
namespace imperator\database;

class AttacksTable extends Table {
	const NAME						= 'imperator_attacks';
	const COLUMN_GID				= 'gid';
	const COLUMN_ATTACKING_TERRITORY= 'a_territory';
	const COLUMN_DEFENDING_TERRITORY= 'd_territory';
	const COLUMN_ATTACKING_UID		= 'a_uid';
	const COLUMN_DEFENDING_UID		= 'd_uid';
	const COLUMN_DICE_ROLL			= 'a_roll';
	const COLUMN_TRANSFERING_UNITS	= 'transfer';

	/**
	 * @return bool
	 */
	public function playerHasToDefend(\imperator\Game $game, \imperator\User $user) {
		return $this->getManager()->rowExists(static::NAME,
			static::COLUMN_GID.' = '.$game->getId().'
			AND '.static::COLUMN_DEFENDING_UID.' = '.$user->getId());
	}

	/**
	 * @param \imperator\Game $game
	 * @return bool
	 */
	public function gameHasAttacks(\imperator\Game $game) {
		return $this->getManager()->rowExists(static::NAME, static::COLUMN_GID.' = '.$game->getId());
	}

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
			static::COLUMN_ATTACKING_UID => $attack->getAttacker()->getOwner()->getId(),
			static::COLUMN_DEFENDING_TERRITORY => $attack->getDefender()->getId(),
			static::COLUMN_DEFENDING_UID => $attack->getDefender()->getOwner()->getId(),
			static::COLUMN_TRANSFERING_UNITS => $attack->getMove(),
			static::COLUMN_DICE_ROLL => implode(' ', $attack->getAttackRoll())
		))->free();
	}
}