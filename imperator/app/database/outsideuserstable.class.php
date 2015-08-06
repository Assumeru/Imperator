<?php
namespace imperator\database;

abstract class OutsideUsersTable extends Table {
	protected function register(DatabaseManager $manager) {
		$manager->registerTable('OUTSIDEUSERS', 'imperator_outsideusers', array(
			'USER' => 'uid',
			'USERNAME' => 'username'
		));
	}
}