var API = (function() {
	function connect() {
		if(supportsWebSocket()) {
			makeWebSocketConnection();
		} else {
			makeLongPollingConnection();
		}
	}

	function makeWebSocketConnection() {
		new WebSocket();
	}

	function makeLongPollingConnection() {
		
	}

	function supportsWebSocket() {
		try {
			return window.WebSocket !== undefined;
		} catch($e) {
			return false;
		}
	}

	return {};
})();