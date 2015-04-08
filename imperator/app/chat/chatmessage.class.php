<?php
namespace imperator\chat;

class ChatMessage {
	private $gid;
	private $time;
	private $user;
	private $message;

	public function __construct($gid, $time, \imperator\User $user, $message) {
		$this->gid = $gid;
		$this->time = $time;
		$this->user = $user;
		$this->message = $message;
	}

	public function getMessage() {
		return $this->message;
	}

	public function getUser() {
		return $this->user;
	}

	public function getTime() {
		return $this->time;
	}
}