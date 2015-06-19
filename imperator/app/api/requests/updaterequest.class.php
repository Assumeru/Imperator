<?php
namespace imperator\api\requests;
use imperator\Imperator;

class UpdateRequest extends \imperator\api\Request {
	private $gid;
	private $time;

	public static function buildRequest($params) {
		if(isset($params['type']) && isset($params['gid']) && is_numeric($params['gid']) && isset($params['time']) && is_numeric($params['time'])) {
			if($params['type'] == 'chat') {
				return new ChatUpdateRequest($params['gid'], $params['time']);
			} else if($params['type'] == 'game') {
				return new GameUpdateRequest($params['gid'], $params['time']);
			} else if($params['type'] == 'pregame') {
				return new PreGameUpdateRequest($params['gid'], $params['time']);
			}
		}
		return new InvalidRequest($params);
	}

	public function __construct($gid, $time) {
		$this->gid = (int)$gid;
		$this->time = (int)$time;
	}

	public function getGid() {
		return $this->gid;
	}

	public function getTime() {
		return $this->time;
	}
}