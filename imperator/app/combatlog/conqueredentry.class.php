<?php
namespace imperator\combatlog;

class ConqueredEntry extends LogEntry {
	private $territory;

	public function __construct($time, \imperator\game\Player $user, \imperator\map\Territory $territory) {
		parent::__construct($time, $user);
		$this->territory = $territory;
	}

	protected function getTerritory() {
		return $this->territory;
	}
}