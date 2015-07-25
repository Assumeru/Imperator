<?php
namespace imperator\combatlog;
use imperator\Imperator;

abstract class LogEntry {
	private $user;
	private $time;

	public function __construct($time, \imperator\game\Player $user) {
		$this->user = $user;
		$this->time = $time;
	}

	public function getUser() {
		return $this->user;
	}

	public function getTime() {
		return $this->time;
	}

	public function getMessage(\imperator\Language $language) {
		throw new \imperator\exceptions\ImperatorException('Cannot call getMessage on an abstract log entry.');
	}

	public function save() {
		Imperator::getDatabaseManager()->getTable('CombatLog')->saveLogEntry($this);
	}
}