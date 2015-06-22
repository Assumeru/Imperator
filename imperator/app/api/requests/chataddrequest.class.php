<?php
namespace imperator\api\requests;
use imperator\Imperator;

class ChatAddRequest extends ChatRequest {
	private $message;

	public function __construct($gid, $message) {
		parent::__construct($gid);
		$this->message = trim(Imperator::stripIllegalCharacters($message));
	}

	public function getType() {
		return 'add';
	}

	protected function getMessage() {
		return $this->message;
	}

	public function handle(\imperator\User $user) {
		if(!$this->canUseChat($user, $this->getGid())) {
			throw new \imperator\exceptions\InvalidRequestException('User %1$d cannot use chat %2$d', $user->getId(), $this->getGid());
		}
		$message = new \imperator\chat\ChatMessage($this->getGid(), time(), $user, $this->getMessage());
		$message->insert();
	}
}