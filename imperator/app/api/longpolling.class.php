<?php
namespace imperator\api;
use imperator\Imperator;

class LongPolling extends Api {
	private $hasHeaders = false;

	public function handleRequest() {
		$shutDownHandler = Imperator::getShutDownHandler();
		$shutDownHandler->setMode(\imperator\ShutDownHandler::MODE_OUTPUT_JSON);
		if($this->getRequest() instanceof requests\ChatUpdateRequest) {
			$output = $this->handleChatUpdateRequest();
		} else if($this->getRequest() instanceof requests\GameUpdateRequest) {
			$output = $this->handleGameUpdateRequest();
		} else {
			$output = parent::handleRequest();
		}
		$shutDownHandler->setMode(\imperator\ShutDownHandler::MODE_OUTPUT_NOTHING);
		return $output;
	}

	protected function handleChatUpdateRequest() {
		set_time_limit(0);
		$table = Imperator::getDatabaseManager()->getChatTable();
		$settings = Imperator::getSettings();
		$max = $settings->getMaxLongPollingTries();
		$sleep = $settings->getLongPollingTimeout();
		$gid = $this->getRequest()->getGid();
		$time = $this->getRequest()->getTime();
		for($n = 0; !$table->hasMessagesAfter($gid, $time) && ($n < $max || $max === 0); $n++) {
			sleep($sleep);
		}
		return parent::handleRequest();
	}

	protected function handleGameUpdateRequest() {
		set_time_limit(0);
		$chat = Imperator::getDatabaseManager()->getChatTable();
		$games = Imperator::getDatabaseManager()->getGamesTable();
		$settings = Imperator::getSettings();
		$max = $settings->getMaxLongPollingTries();
		$sleep = $settings->getLongPollingTimeout();
		$gid = $this->getRequest()->getGid();
		$time = $this->getRequest()->getTime();
		for($n = 0; !$chat->hasMessagesAfter($gid, $time) && $games->gameExists($gid) && !$games->timeIsAfter($gid, $time) && ($n < $max || $max === 0); $n++) {
			sleep($sleep);
		}
		return parent::handleRequest();
	}

	protected function reply($json) {
		$json = parent::reply($json);
		$this->sendHeader('200 Success');
		return json_encode($json);
	}

	protected function sendError(\imperator\exceptions\InvalidRequestException $e) {
		$this->sendHeader('400 '.$e->getMessage());
		return parent::sendError($e);
	}

	protected function sendFatalError() {
		$this->sendHeader('500 Internal server error');
		return parent::sendFatalError($e);
	}

	private function sendHeader($header) {
		if(!$this->hasHeaders) {
			header('Content-Type: application/json');
			header('HTTP/1.0 '.$header);
			$this->hasHeaders = true;
		}
	}
}