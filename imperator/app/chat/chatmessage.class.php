<?php
namespace imperator\chat;
use imperator\Imperator;

class ChatMessage {
	private $gid;
	private $time;
	private $user;
	private $message;

	public function __construct($gid, $time, \imperator\Member $user, $message) {
		$this->gid = $gid;
		$this->time = $time;
		$this->user = $user;
		$this->message = $message;
	}

	public function getGid() {
		return $this->gid;
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

	/**
	 * Inserts this message into the database.
	 */
	public function insert() {
		Imperator::getDatabaseManager()->getChatTable()->insertMessage($this);
	}

	/**
	 * Deletes this message from the database.
	 */
	public function delete() {
		Imperator::getDatabaseManager()->getChatTable()->deleteMessage($this);
	}
}