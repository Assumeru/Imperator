<?php
namespace imperator\database;

abstract class Table {
	private $manager;

	public function __construct(DatabaseManager $manager) {
		$this->manager = $manager;
		$this->register($manager);
	}

	protected function getManager() {
		return $this->manager;
	}

	protected function register(DatabaseManager $manager) {
		throw new \imperator\exceptions\DatabaseException('Cannot call register on abstract table.');
	}

	public function create() {
		throw new \imperator\exceptions\DatabaseException('Cannot call create on abstract table.');
	}
}