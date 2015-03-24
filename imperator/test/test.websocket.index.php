<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Test</title>
	<script src="http://code.jquery.com/jquery-1.11.2.min.js"></script>
	<script>
		$webSocket = new WebSocket('ws://'+window.location.host+':8080/test');
		$webSocket.onmessage = function($msg) {
			var $p = $('<p></p>');
			$p.text($msg.data);
			$('#output').append($p);
		};
		$webSocket.onclose = function() {
			$('#output').append('<p style="color: red;">Connection closed</p>');
		};
		$(function() {
			$('form').submit(function($e) {
				$e.preventDefault();
				$webSocket.send($('textarea').val());
			});
		});
	</script>
</head>
<body>
<div id="output">
</div>
<form>
	<textarea></textarea>
	<input type="submit" />
</form>
</body>
</html>