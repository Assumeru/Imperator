Imperator.Dialog = (function($) {
	function Dialog($html) {
		var $message = $html.find('[data-value="message"]'),
		$header = $html.find('[data-value="header"]');
		this.close = function() {
			closeDialog($html)
		};
		this.message = $message;
		this.header = $header;
		this.dialog = $html;
	}

	function isEscapeKey($e) {
		return $e.key == 'Escape'
			|| $e.key == 'Esc'
			|| $e.keyCode == 27
			|| $e.which == 27;
	}

	function closeDialog($dialog) {
		$dialog.fadeOut(500, function() {
			$dialog.remove();
		});
	}

	function showDialogForm($header, $message, $buttons, $canBeClosed, $class) {
		var $dialog = showDialog($header, Imperator.settings.templates.dialogform, $canBeClosed, $class);
		$dialog.message.find('[data-value="dialog-form-message"]').append($message);
		$dialog.message.find('[data-value="dialog-form-controls"]').append($buttons);
		return $dialog;
	}

	function showDialog($header, $message, $canBeClosed, $class) {
		var $dialog = $(Imperator.settings.templates.dialog),
		$closeButton = $dialog.find('[data-value="close-button"]');
		$dialog.find('[data-value="header"]').text($header);
		if($class) {
			$dialog.find('[data-value="window"]').addClass($class);
		}
		if($message) {
			$dialog.find('[data-value="message"]').html($message);
		}
		if(!$canBeClosed) {
			$closeButton.hide();
		} else {
			$(window).keyup(function($e) {
				if(isEscapeKey($e)) {
					$closeButton.click();
				}
			});
			$closeButton.click(function() {
				closeDialog($dialog);
			});
			$closeButton.focus();
		}
		$dialog.hide();
		$(document.body).append($dialog);
		$dialog.fadeIn(500);
		return new Dialog($dialog);
	}

	return {
		showDialog: showDialog,
		showDialogForm: showDialogForm,
		closeDialog: closeDialog
	};
})(jQuery);