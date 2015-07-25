<?php
namespace imperator\combatlog;
use imperator\Imperator;

class AttackedEntry extends LogEntry {
	const TYPE = 1;
	private $defender;
	private $attackRoll;
	private $defendRoll;
	private $attackingTerritory;
	private $defendingTerritory;

	public function __construct($time, \imperator\game\Player $user, \imperator\game\Player $defender, array $attackRoll, array $defendRoll, \imperator\map\Territory $attacking, \imperator\map\Territory $defending) {
		parent::__construct($time, $user);
		$this->defender = $defender;
		$this->attackRoll = $attackRoll;
		$this->defendRoll = $defendRoll;
		$this->attackingTerritory = $attacking;
		$this->defendingTerritory = $defending;
	}

	public function getDefender() {
		return $this->defender;
	}

	public function getAttackRoll() {
		return $this->attackRoll;
	}

	public function getDefendRoll() {
		return $this->defendRoll;
	}

	public function getAttackingTerritory() {
		return $this->attackingTerritory;
	}

	public function getDefendingTerritory() {
		return $this->defendingTerritory;
	}

	public function getMessage(\imperator\Language $language) {
		return $language->translate(
			'%1$s vs %2$s: %3$s %4$s',
			\imperator\page\Template::getInstance('game_territory_link', $language)->setVariables(array(
				'territory' => $this->attackingTerritory,
				'color' => $this->getUser()->getColor()
			))->execute(),
			\imperator\page\Template::getInstance('game_territory_link', $language)->setVariables(array(
				'territory' => $this->defendingTerritory,
				'color' => $this->defender->getColor()
			))->execute(),
			\imperator\page\Template::getInstance('roll')->setVariables(array(
				'dice' => $this->attackRoll,
				'type' => 'attack'
			))->execute(),
			\imperator\page\Template::getInstance('roll')->setVariables(array(
				'dice' => $this->defendRoll,
				'type' => 'defend'
			))->execute()
		);
	}

	public function save() {
		Imperator::getDatabaseManager()->getTable('CombatLog')->saveAttackedEntry($this);
	}
}