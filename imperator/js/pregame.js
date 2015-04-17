(function($) {
	var $gid = Imperator.settings.gid,
	$time = 0;

	function init() {
		Imperator.API.onMessage(parseGameUpdate);
		Imperator.API.onOpen(sendUpdateRequest);
	}

	function sendUpdateRequest() {
		Imperator.API.send({
			gid: $gid,
			mode: 'update',
			type: 'pregame',
			time: $time
		});
	}

	function parseGameUpdate($msg) {
		var $n, $playerList = $('#player-list');
		if($msg !== undefined && $msg !== '' && $msg.update !== undefined) {
			$time = $msg.update;
			if($msg.gameState !== undefined) {
				window.alert($msg.gameState);
				window.location.reload();
				return;
			}
			if($msg.players !== undefined) {
				$playerList.empty();
				for($n = 0; $n < $msg.players.length; $n++) {
					$playerList.append($msg.players[$n]);
				}
				if($msg.players.length === $msg.maxPlayers) {
					$('#join-game').hide();
				} else {
					$('#join-game').show();
				}
			}
			if($msg.ownerControls !== undefined) {
				$('#owner-controls').html($($msg.ownerControls).find('form'));
			}
			sendUpdateRequest();
		}
	}

	$(init);
})(jQuery);