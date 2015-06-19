<?php
namespace imperator\api\requests;
use imperator\Imperator;

class ChatRequest extends \imperator\api\Request {
	private $gid;

	public static function buildRequest($params) {
		if(isset($params['type']) && isset($params['gid']) && is_numeric($params['gid'])) {
			if($params['type'] == 'delete' && isset($params['time']) && is_numeric($params['time']) && isset($params['uid']) && is_numeric($params['uid'])) {
				return new ChatDeleteRequest($params['gid'], $params['time'], $params['uid']);
			} else if($params['add'] == 'game' && isset($params['message'])) {
				return new ChatAddRequest($params['gid'], $params['message']);
			}
		}
		return new InvalidRequest($params);
	}

	public function __construct($gid) {
		$this->gid = (int)$gid;
	}

	public function getGid() {
		return $this->gid;
	}
}