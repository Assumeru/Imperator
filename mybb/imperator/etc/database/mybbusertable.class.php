<?php
namespace imperator\outside\mybb\database;

class MyBBUserTable extends \imperator\database\OutsideUsersTable {
	protected function register(\imperator\database\DatabaseManager $manager) {
		$manager->registerTable('OUTSIDEUSERS', TABLE_PREFIX.'users', array(
			'USER' => 'uid',
			'USERNAME' => 'username'
		));
	}
}