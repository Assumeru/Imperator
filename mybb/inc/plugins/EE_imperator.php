<?php
if(!defined('IN_MYBB')) {
	die;
}

//Edit this if you're using a different install path
define('EE_MYBB_IMPERATOR_CLASS_PATH', MYBB_ROOT.'/imperator/app/imperator.class.php');

function EE_imperator_info() {
	return array(
		'name' => 'Imperator',
		'author' => 'Evil Eye',
		'version' => '1.0',
		'compatibility' => '18*'
	);
}

function EE_imperator_install() {
	$db = \imperator\Imperator::getDatabaseManager();
	$db->dropTables();
	$db->createTables();
}

function EE_imperator_is_installed() {
	global $db;
	$db->table_exists(\imperator\Imperator::getDatabaseManager()->parseStatement('@GAMES'));
}

function EE_imperator_uninstall() {
	\imperator\Imperator::getDatabaseManager()->dropTables();
}