<?php
namespace imperator;

abstract class User implements Member {
	private $id;
	private $name;
	private $language;
	private $loggedIn;
	private $score;
	private $wins;
	private $losses;

	public static function getCurrentUser() {
		return new User();
	}

	public static function getUserById($uid) {
		return new User($uid);
	}

	public static function getUserByHeaders(\imperator\HttpHeaders $headers) {
		return null;
	}

	public function __construct($id = 0, $name = 'Guest', $loggedIn = false, $lang = 'en', $locale = 'us', $direction = 'ltr') {
		$this->id = $id;
		$this->loggedIn = $loggedIn;
		$this->name = $name;
		$this->language = Language::getInstance($lang, $locale, $direction);
	}

	public function getName() {
		return $this->name;
	}

	public function getLanguage() {
		return $this->language;
	}

	public function getId() {
		return $this->id;
	}

	public function isLoggedIn() {
		return $this->loggedIn;
	}

	public function equals(Member $that) {
		return $this->id == $that->getId();
	}

	public function getProfileLink() {
		return false;
	}

	public function setScore($score) {
		$this->score = $score;
	}

	public function getScore() {
		return $this->score;
	}

	public function setWins($wins) {
		$this->wins = $wins;
	}

	public function getWins() {
		return $this->wins;
	}

	public function setLosses($losses) {
		$this->losses = $losses;
	}

	public function getLosses() {
		return $this->losses;
	}

	public function canDeleteChatMessages() {
		return false;
	}
}