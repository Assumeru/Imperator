<?php
namespace imperator\api\requests;
use imperator\Imperator;

class ChatDeleteRequest extends ChatRequest {
	private $time;
	private $uid;

	public function __construct($gid, $time, $uid) {
		parent::__construct($gid);
		$this->time = (int)$time;
		$this->uid = (int)$uid;
	}

	public function getType() {
		return 'delete';
	}

	protected function getTime() {
		return $this->time;
	}

	protected function getUid() {
		return $this->uid;
	}

	public function handle(\imperator\User $user) {
		if(!$user->canDeleteChatMessages() && !$this->isGameOwner($user, $this->getGid())) {
			throw new \imperator\exceptions\InvalidRequestException('User %1$d cannot delete from chat %2$d', $user->getId(), $this->getGid());
		}
		$userClass = Imperator::getSettings()->getUserClass();
		$message = new \imperator\chat\ChatMessage($this->getGid(), $this->getTime(), new $userClass($this->getUid()), '');
		$message->delete();
	}
}