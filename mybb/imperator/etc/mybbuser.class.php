<?php
namespace imperator\outside\mybb;
use imperator\Imperator;

class MyBBUser extends \imperator\User {
	private static $current = null;

	public static function getCurrentUser() {
		if(!self::$current) {
			global $mybb, $lang;
			Imperator::getSettings()->includeMyBB(false);
			self::$current = new self(
				(int)$mybb->user['uid'],
				$mybb->user['username'],
				$mybb->user['uid'] != 0,
				$lang->settings['htmllang'],
				null,
				$lang->settings['rtl'] ? 'rtl' : 'ltr'
			);
		}
		return self::$current;
	}

	public static function getUserById($uid) {
		Imperator::getSettings()->includeMyBB(false);
		$user = get_user($uid);
		return new self($uid, $user['username'], false);
	}

	public function getProfileLink() {
		global $mybb;
		if(($id = $this->getId()) !== 0) {
			return $mybb->settings['bburl'].'/'.get_profile_link($id);
		}
		return false;
	}

	public function canDeleteChatMessages() {
		Imperator::getSettings()->includeMyBB();
		return is_super_admin($this->getId());
	}
}