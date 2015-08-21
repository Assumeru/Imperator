<?php
namespace imperator;
use imperator\Imperator;

class Cron {
	private $settings;
	private $db;

	public function __construct() {
		$this->settings = Imperator::getSettings();
		$this->db = Imperator::getDatabaseManager();
	}

	public function cleanGames() {
		return $this->db->getGamesTable()->deleteOldGames(
			time() - $this->settings->getMaxFinishedGameAge(),
			time() - $this->settings->getInactiveGameTime()
		);
	}

	public function cleanChat() {
		return $this->db->getChatTable()->deleteOldMessages(
			time() - $this->settings->getMaxChatMessageAge(),
			$this->settings->getMinNumChatMessagesToPreserve()
		);
	}
}