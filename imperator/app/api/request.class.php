<?php
namespace imperator\api;

class Request {
	private $user;
	private $data;
	private $valid = false;

	public function __construct(array $params, \imperator\User $user) {
		$this->user = $user;
		$this->data = $params;
		$this->valid = $this->validateRequest();
	}

	private function validateRequest() {
		if(isset($this->data['mode'])) {
			$mode = $this->data['mode'];
			if($mode == 'update') {
				return $this->validateUpdate();
			} else if($mode == 'game') {
				return $this->validateGame();
			} else if($mode == 'chat') {
				return $this->validateChat();
			}
		}
		return false;
	}

	private function validateUpdate() {
		return isset($this->data['type']) && ($this->data['type'] == 'chat' || $this->data['type'] == 'game')
			&& isset($this->data['gid']) && is_numeric($this->data['gid']);
	}

	private function validateGame() {
		return false;
	}

	private function validateChat() {
		return false;
	}

	public function isValid() {
		return $this->valid;
	}
}