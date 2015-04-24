(function($) {
	var $currentTab = ['players'];

	function init() {
		parseHash();
		updateTab();
		$(window).on('hashchange', function() {
			parseHash();
			updateTab();
		});
	}

	function updateTab($e) {
		if($e !== undefined) {
			$e.preventDefault();
		}
		$('#content nav li.active').removeClass('active');
		$('#content nav a[href="#'+$currentTab[0]+'"]').parent().addClass('active');
	}

	function parseHash() {
		var $page = window.location.hash.replace('#', ''),
		$userIsPlayer = !$('#main').hasClass('not-player');
		if($page !== '') {
			if($page == 'players' || $page == 'regions' || $page == 'territories' || ($userIsPlayer && ($page == 'cards' || $page == 'chatbox' || $page == 'settings' || $page == 'log'))) {
				$currentTab = [$page];
			} else if($page.indexOf('territory-') === 0) {
				$page = $page.split('-');
				if($NATIONS[$page[1]] !== undefined) {
					$currentTab = $page;
				}
			}
		} else {
			$currentTab = ['territories'];
		}
	}

	$(init);
})(jQuery);