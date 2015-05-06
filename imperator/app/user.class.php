<?php
namespace imperator;

abstract class User {
	const STATE_PLAYING = 0;
	const STATE_GAME_OVER = 1;
	const STATE_DESTROYED_RIVAL = 2;
	const STATE_VICTORIOUS = 3;

	private $id;
	private $name;
	private $language;
	private $loggedIn;
	private $color;
	private $mission;
	private $state;
	private $score;
	private $wins;
	private $losses;
	private $autoroll;

	public static function getCurrentUser() {
		return new User();
	}

	public static function getUserById($uid) {
		return new User($uid);
	}

	public function __construct($id = 0, $name = 'Guest', $loggedIn = false, $lang = 'en', $locale = 'us') {
		$this->id = $id;
		$this->loggedIn = $loggedIn;
		$this->name = $name;
		$this->language = Language::getInstance($lang, $locale);
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

	public function equals(User $that) {
		return $this->id == $that->id;
	}

	public function getProfileLink() {
		return false;
	}

	public function setColor($color) {
		$this->color = $color;
	}

	public function getColor() {
		return $this->color;
	}

	public function setState($state) {
		$this->state = $state;
	}

	public function getState() {
		return $this->state;
	}

	public function setMission(\imperator\mission\Mission $mission) {
		$this->mission = $mission;
	}

	public function getMission() {
		return $this->mission;
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

	public function setAutoRoll($autoroll) {
		$this->autoroll = $autoroll;
	}

	public function getAutoRoll() {
		return $this->autoroll;
	}
}