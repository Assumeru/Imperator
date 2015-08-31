<?php
if(!defined('IN_MYBB')) {
	die;
}

//Edit this if you're using a different install path
require_once MYBB_ROOT.'/imperator/app/imperator.class.php';

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
	$db = \imperator\Imperator::getDatabaseManager();
	return $db->exists($db->preparedStatement('SHOW TABLES LIKE %s', $db->parseStatement('@GAMES')));
}

function EE_imperator_uninstall() {
	\imperator\Imperator::getDatabaseManager()->dropTables();
}

function EE_imperator_activate() {
	global $db;
	$db->update_query('tasks', array('enabled' => 1), 'file = "EE_imperator"');
}

function EE_imperator_deactivate() {
	global $db;
	$db->update_query('tasks', array('enabled' => 0), 'file = "EE_imperator"');
}