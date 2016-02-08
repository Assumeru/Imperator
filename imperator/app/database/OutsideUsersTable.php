<?php
namespace imperator\database;
use imperator\Imperator;

abstract class OutsideUsersTable extends Table {
	protected function register(DatabaseManager $manager) {
		$manager->registerTable('OUTSIDEUSERS', 'imperator_outsideusers', array(
			'USER' => 'uid',
			'USERNAME' => 'username'
		));
	}

	public function create() {
		Imperator::getLogger()->w('Outside table should already exist.');
	}

	public function drop() {
		Imperator::getLogger()->w('Outside table should not be dropped.');
	}
}