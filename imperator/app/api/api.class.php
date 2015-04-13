<?php
namespace imperator\api;
use imperator\Imperator;

abstract class Api {
	const LONGPOLLING = '\\imperator\\api\\LongPolling';
	const WEBSOCKET = '\\imperator\\api\\WebSocket';
	private $user;
	private $request;

	public function __construct(Request $request, \imperator\User $user) {
		$this->request = $request;
		$this->user = $user;
	}

	protected function getUser() {
		return $this->user;
	}

	protected function getRequest() {
		return $this->request;
	}

	public function handleRequest() {
		if($this->request->isValid()) {
			if($this->request->getMode() == Request::MODE_UPDATE) {
				return $this->handleUpdateRequest();
			} else if($this->request->getMode() == Request::MODE_CHAT) {
				return $this->handleChatRequest();
			}
		}
		return $this->handleInvalidRequest();
	}

	protected function handleUpdateRequest() {
		if($this->request->getType() == 'chat' && $this->canUseChat()) {
			return $this->handleChatUpdateRequest();
		} else if($request->getType() == 'game') {
			return $this->handleGameUpdateRequest();
		} else if($request->getType() == 'pregame') {
			return $this->handleGameUpdateRequest(true);
		}
	}

	protected function handleGameUpdateRequest($pregame = false) {
		
	}

	protected function handleChatUpdateRequest() {
		$messages = Imperator::getDatabaseManager()->getTable('Chat')->getMessagesAfter($this->request->getGid(), $this->request->getTime());
		return $this->replyWithMessages($messages);
	}

	protected function replyWithMessages(array $messages) {
		$messages = Imperator::getDatabaseManager()->getTable('Chat')->getMessagesAfter($this->request->getGid(), $this->request->getTime());
		$json = array();
		foreach($messages as $message) {
			$user = $message->getUser();
			$jsonMessage = array(
				'message' => $message->getMessage(),
				'user' => array(
					'id' => $user->getId(),
					'name' => $user->getName(),
					'url' => $user->getProfileLink()
				),
				'time' => date(DATE_ATOM, $message->getTime())
			);
			if($user->getColor()) {
				$jsonMessage['user']['color'] = $user->getColor();
			}
			$json[] = $jsonMessage;
		}
		return $this->reply(array('messages' => $json, 'update' => time()));
	}

	protected function handleChatRequest() {
		if($this->request->getType() == 'add' && $this->canUseChat()) {
			return $this->handleChatAddRequest();
		} else if($this->request->getType() == 'delete' && $this->canDeleteFromChat()) {
			return $this->handleChatDeleteRequest();
		}
	}

	protected function handleChatAddRequest() {
		$message = new \imperator\chat\ChatMessage($this->request->getGid(), time(), $this->user, $this->request->getMessage());
		$message->insert();
	}

	protected function handleChatDeleteRequest() {
		$userClass = Imperator::getSettings()->getUserClass();
		$message = new \imperator\chat\ChatMessage($this->request->getGid(), $this->request->getTime(), new $userClass($this->request->getUid()), '');
		$message->delete();
	}

	protected function reply($json) {}

	protected function handleInvalidRequest() {}

	protected function canUseChat() {
		if($this->request->getGid() === 0) {
			return true;
		}
		return $this->isPlayerInGame();
	}

	protected function isPlayerInGame() {
		return Imperator::getDatabaseManager()->getTable('GamesJoined')->gameContainsPlayer($this->request->getGid(), $this->user);
	}

	protected function isGameOwner() {
		return Imperator::getDatabaseManager()->getTable('Games')->gameOwnerEquals($this->request->getGid(), $this->user);
	}

	protected function canDeleteFromChat() {
		return $this->user->canDeleteChatMessages() || $this->isGameOwner();
	}
}