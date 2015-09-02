<?php
if(!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}
//Edit this if you're using a different install path
require_once ABSPATH.'/imperator/app/imperator.class.php';

\imperator\Imperator::getDatabaseManager()->dropTables();