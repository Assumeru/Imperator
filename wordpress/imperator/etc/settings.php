<?php
namespace imperator\outside\wordpress;
use imperator\Imperator;

class WordPressSettings extends \imperator\Settings {
	public function getDatabaseManager() {
		return \imperator\outside\wordpress\database\WordPressDatabaseManager::getInstance();
	}

	public function getBrandLink() {
		return get_site_url();
	}

	public function getUserClass() {
		return '\\imperator\\outside\\wordpress\\WordPressUser';
	}

	public function includeWordPress() {
		require_once dirname($this->getBasePath()).'/wp-load.php';
	}
}