<?php
namespace imperator\mission;

class PlayerMission implements Mission {
	private $mission;
	private $uid = 0;
	private $player;

	public function __construct(MapMission $mission, \imperator\game\Player $player) {
		$this->mission = $mission;
		$this->player = $player;
	}

	public function setUid($uid) {
		$this->uid = $uid;
	}

	public function getUid() {
		return $this->uid;
	}

	public function getPlayer() {
		return $this->player;
	}

	public function getGame() {
		return $this->player->getGame();
	}

	public function getName() {
		return $this->mission->getName();
	}

	public function getId() {
		return $this->mission->getId();
	}

	public function getDescription(\imperator\Language $language) {
		return $this->mission->getDescription($language, $this);
	}

	public function equals(Mission $that) {
		return $this->mission->equals($that);
	}

	public function containsEliminate() {
		return $this->mission->containsEliminate();
	}

	public function hasBeenCompleted() {
		return $this->mission->hasBeenCompleted($this);
	}
}