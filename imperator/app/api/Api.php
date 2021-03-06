<?php
namespace imperator\api;
use imperator\Imperator;

abstract class Api {
	const LONGPOLLING = '\\imperator\\api\\LongPolling';
	const WEBSOCKET = '\\imperator\\api\\WebSocket';
	private $user;
	private $request;
	private $apiHandler;

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

	protected function getApiHandler() {
		return $this->apiHandler;
	}

	public function setApiHandler(\imperator\websocket\ApiHandler $apiHandler) {
		$this->apiHandler = $apiHandler;
	}

	protected function sendError(\imperator\exceptions\InvalidRequestException $e) {
		return $this->reply(array(
			'error' => $e->getUserFriendlyMessage($this->user)
		));
	}

	protected function sendFatalError() {
		return $this->reply(array(
			'error' => 'Fatal error'
		));
	}

	public function handleRequest() {
		try {
			$output = $this->request->handle($this->user);
			if(!empty($output)) {
				return $this->reply($output);
			}
		} catch(\imperator\exceptions\InvalidRequestException $e) {
			Imperator::getLogger()->w($e);
			return $this->sendError($e);
		} catch(\imperator\exceptions\ImperatorException $e) {
			Imperator::getLogger()->w($e);
			return $this->sendFatalError();
		} catch(\Exception $e) {
			Imperator::getLogger()->e($e);
			return $this->sendFatalError();
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