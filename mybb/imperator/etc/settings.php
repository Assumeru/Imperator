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
		return (int)$this->getMyBBSetting('EE_imperator_settings_longpolling_timeout', parent::getLongPollingTimeout());
	}

	public function getMaxLongPollingTries() {
		return (int)$this->getMyBBSetting('EE_imperator_settings_longpolling_tries', parent::getMaxLongPollingTries());
	}

	public function getMaxChatMessageAge() {
		return (int)$this->getMyBBSetting('EE_imperator_settings_max_chat_message_age', parent::getMaxChatMessageAge());
	}

	public function getMinNumChatMessagesToPreserve() {
		return (int)$this->getMyBBSetting('EE_imperator_settings_min_chat_messages_preserved', parent::getMinNumChatMessagesToPreserve());
	}

	public function getMaxFinishedGameAge() {
		return (int)$this->getMyBBSetting('EE_imperator_settings_max_finished_game_age', parent::getMaxFinishedGameAge());
	}

	public function getInactiveGameTime() {
		return (int)$this->getMyBBSetting('EE_imperator_settings_inactive_game_time', parent::getInactiveGameTime());
	}

	public function getWebSocketAddress() {
		return $this->getMyBBSetting('EE_imperator_settings_websocket_address', parent::getWebSocketAddress());
	}

	public function getWebSocketPort() {
		return $this->getMyBBSetting('EE_imperator_settings_websocket_port', parent::getWebSocketPort());
	}

	public function getWebSocketPath() {
		return $this->getMyBBSetting('EE_imperator_settings_websocket_path', parent::getWebSocketPath());
	}

	public function getLogPath() {
		return $this->getMyBBSetting('EE_imperator_settings_log_path', parent::getLogPath());
	}

	public function getImageURL() {
		return $this->getMyBBSetting('EE_imperator_settings_img_url', parent::getImageURL());
	}

	public function getStylesheetURL() {
		return $this->getMyBBSetting('EE_imperator_settings_css_url', parent::getStylesheetURL());
	}

	public function getJavascriptURL() {
		return $this->getMyBBSetting('EE_imperator_settings_js_url', parent::getJavascriptURL());
	}

	public function getLanguagePath() {
		return $this->getMyBBSetting('EE_imperator_settings_i18n_path', parent::getLanguagePath());
	}

	private function getMyBBSetting($key, $fallback) {
		global $mybb;
		$this->includeMyBB();
		if(isset($mybb->settings[$key])) {
			return $mybb->settings[$key];
		}
		return $fallback;
	}
}