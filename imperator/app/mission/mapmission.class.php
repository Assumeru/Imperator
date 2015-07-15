<?php
namespace imperator\mission;

class MapMission implements Mission {
	private $id;
	private $name;
	private $description;
	private $conditions;
	private $fallback;

	public function __construct($id, $name, $description, array $conditions) {
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->conditions = $conditions;
	}

	public function setFallback($fallback) {
		$this->fallback = $fallback;
	}

	public function getFallback() {
		return $this->fallback;
	}

	public function hasBeenCompleted(PlayerMission $mission) {
		foreach($this->conditions as $condition) {
			if(!$condition->isFulfilled($mission)) {
				return false;
			}
		}
		return true;
	}

	public function getName() {
		return $this->name;
	}

	public function getId() {
		return $this->id;
	}

	public function containsEliminate() {
		foreach($this->conditions as $condition) {
			if($condition instanceof EliminateCondition) {
				return true;
			}
		}
		return false;
	}

	public function getDescription(\imperator\Language $language) {
		return $language->translate($this->description);
	}

	public function equals(Mission $that) {
		return $this->id == $that->getId();
	}
}