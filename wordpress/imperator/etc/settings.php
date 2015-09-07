<?php
namespace imperator\outside\wordpress;
use imperator\Imperator;

class WordPressSettings extends \imperator\Settings {
	public static $defaultSettings = array(
		'max_chat_message_age' => 86400,
		'min_chat_messages_preserved' => 10,
		'max_finished_game_age' => 86400,
		'inactive_game_time' => 1209600,
		'longpolling_timeout' => 1,
		'longpolling_tries' => 30,
		'websocket_address' => '127.0.0.1',
		'websocket_port' => '8080',
		'websocket_path' => '/websocket'
	);
	private $settings;

	public function __construct() {
		parent::__construct();
		$this->includeWordPress();
		$this->settings = get_option('EE_imperator_settings', static::$defaultSettings);
	}

	public function getDatabaseManager() {
		return \imperator\outside\wordpress\database\WordPressDatabaseManager::getInstance();
	}

	public function getBrandLink() {
		return get_site_url();
	}

	public function getUserClass() {
		return '\\imperator\\outside\\wordpress\\WordPressUser';
	}

	private function includeWordPress() {
		require_once dirname($this->getBasePath()).'/wp-load.php';
		//WordPress is weird, even php removed magic quotes...
		$_GET = stripslashes_deep($_GET);
		$_POST = stripslashes_deep($_POST);
		$_COOKIE = stripslashes_deep($_COOKIE);
		$_SERVER = stripslashes_deep($_SERVER);
	}

	private function getWordPressSetting($key) {
		return $this->settings[$key];
	}

	public function getLongPollingTimeout() {
		return (int)$this->getWordPressSetting('longpolling_timeout');
	}

	public function getMaxLongPollingTries() {
		return (int)$this->getWordPressSetting('longpolling_tries');
	}

	public function getMaxChatMessageAge() {
		return (int)$this->getWordPressSetting('max_chat_message_age');
	}

	public function getMinNumChatMessagesToPreserve() {
		return (int)$this->getWordPressSetting('min_chat_messages_preserved');
	}

	public function getMaxFinishedGameAge() {
		return (int)$this->getWordPressSetting('max_finished_game_age');
	}

	public function getInactiveGameTime() {
		return (int)$this->getWordPressSetting('inactive_game_time');
	}

	public function getWebSocketAddress() {
		return $this->getWordPressSetting('websocket_address');
	}

	public function getWebSocketPort() {
		return $this->getWordPressSetting('websocket_port');
	}

	public function getWebSocketPath() {
		return $this->getWordPressSetting('websocket_path');
	}
}