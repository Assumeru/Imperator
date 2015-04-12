Imperator.API = (function($) {
	var $open = false,
	$onOpen = [],
	$onMessage = [],
	$mode,
	$longPollingURL = Imperator.settings.API.longpollingURL;

	function connect() {
		if(supportsWebSocket()) {
			$mode = 'WebSocket';
			makeWebSocketConnection();
		} else {
			$mode = 'LongPolling';
			makeLongPollingConnection();
		}
	}

	function makeWebSocketConnection() {
		//TODO
		new WebSocket();
	}

	function makeLongPollingConnection() {
		$open = true;
		onOpen();
	}

	function supportsWebSocket() {
		return false;
		try {
			return window.WebSocket !== undefined;
		} catch($e) {
			return false;
		}
	}

	function onOpen() {
		for(var $n = 0; $n < $onOpen.length; $n++) {
			$onOpen[$n]();
		}
	}

	function addOnOpen($function) {
		if($open) {
			$function();
		} else {
			$onOpen.push($function);
		}
	}

	function onMessage($json) {
		for(var $n = 0; $n < $onMessage.length; $n++) {
			$onMessage[$n]($json);
		}
	}

	function addOnMessage($function) {
		$onMessage.push($function);
	}

	function sendWebSocket($json) {
		
	}

	function sendLongPolling($json) {
		$.ajax({
			method: 'POST',
			url: $longPollingURL,
			data: $json
		}).done(function($msg) {
			onMessage($msg);
		}).error(function($msg) {
			console.log($msg);
		});
	}

	function send($json) {
		if($open) {
			if($mode == 'WebSocket') {
				return sendWebSocket($json);
			} else {
				return sendLongPolling($json);
			}
		}
		return false;
	}

	connect();

	return {
		onOpen: addOnOpen,
		onMessage: addOnMessage,
		send: send
	};
})(jQuery);