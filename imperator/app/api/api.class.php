<?php
namespace imperator\api;
use imperator\Imperator;

abstract class Api {
	public static function handleRequest(Request $request) {
		if($request->isValid()) {
			if($request->getMode() == Request::MODE_UPDATE) {
				static::handleUpdate($request);
			}
		} else {
			static::handleInvalidRequest($request);
		}
	}

	protected static function handleUpdate(Request $request) {
		if($request->getType() == 'chat' && static::canUseChat($request)) {
			static::handleChatUpdate($request);
		} else if($request->getType() == 'game') {
			static::handleGameUpdate($request);
		}
	}

	protected static function handleChatUpdate(Request $request) {
		$messages = Imperator::getDatabaseManager()->getTable('Chat')->getMessagesAfter($request->getGid(), $request->getTime());
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
		static::reply(array('messages' => $json, 'update' => time()), $request);
	}

	protected static function reply($json, Request $request) {
		
	}

	protected static function handleInvalidRequest(Request $request) {
		
	}

	protected static function canUseChat(Request $request) {
		if($request->getGid() === 0) {
			return true;
		}
		return static::isPlayerInGame($request);
	}

	protected static function isPlayerInGame(Request $request) {
		return Imperator::getDatabaseManager()->getTable('GamesJoined')->gameContainsPlayer($request->getGid(), $request->getUser());
	}
}