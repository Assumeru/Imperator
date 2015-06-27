<?php
namespace imperator\page;
use imperator\Imperator;

abstract class Page {
	const URL = '';
	private static $urls = null;

	private static function loadAllPages() {
		$files = glob(Imperator::getSettings()->getBasePath().'/app/page/*.class.php');
		foreach($files as $file) {
			require_once $file;
		}
		self::$urls = array();
		foreach(get_declared_classes() as $class) {
			if(is_subclass_of($class, '\\imperator\\page\\Page') && $class::URL !== '') {
				$page = substr($class, strrpos($class, '\\')+1);
				self::$urls[$class::URL] = $page;
			}
		}
	}

	public static function getInstanceByURL($url) {
		if(!self::$urls) {
			self::loadAllPages();
		}
		if(!isset(self::$urls[$url])) {
			return null;
		}
		return self::$urls[$url];
	}

	/**
	 * Checks if a user is allowed to view this page.
	 * 
	 * @param User $user The user to check
	 * @return boolean True if the user can view this page
	 */
	public function canBeUsedBy(\imperator\User $user) {
		return false;
	}

	/**
	 * Renders the page.
	 * 
	 * @param User $user The user viewing the page
	 */
	public function render(\imperator\User $user) {
		throw new \imperator\exceptions\ImperatorException('Cannot render abstract page.');
	}

	public static function getURL() {
		if(static::URL) {
			return Imperator::getSettings()->getBaseURL().'/?page='.static::URL;
		} else {
			return Imperator::getSettings()->getBaseURL();
		}
	}
}