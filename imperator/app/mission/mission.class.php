<?php
namespace imperator\mission;

class Mission {
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

	/**
	 * Checks if a user has completed this mission.
	 * 
	 * @param \imperator\game\Player $user The user to check for
	 * @return bool True if all of this mission's conditions are met
	 */
	public function hasBeenCompleted(\imperator\game\Player $user) {
		foreach($this->conditions as $condition) {
			if(!$condition->isFulfilled($user)) {
				return false;
			}
		}
		return true;
	}

	public function getName() {
		return $this->name;
	}

	public function setUid($uid) {
		foreach($this->conditions as $condition) {
			if($condition instanceof UidCondition) {
				$condition->setUid($uid);
			}
		}
	}

	public function getUid() {
		foreach($this->conditions as $condition) {
			if($condition instanceof UidCondition) {
				return $condition->getUid();
			}
		}
		return 0;
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
		return $this->id == $that->id;
	}
}