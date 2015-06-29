<?php
namespace imperator\api\requests;
use imperator\Imperator;

abstract class UpdateRequest extends \imperator\api\Request {
	private $gid;
	private $time;

	public static function buildRequest($params) {
		if(isset($params['type']) && isset($params['gid']) && is_numeric($params['gid']) && isset($params['time']) && is_numeric($params['time'])) {
			if($params['type'] == 'chat') {
				return new ChatUpdateRequest($params['gid'], $params['time']);
			} else if($params['type'] == 'game') {
				return new GameUpdateRequest($params['gid'], $params['time']);
			} else if($params['type'] == 'pregame') {
				return new PreGameUpdateRequest($params['gid'], $params['time']);
			}
		}
		return new InvalidRequest($params);
	}

	public function __construct($gid, $time) {
		$this->gid = (int)$gid;
		$this->time = (int)$time;
	}

	public function getMode() {
		return 'update';
	}

	public function getGid() {
		return $this->gid;
	}

	public function getTime() {
		return $this->time;
	}

	/**
	 * @param \imperator\chat\ChatMessage[] $messages
	 * 
	 * @return array
	 */
	protected function getJSONMessages(array $messages) {
		$json = array();
		foreach($messages as $message) {
			$user = $message->getUser();
			$jsonMessage = array(
				'message' => $message->getMessage(),
				'user' => array(
					'id' => $user->getId(),
					'name' => $user->getName()
				),
				'time' => date(DATE_ATOM, $message->getTime())
			);
			if($user instanceof \imperator\game\Player) {
				$jsonMessage['user']['color'] = $user->getColor();
				$jsonMessage['user']['url'] = $user->getUser()->getProfileLink();
			} else if($user instanceof \imperator\User) {
				$jsonMessage['user']['url'] = $user->getProfileLink();
			}
			$json[] = $jsonMessage;
		}
		return $json;
	}
}