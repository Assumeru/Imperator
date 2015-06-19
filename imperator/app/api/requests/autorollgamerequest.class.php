<?php
namespace imperator\api\requests;
use imperator\Imperator;

class AutoRollRequest extends GameRequest {
	private $autoroll;

	public function __construct($gid, $autoroll) {
		parent::__construct($gid);
		if($autoroll == 'false') {
			$this->autoroll = false;
		} else {
			$this->autoroll = (bool)$autoroll;
		}
	}

	public function getAutoRoll() {
		return $this->autoroll;
	}
}