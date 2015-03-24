<?php
namespace imperator\outside\mybb;

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
	}

	public function getAutoLoaderClass() {
		require_once $this->getBasePath().'/etc/mybbautoloader.class.php';
		return '\\imperator\\outside\\mybb\\MyBBAutoLoader';
	}
}