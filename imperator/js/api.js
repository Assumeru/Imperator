Imperator.API = (function($) {
	var $open = false,
	$unloading = false,
	$onOpen = [],
	$onMessage = [],
	$onError = [],
	$mode,
	$longPollingURL = Imperator.settings.API.longpollingURL,
	$webSocketURL = Imperator.settings.API.webSocketURL,
	$ws;
	if(window.console === undefined) {
		window.console = {};
	}
	if(typeof window.console.log != 'function') {
		window.console.log = function() {};
	}
	if(typeof window.console.error != 'function') {
		window.console.error = function() {};
	}

	function connect() {
		$(window).on('beforeunload', function() {
			$unloading = true;
		});
		if(supportsWebSocket()) {
			$mode = 'WebSocket';
			makeWebSocketConnection();
		} else {
			$mode = 'LongPolling';
			makeLongPollingConnection();
		}
	}

	function makeWebSocketConnection() {
		$ws = new WebSocket($webSocketURL);
		$ws.onopen = function() {
			if(!$open) {
				$open = true;
				onOpen();
			}
		};
		$ws.onmessage = receiveWebSocket(onMessage);
		$ws.onerror = receiveWebSocket(onError);
		$ws.onclose = attemptWebSocketReconnect;
	}

	function attemptWebSocketReconnect() {
		onError({
			error: '',
			method: {
				mode: 'update',
				type: 'game'
			}
		});
		$ws.close();
		makeWebSocketConnection();
	}

	function receiveWebSocket($func) {
		return function($msg) {
			try {
				$func(JSON.parse($msg));
			} catch($e) {
				console.log($msg);
			}
		};
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

	function onError($response, $msg) {
		if($msg !== undefined && $msg.mode !== undefined && $msg.type !== undefined) {
			if($response === undefined || $response.error === undefined) {
				$response = {
					request: {
						mode: $msg.mode,
						type: $msg.type
					},
					error: $response
				};
			} else if($response.request === undefined || $response.request.mode === undefined || $response.request.type === undefined) {
				if($response.request === undefined) {
					$response.request = {};
				}
				if($response.request.mode === undefined) {
					$response.request.mode = $msg.mode;
				}
				if($response.request.type === undefined) {
					$response.request.type = $msg.type;
				}
			}
		}
		if($response.error === undefined) {
			$response.error = Imperator.settings.language.unknownerror;
		}
		for(var $n = 0; $n < $onError.length; $n++) {
			$onError[$n]($response);
		}
	}

	function addOnError($function) {
		$onError.push($function);
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
		if($ws.readyState == WebSocket.OPEN) {
			$ws.send(JSON.stringify($json));
		}
	}

	function sendLongPolling($json) {
		$.ajax({
			method: 'POST',
			url: $longPollingURL,
			data: $json
		}).done(function($msg) {
			onMessage($msg);
			if($msg !== undefined && $msg.error !== undefined) {
				onError($msg, $json);
			}
		}).fail(function($msg) {
			if(!$unloading) {
				onError($msg.responseJSON, $json);
			}
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
		onError: addOnError,
		onMessage: addOnMessage,
		send: send,
		MAX_CHAT_ERRORS: 5,
		MAX_GAME_ERRORS: 5
	};
})(jQuery);