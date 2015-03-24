<?php
namespace imperator\outside\mybb;
use imperator\Imperator;

class MyBBUser extends \imperator\User {
	private static $current = null;

	public static function getCurrentUser() {
		if(!self::$current) {
			global $mybb;
			Imperator::getSettings()->includeMyBB(false);
			self::$current = new MyBBUser($mybb->user['uid'], $mybb->user['username'], $mybb->user['uid'] !== 0);
		}
		return self::$current;
	}

	public function getProfileLink() {
		global $mybb;
		if($id = $this->getId() !== 0) {
			return $mybb->settings['bburl'].'/'.get_profile_link($id);
		}
		return false;
	}
}