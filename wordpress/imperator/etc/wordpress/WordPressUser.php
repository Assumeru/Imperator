<?php
namespace imperator\outside\wordpress;

class WordPressUser extends \imperator\User {
	private static $current = null;

	public static function getCurrentUser() {
		if(!self::$current) {
			$user = wp_get_current_user();
			$lang = explode('-', get_bloginfo('language'));
			self::$current = new self(
				(int)$user->ID,
				$user->user_login,
				is_user_logged_in(),
				$lang[0],
				$lang[1],
				is_rtl() ? 'rtl' : 'ltr'
			);
		}
		return self::$current;
	}

	public static function getUserById($uid) {
		$user = new \WP_User($uid);
		return new self($uid, $user->user_login, false);
	}

	public static function getUserByHeaders(\imperator\HttpHeaders $headers) {
		return null;
	}

	public function getProfileLink() {
		if(($id = $this->getId()) !== 0) {
			return get_author_posts_url($id, $this->getName());
		}
		return false;
	}

	public function canDeleteChatMessages() {
		return is_super_admin($this->getId());
	}
}