<?php
namespace imperator\api;
use imperator\Imperator;

class LongPolling extends Api {
	protected function handleChatUpdateRequest() {
		set_time_limit(0);
		$table = Imperator::getDatabaseManager()->getTable('Chat');
		$settings = Imperator::getSettings();
		$max = $settings->getMaxLongPollingTries();
		$sleep = $settings->getLongPollingTimeout();
		$gid = $this->getRequest()->getGid();
		$time = $this->getRequest()->getTime();
		for($n = 0; !$table->hasMessagesAfter($gid, $time) && ($n < $max || $max === 0); $n++) {
			sleep($sleep);
		}
		if($n >= $max && $max !== 0) {
			return parent::replyWithMessages(array());
		} else {
			return parent::handleChatUpdateRequest();
		}
	}

	protected function handleGameUpdateRequest($pregame = false) {
		set_time_limit(0);
		$chat = Imperator::getDatabaseManager()->getTable('Chat');
		$games = Imperator::getDatabaseManager()->getTable('Games');
		$settings = Imperator::getSettings();
		$max = $settings->getMaxLongPollingTries();
		$sleep = $settings->getLongPollingTimeout();
		$gid = $this->getRequest()->getGid();
		$time = $this->getRequest()->getTime();
		for($n = 0; !$chat->hasMessagesAfter($gid, $time) && !$games->timeIsAfter($gid, $time) && ($n < $max || $max === 0); $n++) {
			sleep($sleep);
		}
		if($n >= $max && $max !== 0) {
			return parent::reply(array('update' => time()));
		} else {
			return parent::handleGameUpdateRequest($pregame);
		}
	}

	protected function reply($json) {
		$this->sendHeader('200 Success');
		return json_encode($json);
	}

	protected function handleInvalidRequest() {
		$this->sendHeader('400 Bad request');
		return '{"error":"Bad request"}';
	}

	private function sendHeader($header) {
		header('Content-Type: application/json');
		header('HTTP/1.0 '.$header);
	}
}