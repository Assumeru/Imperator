<?php
namespace imperator\mission;
use imperator\Imperator;

class RivalryMission extends Mission {
	private $user;

	public function __construct($id, $uid = null) {
		parent::__construct($id, 'Rivalry', 'Two win this game you will have to conquer the last of an opponent\'s territories.', array(
			new EliminateCondition($uid)
		));
		$this->setUid($uid);
	}

	public function getDescription(\imperator\Language $language) {
		if($this->user === null) {
			return parent::getDescription($language);
		}
		return $language->translate('Two win this game you will have to conquer the last of %1$s\'s territories.', $this->user->getName());
	}

	public function setUid($uid) {
		$class = Imperator::getSettings()->getUserClass();
		$this->user = $class::getUserById($uid);
		parent::setUid($uid);
	}
}