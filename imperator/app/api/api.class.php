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

	protected function sendError(\imperator\exceptions\InvalidRequestException $e) {
		return $this->reply(array(
			'error' => $e->getUserFriendlyMessage($this->user)
		));
	}

	public function handleRequest() {
		try {
			$output = $this->request->handle($this->user);
			if(!empty($output)) {
				return $this->reply($output);
			}
		} catch(\imperator\exceptions\InvalidRequestException $e) {
			Imperator::getLogger()->log(\imperator\Logger::LEVEL_WARNING, $e);
			return $this->sendError($e);
		} catch(\imperator\exceptions\ImperatorException $e) {
			Imperator::getLogger()->log(\imperator\Logger::LEVEL_WARNING, $e);
			return $this->reply(array(
				'error' => 'Fatal error'
			));
		} catch(\Exception $e) {
			Imperator::getLogger()->log(\imperator\Logger::LEVEL_FATAL, $e);
			return $this->reply(array(
				'error' => 'Fatal error'
			));
		}
	}

	protected function reply(array $json) {
		$json['request'] = array(
			'mode' => $this->request->getMode(),
			'type' => $this->request->getType()
		);
		return $json;
	}
}