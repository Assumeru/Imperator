<?php
namespace imperator\api;
use imperator\Imperator;

class Request {
	const MODE_UPDATE = 'update';
	const MODE_GAME = 'game';
	const MODE_CHAT = 'chat';
	private $data;
	private $valid = false;

	public function __construct(array $params) {
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
		return isset($this->data['type']) && ($this->data['type'] == 'chat' || $this->data['type'] == 'game' || $this->data['type'] == 'pregame')
			&& isset($this->data['gid']) && is_numeric($this->data['gid'])
			&& isset($this->data['time']) && is_numeric($this->data['time']);
	}

	private function validateGame() {
		if(isset($this->data['gid']) && is_numeric($this->data['gid']) && isset($this->data['type'])) {
			return $this->data['type'] == 'forfeit'
				|| $this->data['type'] == 'fortify'
				|| $this->data['type'] == 'start-move'
				|| $this->data['type'] == 'end-turn';
		}
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

	public function getGid() {
		return (int)$this->data['gid'];
	}

	public function getUid() {
		return (int)$this->data['uid'];
	}

	public function getTime() {
		return (int)$this->data['time'];
	}

	public function getMessage() {
		return trim(Imperator::stripIllegalCharacters($this->data['message']));
	}

	public function getCard() {
		if(isset($this->data['card']) && is_numeric($this->data['card']) && \imperator\game\Cards::isCard($this->data['card'])) {
			return (int)$this->data['card'];
		}
		return \imperator\game\Cards::CARD_NONE;
	}
}