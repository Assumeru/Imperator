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
		Imperator::getLogger()->log(\imperator\Logger::LEVEL_WARNING, 'Outside table should already exist.');
	}

	public function drop() {
		Imperator::getLogger()->log(\imperator\Logger::LEVEL_WARNING, 'Outside table should not be dropped.');
	}
}