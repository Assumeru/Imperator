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
				return $this->handleUpdate();
			}
		}
		return $this->handleInvalidRequest();
	}

	protected function handleUpdate() {
		if($this->request->getType() == 'chat' && $this->canUseChat()) {
			return $this->handleChatUpdate();
		} else if($request->getType() == 'game') {
			return $this->handleGameUpdate();
		}
	}

	protected function handleChatUpdate() {
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

	protected function reply($json) {
		
	}

	protected function handleInvalidRequest() {
		
	}

	protected function canUseChat() {
		if($this->request->getGid() === 0) {
			return true;
		}
		return $this->isPlayerInGame();
	}

	protected function isPlayerInGame() {
		return Imperator::getDatabaseManager()->getTable('GamesJoined')->gameContainsPlayer($this->request->getGid(), $this->user);
	}
}