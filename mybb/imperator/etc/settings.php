<?php
namespace imperator\outside\mybb;
use imperator\Imperator;

class MyBBSettings extends \imperator\Settings {
	public function getDatabaseManager() {
		return \imperator\outside\mybb\database\MyBBDatabaseManager::getInstance();
	}

	public function getBrandLink() {
		global $mybb;
		$this->includeMyBB();
		return $mybb->settings['bburl'];
	}

	public function getUserClass() {
		return '\\imperator\\outside\\mybb\\MyBBUser';
	}

	public function includeMyBB($initOnly = true) {
		global $mybb, $db, $templates, $cache, $plugins, $lang, $date_formats, $time_formats, $maintimer;
		if($initOnly) {
			$path = dirname($this->getBasePath()).'/inc/init.php';
		} else {
			$path = dirname($this->getBasePath()).'/global.php';
		}
		if(!defined('IN_MYBB')) {
			define('IN_MYBB', true);
		}
		require_once $path;
		$db->error_reporting = false;
		Imperator::getShutDownHandler()->resetErrorHandler();
	}

	public function getLongPollingTimeout() {
		return (int)$this->getMyBBSetting('EE_imperator_settings_longpolling_timeout');
	}

	public function getMaxLongPollingTries() {
		return (int)$this->getMyBBSetting('EE_imperator_settings_longpolling_tries');
	}

	public function getMaxChatMessageAge() {
		return (int)$this->getMyBBSetting('EE_imperator_settings_max_chat_message_age');
	}

	public function getMinNumChatMessagesToPreserve() {
		return (int)$this->getMyBBSetting('EE_imperator_settings_min_chat_messages_preserved');
	}

	public function getMaxFinishedGameAge() {
		return (int)$this->getMyBBSetting('EE_imperator_settings_max_finished_game_age');
	}

	public function getInactiveGameTime() {
		return (int)$this->getMyBBSetting('EE_imperator_settings_inactive_game_time');
	}

	public function getWebSocketAddress() {
		return $this->getMyBBSetting('EE_imperator_settings_websocket_address');
	}

	public function getWebSocketPort() {
		return $this->getMyBBSetting('EE_imperator_settings_websocket_port');
	}

	public function getWebSocketPath() {
		return $this->getMyBBSetting('EE_imperator_settings_websocket_path');
	}

	public function getLogPath() {
		return $this->getMyBBSetting('EE_imperator_settings_log_path');
	}

	public function getImageURL() {
		return $this->getMyBBSetting('EE_imperator_settings_img_url');
	}

	public function getStylesheetURL() {
		return $this->getMyBBSetting('EE_imperator_settings_css_url');
	}

	public function getJavascriptURL() {
		return $this->getMyBBSetting('EE_imperator_settings_js_url');
	}

	public function getLanguagePath() {
		return $this->getMyBBSetting('EE_imperator_settings_i18n_path');
	}

	private function getMyBBSetting($key) {
		global $mybb;
		$this->includeMyBB();
		return $mybb->settings[$key];
	}
}