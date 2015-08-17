<?php
namespace imperator\api;

class WebSocket extends Api {
	protected function reply($json) {
		return json_encode(parent::reply($json));
	}
}