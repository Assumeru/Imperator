Imperator.Chat = (function($) {
	var $chatWindow,
	$gid = 0,
	$time = 0;

	function create() {
		$chatWindow = $('#chat-window');
		Imperator.API.onMessage(parseChatMessage);
		Imperator.API.onOpen(function() {
			$('#chat').parent().removeClass('hidden');
			$chatWindow.empty();
			sendUpdateRequest();
		});
	}

	function sendUpdateRequest() {
		Imperator.API.send({
			gid: $gid,
			time: $time,
			mode: 'update',
			type: 'chat'
		});
	}

	function parseChatMessage($msg) {
		if($msg.update !== undefined && $msg.messages !== undefined) {
			for(var $n = 0; $n < $msg.messages.length; $n++) {
				addMessage($msg.messages[$n]);
			}
			$time = $msg.update;
			sendUpdateRequest();
		}
	}

	function addMessage($msg) {
		var $chat = $('<div class="chat"></div>'),
		$row = $('<div class="row"></div>'),
		$time = new Date($msg.time),
		$user = $('<div class="col-xs-2"><a href="'+$msg.user.url+'" class="user">'+$msg.user.name+'</a> (<time title="'+$time.toLocaleString()+'" datetime="'+$msg.time+'">'+$time.toLocaleTimeString()+'</time>):</div>'),
		$message = $('<div class="col-xs-10"></div>');
		if($msg.user.color !== undefined) {
			$user.find('a').style('color', '#'+$msg.user.color);
		}
		$row.append($user);
		$row.append($message);
		$chat.append($row);
		$message.text($msg.message);
		$chatWindow.append($chat);
	}

	$(create);
})(jQuery);