<?php
namespace imperator\outside\wordpress\database;

class WordPressUserTable extends \imperator\database\OutsideUsersTable {
	protected function register(\imperator\database\DatabaseManager $manager) {
		global $wpdb;
		$manager->registerTable('OUTSIDEUSERS', $wpdb->prefix.'users', array(
			'USER' => 'ID',
			'USERNAME' => 'user_login'
		));
	}
}