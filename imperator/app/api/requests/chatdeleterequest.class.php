<?php
namespace imperator\api\requests;
use imperator\Imperator;

class ChatAddRequest extends ChatRequest {
	private $message;

	public function __construct($gid, $message) {
		parent::__construct($gid);
		$this->message = trim(Imperator::stripIllegalCharacters($message));
	}

	public function getMessage() {
		return $this->message;
	}
}