<?php
namespace imperator\api;
use imperator\Imperator;

class LongPolling extends Api {
	protected static function handleChatUpdate(Request $request) {
		set_time_limit(0);
		$table = Imperator::getDatabaseManager()->getTable('Chat');
		while(!$table->hasMessagesAfter($request->getGid(), $request->getTime())) {
			sleep(1);
		}
		parent::handleChatUpdate($request);
	}

	protected static function reply($json, Request $request) {
		echo json_encode($json);
	}
}