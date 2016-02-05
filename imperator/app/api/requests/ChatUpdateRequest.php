<?php
namespace imperator\api\requests;
use imperator\Imperator;

class ChatUpdateRequest extends UpdateRequest {
	public function getType() {
		return 'chat';
	}

	public function handle(\imperator\User $user) {
		if(!$this->canUseChat($user, $this->getGid())) {
			throw new \imperator\exceptions\InvalidRequestException('User %1$d cannot use chat %2$d', $user->getId(), $this->getGid());
		}
		$messages = Imperator::getDatabaseManager()->getChatTable()->getMessagesAfter($this->getGid(), $this->getTime());
		return array(
			'messages' => $this->getJSONMessages($messages),
			'update' => time()
		);
	}
}