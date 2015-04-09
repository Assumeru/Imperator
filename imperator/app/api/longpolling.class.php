<?php
namespace imperator\api;
use imperator\Imperator;

class LongPolling extends Api {
	protected function handleChatUpdate() {
		set_time_limit(0);
		$table = Imperator::getDatabaseManager()->getTable('Chat');
		$settings = Imperator::getSettings();
		$max = $settings->getMaxLongPollingTries();
		$sleep = $settings->getLongPollingTimeout();
		for($n = 0; !$table->hasMessagesAfter($this->getRequest()->getGid(), $this->getRequest()->getTime()) && ($n < $max || $max === 0); $n++) {
			sleep($sleep);
		}
		if($n >= $max && $max !== 0) {
			$this->sendHeader('204 No content');
		} else {
			return parent::handleChatUpdate();
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