(function($) {
	var $chatWindow,
	$gid = Imperator.settings.gid,
	$time = 0,
	$loading = true,
	$postgame = Imperator.settings.postgame !== undefined ? Imperator.settings.postgame : false,
	$canDelete = Imperator.settings.chat.canDelete,
	$updateErrors = 0;

	function create() {
		var $chat = $('#chat');
		$chatWindow = $('#chat-window');
		Imperator.API.onError(parseErrorMessage);
		Imperator.API.onMessage(parseChatMessage);
		Imperator.API.onOpen(function() {
			var $chatScrolling = $chat.find('input[name="chatscrolling"]');
			$chat.submit(submitChat);
			$chatScrolling[0].checked = Imperator.Store.getBoolean('chatscrolling', true);
			$chatScrolling.change(chatScrolling);
			sendUpdateRequest();
			$chat.parent().removeClass('hidden');
		});
	}

	function chatScrolling($e) {
		$e.preventDefault();
		Imperator.Store.setItem('chatscrolling', this.checked);
	}

	function submitChat($e) {
		var $input = $('#chat input[name="message"]'),
		$message = $input.val();
		$e.preventDefault();
		if($message !== undefined && $message !== '') {
			$message += '';
			$message = $message.trim();
			if($message !== '') {
				$input.val('');
				Imperator.API.send({
					mode: 'chat',
					type: 'add',
					gid: $gid,
					message: $message
				});
			}
		}
	}

	function sendUpdateRequest() {
		if($gid === 0 || $postgame) {
			Imperator.API.send({
				gid: $gid,
				time: $time,
				mode: 'update',
				type: 'chat'
			});
		}
	}

	function parseErrorMessage($msg) {
		if($msg !== undefined && $msg !== '' && $msg.error !== undefined && $msg.request !== undefined && (($msg.request.mode == 'update' && $msg.request.type == 'chat') || $msg.request.mode == 'chat')) {
			if($msg.request.mode == 'update') {
				$updateErrors++;
				if($updateErrors < Imperator.API.MAX_CHAT_ERRORS) {
					sendUpdateRequest();
				}
			}
			Imperator.Dialog.showDialog(Imperator.settings.chat.chaterror, $msg.error, true);
		}
	}

	function parseChatMessage($msg) {
		if($loading) {
			$chatWindow.empty();
			$loading = false;
		}
		if($msg !== undefined && $msg !== '' && $msg.update !== undefined && $msg.messages !== undefined) {
			for(var $n = 0; $n < $msg.messages.length; $n++) {
				addMessage($msg.messages[$n]);
			}
			if($msg.messages.length > 0 && Imperator.Store.getBoolean('chatscrolling', true)) {
				$chatWindow.scrollTop($chatWindow[0].scrollHeight);
			}
			$time = $msg.update;
			sendUpdateRequest();
			$updateErrors = 0;
		}
	}

	function addMessage($msg) {
		var $time = new Date($msg.time),
		$message = $(Imperator.settings.chat.template),
		$deleteButton = $message.find('[data-type="delete"]'),
		$user = $message.find('a.user'),
		$stamp = $message.find('time');
		if($canDelete) {
			$deleteButton.click(function($e) {
				$e.preventDefault();
				Imperator.API.send({
					uid: $msg.user.id,
					time: $msg.timestamp,
					gid: $gid,
					type: 'delete',
					mode: 'chat'
				});
				$message.remove();
			});
		} else {
			$deleteButton.hide();
		}
		$user.attr('href', $msg.user.url);
		$user.text($msg.user.name);
		if($msg.user.color !== undefined) {
			$user.css('color', '#'+$msg.user.color);
		}
		$stamp.attr('title', $time.toLocaleString());
		$stamp.attr('datetime', $msg.time);
		$stamp.text($time.toLocaleTimeString());
		$message.find('.message').text($msg.message);
		$chatWindow.append($message);
	}

	$(create);
})(jQuery);