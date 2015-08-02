jQuery(function($) {
	var $started = $('form input[name="hidestarted"]'),
	$password = $('form input[name="hidepassword"]'),
	$list = $('#gamelist');

	function changeStarted() {
		var $checked = $(this).prop('checked');
		if($checked) {
			$list.addClass('hide-started');
		} else {
			$list.removeClass('hide-started');
		}
		Imperator.Store.setItem('gameListHideStarted', $checked);
	}

	function changePassword() {
		var $checked = $(this).prop('checked');
		if($checked) {
			$list.addClass('hide-password');
		} else {
			$list.removeClass('hide-password');
		}
		Imperator.Store.setItem('gameListHidePassword', $checked);
	}

	$started.change(changeStarted);
	$password.change(changePassword);
	$started.prop('checked', Imperator.Store.getBoolean('gameListHideStarted', false));
	$password.prop('checked', Imperator.Store.getBoolean('gameListHidePassword', false));

	$started.change();
	$password.change();
});