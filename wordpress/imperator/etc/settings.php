<?php
namespace imperator\outside\wordpress;

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
		static::$defaultSettings['img_url'] = $this->getBaseURL().'/img/%1$s';
		static::$defaultSettings['css_url'] = $this->getBaseURL().'/css/%1$s';
		static::$defaultSettings['js_url'] = $this->getBaseURL().'/js/%1$s';
		static::$defaultSettings['log_path'] = $this->getBasePath().'/var/log/';
		static::$defaultSettings['i18n_path'] = $this->getBasePath().'/etc/i18n/';
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

	public function getLogPath() {
		return $this->getWordPressSetting('log_path');
	}

	public function getImageURL() {
		return $this->getWordPressSetting('img_url');
	}

	public function getStylesheetURL() {
		return $this->getWordPressSetting('css_url');
	}

	public function getJavascriptURL() {
		return $this->getWordPressSetting('js_url');
	}

	public function getLanguagePath() {
		return $this->getWordPressSetting('i18n_path');
	}
}