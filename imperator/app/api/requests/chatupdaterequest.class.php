<?php
namespace imperator\api\requests;
use imperator\Imperator;

class ChatUpdateRequest extends UpdateRequest {
	public function getType() {
		return 'chat';
	}

	public function handle(\imperator\User $user) {
		if(!$this->canUseChat($user, $this->getGid())) {
			throw new \imperator\exceptions\InvalidRequestException('User '.$user->getId().' cannot use chat '.$this->getGid());
		}
		$messages = Imperator::getDatabaseManager()->getTable('Chat')->getMessagesAfter($this->getGid(), $this->getTime());
		return array(
			'messages' => $this->getJSONMessages($messages),
			'update' => time()
		);
	}
}