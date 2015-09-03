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
		//WordPress is weird, even php removed magic quotes...
		$_GET = stripslashes_deep($_GET);
		$_POST = stripslashes_deep($_POST);
		$_COOKIE = stripslashes_deep($_COOKIE);
		$_SERVER = stripslashes_deep($_SERVER);
	}
}