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
	global $db;
	$dbm = \imperator\Imperator::getDatabaseManager();
	$dbm->dropTables();
	$dbm->createTables();
	$db->insert_query('settinggroups', array(
		'name' => 'EE_imperator_settings',
		'title' => 'Imperator Settings',
		'description' => 'Settings for Imperator.'
	));
	$gid = $db->insert_id();
	$db->insert_query_multiple('settings', array(
		array(
			'name' => 'EE_imperator_settings_max_chat_message_age',
			'title' => 'Max. chat message age',
			'description' => 'The time (in seconds) until a chat message is deleted.',
			'optionscode' => 'text',
			'value' => 86400,
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_min_chat_messages_preserved',
			'title' => 'Min. chat messages preserved',
			'description' => 'The minimum number of chat messages to preserve.',
			'optionscode' => 'text',
			'value' => 10,
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_max_finished_game_age',
			'title' => 'Max. finished game age',
			'description' => 'The time (in seconds) until a finished game is deleted.',
			'optionscode' => 'text',
			'value' => 86400,
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_inactive_game_time',
			'title' => 'Max. inactive game time',
			'description' => 'The time (in seconds) until an inactive game is deleted.',
			'optionscode' => 'text',
			'value' => 1209600,
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_longpolling_timeout',
			'title' => 'Long polling timeout',
			'description' => 'The time (in seconds) between each update check.',
			'optionscode' => 'text',
			'value' => 1,
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_longpolling_tries',
			'title' => 'Max. long polling tries',
			'description' => 'The maximum number of long polling tries before timing out. Set to 0 for infinite tries.',
			'optionscode' => 'text',
			'value' => 30,
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_websocket_address',
			'title' => 'WebSocket address',
			'description' => 'The address websockets connect to.',
			'optionscode' => 'text',
			'value' => '127.0.0.1',
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_websocket_port',
			'title' => 'WebSocket port',
			'description' => 'The port websockets connect to.',
			'optionscode' => 'text',
			'value' => '8080',
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_websocket_path',
			'title' => 'WebSocket path',
			'description' => 'The path websockets connect to.',
			'optionscode' => 'text',
			'value' => '/websocket',
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_log_path',
			'title' => 'Log path',
			'description' => 'The path error.log and output.log are written to.',
			'optionscode' => 'text',
			'value' => \imperator\Imperator::getSettings()->getBasePath().'/var/log/',
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_img_url',
			'title' => 'Image url',
			'description' => 'The url to get images from. Needs to contain %1$s.',
			'optionscode' => 'text',
			'value' => \imperator\Imperator::getSettings()->getBaseURL().'/img/%1$s',
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_js_url',
			'title' => 'Javascript url',
			'description' => 'The url to get javascript from. Needs to contain %1$s.',
			'optionscode' => 'text',
			'value' => \imperator\Imperator::getSettings()->getBaseURL().'/js/%1$s',
			'gid' => $gid
		),
		array(
			'name' => 'EE_imperator_settings_css_url',
			'title' => 'CSS url',
			'description' => 'The url to get stylesheets from. Needs to contain %1$s.',
			'optionscode' => 'text',
			'value' => \imperator\Imperator::getSettings()->getBaseURL().'/css/%1$s',
			'gid' => $gid
		)
	));
	rebuild_settings();
}

function EE_imperator_is_installed() {
	$db = \imperator\Imperator::getDatabaseManager();
	return $db->exists($db->preparedStatement('SHOW TABLES LIKE %s', $db->parseStatement('@GAMES')));
}

function EE_imperator_uninstall() {
	global $db;
	\imperator\Imperator::getDatabaseManager()->dropTables();
	$query = $db->simple_select('settings','gid','name = "EE_imperator_settings"');
	if($result = $db->fetch_array($query)) {
		$gid = $result['gid'];
		$db->delete_query('settings','gid = '.$gid);
		$db->delete_query('settinggroups','name = "EE_imperator_settings"');
		rebuild_settings();
	}
}

function EE_imperator_activate() {
	global $db;
	$db->update_query('tasks', array('enabled' => 1), 'file = "EE_imperator"');
}

function EE_imperator_deactivate() {
	global $db;
	$db->update_query('tasks', array('enabled' => 0), 'file = "EE_imperator"');
}