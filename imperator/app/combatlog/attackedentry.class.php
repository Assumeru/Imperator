<?php
namespace imperator\combatlog;

class AttackedEntry extends LogEntry {
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

	protected function getDefender() {
		return $this->defender;
	}

	protected function getAttackRoll() {
		return $this->attackRoll;
	}

	protected function getDefendRoll() {
		return $this->defendRoll;
	}

	protected function getAttackingTerritory() {
		return $this->attackingTerritory;
	}

	protected function getDefendingTerritory() {
		return $this->defendingTerritory;
	}
}