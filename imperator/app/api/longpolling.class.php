<?php
namespace imperator\api;
use imperator\Imperator;

class LongPolling extends Api {
	protected static function handleChatUpdate(Request $request) {
		set_time_limit(0);
		$table = Imperator::getDatabaseManager()->getTable('Chat');
		$settings = Imperator::getSettings();
		$max = $settings->getMaxLongPollingTries();
		$sleep = $settings->getLongPollingTimeout();
		for($n = 0; !$table->hasMessagesAfter($request->getGid(), $request->getTime()) && ($n < $max || $max === 0); $n++) {
			sleep($sleep);
		}
		if($n >= $max && $max !== 0) {
			self::sendHeader('204 No content');
		} else {
			parent::handleChatUpdate($request);
		}
	}

	protected static function reply($json, Request $request) {
		self::sendHeader('200 Success');
		echo json_encode($json);
	}

	protected static function handleInvalidRequest(Request $request) {
		self::sendHeader('400 Bad request');
		echo '{"error":"Bad request"}';
	}

	private static function sendHeader($header) {
		header('Content-Type: application/json');
		header('HTTP/1.0 '.$header);
	}
}