<?php
namespace imperator\api;

class WebSocket extends Api {
	public function handleRequest() {
		return parent::handleRequest();
	}

	protected function reply($json) {
		return json_encode(parent::reply($json));
	}
}