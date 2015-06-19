<?php
namespace imperator\api\requests;
use imperator\Imperator;

class DefendGameRequest extends GameRequest {
	private $units;
	private $to;
	private $from;

	public function __construct($gid, $units, $to, $from) {
		parent::__construct($gid);
		$this->units = (int)$units;
		$this->to = $to;
		$this->from = $from;
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
}