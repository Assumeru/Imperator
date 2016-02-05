<?php
namespace imperator\api;

class WebSocket extends Api {
	public function handleRequest() {
		$request = $this->getRequest();
		if($request instanceof requests\ChatAddRequest) {
			$response = parent::handleRequest();
			$this->getApiHandler()->sendChatToConnections($request->getGid(), $response);
			return $response;
		}
		return parent::handleRequest();
	}

	protected function reply(array $json) {
		return json_encode(parent::reply($json));
	}
}