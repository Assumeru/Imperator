(function($) {
	var $currentTab = ['players'],
	$game = {
		map: {
			territories: {},
			regions: {}
		},
		players: {},
		id: Imperator.settings.gid
	},
	$time = 0;

	function init() {
		Imperator.API.onMessage(parseUpdateMessage);
		Imperator.API.onOpen(function() {
			Imperator.API.send({
				mode: 'update',
				type: 'game',
				gid: $game.id,
				time: $time
			});
		});
		parseHash();
		updateTab();
		$(window).on('hashchange', function() {
			parseHash();
			updateTab();
		});
	}

	function parseUpdateMessage($msg) {
		var $id, $key,
		$territoriesUpdated = false;
		if($msg !== undefined && $msg !== '') {
			if($msg.regions !== undefined) {
				$game.map.regions = $msg.regions;
			}
			if($msg.territories !== undefined) {
				for($id in $msg.territories) {
					if($game.map.territories[$id] === undefined) {
						$game.map.territories[$id] = $msg.territories[$id];
						$territoriesUpdated = true;
					} else {
						for($key in $msg.territories[$id]) {
							$game.map.territories[$id][$key] = $msg.territories[$id][$key];
							$territoriesUpdated = true;
						}
					}
				}
			}
			if($msg.players !== undefined) {
				for($id in $msg.players) {
					if($game.players[$id] === undefined) {
						$game.players[$id] = $msg.players[$id];
					} else {
						for($key in $msg.players[$id]) {
							$game.players[$id][$key] = $msg.players[$id][$key];
						}
					}
				}
			}
		}
		if($territoriesUpdated) {
			updateTerritories();
		}
	}

	function updateTerritories() {
		var $id, $territory, $player, $players = [];
		for($id in $game.players) {
			$players[$id] = {
				territories: 0,
				units: 0
			};
		}
		for($id in $game.map.territories) {
			$territory = $game.map.territories[$id];
			$players[$territory.uid].territories++;
			$players[$territory.uid].units += $territory.units;
			$('#'+$id).css('fill', '#'+$game.players[$territory.uid].color);
		}
		for($id in $players) {
			$player = $('#players *[data-player="'+$id+'"]');
			$player.find('*[data-value="territories"]').text($players[$id].territories);
			$player.find('*[data-value="units"]').text($players[$id].units);
		}
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