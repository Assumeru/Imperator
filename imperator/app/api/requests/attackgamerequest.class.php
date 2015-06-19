<?php
namespace imperator\api\requests;
use imperator\Imperator;

class AttackGameRequest extends GameRequest {
	private $units;
	private $to;
	private $from;
	private $move;

	public function __construct($gid, $units, $to, $from, $move) {
		parent::__construct($gid);
		$this->units = (int)$units;
		$this->to = $to;
		$this->from = $from;
		$this->move = (int)$move;
	}

	public function getUnits() {
		return $this->units;
	}

	public function getTo() {
		return $this->to;
	}

	public function getFrom() {
		return $this->from;
	}

	public function getMove() {
		return $this->move;
	}
}