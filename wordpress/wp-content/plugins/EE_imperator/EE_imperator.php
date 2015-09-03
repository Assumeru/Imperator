<?php
/**
 * Plugin Name: Imperator
 * Version: 1.0
 * Author: Evil Eye
 * Text Domain: EE_imperator
 * Domain Path: /i18n
 */
//Edit this if you're using a different install path
require_once ABSPATH.'/imperator/app/imperator.class.php';

final class EE_Imperator {
	private static $initialised = false;

	public static function init() {
		if(!self::$initialised) {
			self::$initialised = true;
			new self();
		}
	}

	private function __construct() {
		$this->setUpHooks();
	}

	private function setUpHooks() {
		register_activation_hook(__FILE__, array($this, 'install'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));
		add_action('EE_imperator_run_cron', array($this, 'runCron'));
	}

	public function install() {
		wp_schedule_event(time(), 'daily', 'EE_imperator_run_cron');
		$db = \imperator\Imperator::getDatabaseManager();
		$db->dropTables();
		$db->createTables();
	}

	public function runCron() {
		$cron = new \imperator\Cron();
		$numChats = $cron->cleanChat();
		$numGames = $cron->cleanGames();
		\imperator\Imperator::getLogger()->log(\imperator\Logger::LEVEL_DEBUG, 'EE_imperator cleanup task completed: '.$numChats.' chat messages and '.$numGames.' games deleted.');
	}

	public function deactivate() {
		wp_clear_scheduled_hook('EE_imperator_run_cron');
	}
}

EE_Imperator::init();