Imperator.Chat = (function($) {
	var $chatWindow,
	$gid = Imperator.settings.gid,
	$time = 0,
	$loading = true;

	function create() {
		var $chat = $('#chat');
		$chatWindow = $('#chat-window');
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
		if($gid === 0) {
			Imperator.API.send({
				gid: $gid,
				time: $time,
				mode: 'update',
				type: 'chat'
			});
		}
	}

	function parseChatMessage($msg) {
		if($loading) {
			$chatWindow.empty();
			$loading = false;
		}
		if($msg !== undefined && $msg !== '') {
			if($msg.update !== undefined && $msg.messages !== undefined) {
				for(var $n = 0; $n < $msg.messages.length; $n++) {
					addMessage($msg.messages[$n]);
				}
				if($msg.messages.length > 0 && Imperator.Store.getBoolean('chatscrolling', true)) {
					$chatWindow.scrollTop($chatWindow[0].scrollHeight);
				}
				$time = $msg.update;
				sendUpdateRequest();
			}
		}
	}

	function addMessage($msg) {
		var $time = new Date($msg.time),
		$chat = $('<div class="chat"><a href="'+$msg.user.url+'" class="user">'+$msg.user.name+'</a> (<time title="'+$time.toLocaleString()+'" datetime="'+$msg.time+'">'+$time.toLocaleTimeString()+'</time>): </div>'),
		$message = $('<span class="message"></span>');
		if($msg.user.color !== undefined) {
			$chat.find('a.user').css('color', '#'+$msg.user.color);
		}
		$message.text($msg.message);
		$chat.append($message);
		$chatWindow.append($chat);
	}

	$(create);
})(jQuery);