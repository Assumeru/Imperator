<?php
namespace imperator\page\form;

class PreGameControlForm extends Form {
	public function hasBeenSubmitted() {
		return $this->startHasBeenSubmitted()
			|| $this->disbandHasBeenSubmitted()
			|| $this->leaveHasBeenSubmitted();
	}

	public function startHasBeenSubmitted() {
		return $this->hasPost('startgame');
	}

	public function disbandHasBeenSubmitted() {
		return $this->hasPost('disband');
	}

	public function leaveHasBeenSubmitted() {
		return $this->hasPost('leavegame');
	}
}