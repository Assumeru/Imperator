<?php
namespace imperator\api\requests;
use imperator\Imperator;

class InvalidRequest extends \imperator\api\Request {
	private $mode = null;
	private $type = null;

	public function __construct(array $params) {
		if(isset($params['mode'])) {
			$this->mode = $params['mode'];
		}
		if(isset($params['type'])) {
			$this->type = $params['type'];
		}
	}

	public function getMode() {
		return $this->mode;
	}

	public function getType() {
		return $this->type;
	}

	public function handle(\imperator\User $user) {
		throw new \imperator\exceptions\InvalidRequestException('Bad request.');
	}
}