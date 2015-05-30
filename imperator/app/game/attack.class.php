<?php
namespace imperator\game;
use imperator\Imperator;

class Attack {
	private $attacker;
	private $defender;
	private $attackRoll;
	private $defendRoll;
	private $attackLosses;
	private $defendLosses;
	private $move;

	public function __construct(\imperator\map\Territory $attacker, $move, \imperator\map\Territory $defender, array $attackRoll = null, array $defendRoll = null) {
		$this->attacker = $attacker;
		$this->defender = $defender;
		$this->move = $move;
		$this->attackRoll = $attackRoll;
		$this->defendRoll = $defendRoll;
	}

	public function getAttacker() {
		return $this->attacker;
	}

	public function getDefender() {
		return $this->defender;
	}

	public function getAttackRoll() {
		return $this->attackRoll;
	}

	public function getDefenceRoll() {
		return $this->defendRoll;
	}

	public function getAttackerLosses() {
		return $this->attackLosses;
	}

	public function getDefenderLosses() {
		return $this->defendLosses;
	}

	public function getMove() {
		return $this->move;
	}

	private function rollDice($dice) {
		$roll = array();
		for($n = 0; $n < $dice; $n++) {
			$roll[] = mt_rand(1, 6);
		}
		sort($roll);
		$roll = array_reverse($roll);
		return $roll;
	}

	public function rollAttack($dice) {
		$this->attackRoll = $this->rollDice($dice);
		$this->calculateLosses();
	}

	public function rollDefence($dice) {
		$this->defendRoll = $this->rollDice($dice);
		$this->calculateLosses();
	}

	public function attackerCannotWin() {
		return count($this->attackRoll) === 1 && $this->attackRoll[0] === 1;
	}

	public function autoRollDefence() {
		$dice = 1;
		if($this->defender->getUnits() > 1 && (count($this->attackRoll) === 1 || ($this->attackRoll[0] + $this->attackRoll[1]) / 2 <= 3.5 || $this->attackRoll[1] < 4)) {
			$defendRoll[] = mt_rand(1,6);
		}
		$this->rollDefence($dice);
	}

	private function calculateLosses() {
		if($this->attackRoll && $this->defendRoll) {
			$this->attackLosses = 0;
			$this->defendLosses = 0;
			for($n = 0; $n < count($this->defendRoll) && $n < count($this->attackRoll); $n++) {
				if($this->attackRoll[$n] > $this->defendRoll[$n]) {
					$this->defendLosses++;
				} else {
					$this->attackLosses++;
				}
			}
		}
	}

	public function save() {
		Imperator::getDatabaseManager()->getTable('Attacks')->insertAttack($this);
	}
}