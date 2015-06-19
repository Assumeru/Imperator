<?php
namespace imperator\api;
use imperator\Imperator;

class Request {
	public static function buildRequest(array $params) {
		if(isset($params['mode'])) {
			if($params['mode'] == 'update') {
				return requests\UpdateRequest::buildRequest($params);
			} else if($params['mode'] == 'game') {
				return requests\GameRequest::buildRequest($params);
			} else if($params['mode'] == 'chat') {
				return requests\ChatRequest::buildRequest($params);
			}
		}
		return new requests\InvalidRequest($params);
	}
}