<?php
namespace imperator\api\requests;
use imperator\Imperator;

class ChatDeleteRequest extends ChatRequest {
	private $time;
	private $uid;

	public function __construct($gid, $time, $uid) {
		parent::__construct($gid);
		$this->time = (int)$time;
		$this->uid = (int)$uid;
	}

	public function getTime() {
		return $this->time;
	}

	public function getUid() {
		return $this->uid;
	}
}