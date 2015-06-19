<?php
namespace imperator\api\requests;
use imperator\Imperator;

class MoveGameRequest extends GameRequest {
	private $to;
	private $from;

	public function __construct($gid, $to, $from) {
		parent::__construct($gid);
		$this->to = $to;
		$this->from = $from;
	}

	public function getTo() {
		return $this->to;
	}

	public function getFrom() {
		return $this->from;
	}
}