<?php
namespace imperator\mission;

class Mission {
	private $id;
	private $name;
	private $description;
	private $conditions;

	public function __construct($id, $name, $description, array $conditions) {
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->conditions = $conditions;
	}

	/**
	 * Checks if a user has completed this mission.
	 * 
	 * @param Game $game The game to check in
	 * @param User $user The user to check for
	 * @return bool True if all of this mission's conditions are met
	 */
	public function hasBeenCompleted(\imperator\Game $game, \imperator\User $user) {
		foreach($this->conditions as $condition) {
			if(!$condition->isFulfilled($game, $user)) {
				return false;
			}
		}
		return true;
	}

	public function setUid($uid) {
		
	}

	public function getUid() {
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
}