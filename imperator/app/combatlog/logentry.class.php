<?php
namespace imperator\combatlog;

abstract class LogEntry {
	const CONQUERED_TERRITORY = 0;
	const ATTACKED_TERRITORY = 1;
	const ENDED_TURN = 2;
	const FORFEITED = 3;
	const PLAYED_CARDS = 4;

	private $user;
	private $time;

	public function __construct($time, \imperator\game\Player $user) {
		$this->user = $user;
		$this->time = $time;
	}

	protected function getUser() {
		return $this->user;
	}

	protected function getTime() {
		return $this->time;
	}

	public function getMessageArray(\imperator\Language $language) {
		throw new \imperator\exceptions\ImperatorException('Cannot call getMessage on an abstract log entry.');
	}

	public function save() {
		//TODO
	}
}