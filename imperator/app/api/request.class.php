<?php
namespace imperator\api;

class Request {
	const MODE_UPDATE = 'update';
	const MODE_GAME = 'game';
	const MODE_CHAT = 'chat';
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
			if($mode == static::MODE_UPDATE) {
				return $this->validateUpdate();
			} else if($mode == static::MODE_GAME) {
				return $this->validateGame();
			} else if($mode == static::MODE_CHAT) {
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
		if(isset($this->data['type'])) {
			$out = isset($this->data['gid']) && is_numeric($this->data['gid']);
			$type = $this->data['type'];
			if($type == 'delete') {
				return $out && isset($this->data['uid']) && is_numeric($this->data['uid'])
					&& isset($this->data['time']) && is_numeric($this->data['time']);
			} else if($type == 'add') {
				return $out && isset($this->data['message']);
			}
		}
		return false;
	}

	public function isValid() {
		return $this->valid;
	}

	public function getMode() {
		return $this->data['mode'];
	}

	public function getType() {
		return $this->data['type'];
	}

	public function getUser() {
		return $this->user;
	}

	public function getGid() {
		return (int)$this->data['gid'];
	}

	public function getData() {
		return $this->data;
	}

	public function getTime() {
		return (int)$this->data['time'];
	}
}