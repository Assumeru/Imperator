<?php
namespace imperator\database;

abstract class Table {
	private $manager;

	public function __construct(DatabaseManager $manager) {
		$this->manager = $manager;
	}

	protected function getManager() {
		return $this->manager;
	}
}