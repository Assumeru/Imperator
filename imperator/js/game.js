(function($) {
	var $currentTab = ['territories'],
	$game = {
		map: {
			territories: {},
			regions: {}
		},
		players: {},
		id: Imperator.settings.gid
	},
	$time = 0,
	$resizeTimeout,
	$emptyBorder;
	if(Number.parseInt === undefined) {
		Number.parseInt = parseInt;
	}

	function init() {
		var $window = $(window);
		Imperator.API.onMessage(parseUpdateMessage);
		Imperator.API.onOpen(function() {
			Imperator.API.send({
				mode: 'update',
				type: 'game',
				gid: $game.id,
				time: $time
			});
		});
		Imperator.Map.onLoad(function() {
			$('#map svg g[id]').click(function() {
				window.location = '#tab-territory-'+this.id;
			});
		});
		$emptyBorder = $('#territory [data-value="border"]');
		$emptyBorder.remove();
		parseHash();
		resetTabScroll();
		$window.on('hashchange', function($e) {
			var $previous = $currentTab[0];
			parseHash();
			updateTab($previous);
		});
		$window.resize(function() {
			clearTimeout($resizeTimeout);
			$resizeTimeout = setTimeout(resetTabScroll, 250);
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
			$player.find('*[data-value="unitsperturn"]').text(getUnitsPerTurnFor($id));
		}
		updateRegionDivision();
	}

	function updateRegionDivision() {
		var $region, $players, $uid, $n, $territories, $div, $span;
		for($region in $game.map.regions) {
			$players = {};
			for($uid in $game.players) {
				$players[$uid] = 0;
			}
			$territories = $game.map.regions[$region].territories;
			for($n = 0; $n < $territories.length; $n++) {
				$players[$game.map.territories[$territories[$n]].uid]++;
			}
			$div = $('#regions .region-division[data-region="'+$region+'"]');
			$div.empty();
			for($uid in $players) {
				$span = $('<div>');
				$span.css('backgroundColor', '#'+$game.players[$uid].color);
				$span.css('width', (100 * $players[$uid] / $territories.length) + '%');
				$span.attr('title', $game.players[$uid].name);
				$div.append($span);
			}
		}
	}

	function updateTab($current) {
		var $destination,
		$target = $('#'+$currentTab[0]),
		$parent = $target.parent(),
		$panes = $('#content .swipe-panes'),
		$current = $('#'+$current),
		$currentParent = $current.parent(),
		$nav = $('#content nav'),
		$tab = $nav.find('a[href|="#tab-'+$currentTab[0]+'"]').parent();
		$nav.find('li.active').removeClass('active');
		$tab.addClass('active');
		$nav.animate({
			scrollLeft: $tab.offset().left
		}, 500);
		function getDestination() {
			return $target.offset().left - getOffset($target, 'right') + $parent.scrollLeft();
		}
		if(!$currentParent.is($parent)) {
			$destination = ($currentParent.index() < $parent.index() ? 1 : -1) * $parent.outerWidth();
			$parent.scrollLeft(getDestination() - $destination);
			$panes.animate({
				scrollLeft: $destination
			}, 750, 'swing', function() {
				$parent.scrollLeft(getDestination());
			});
		} else {
			$parent.animate({
				scrollLeft: getDestination()
			}, 750);
		}
	}

	function resetTabScroll() {
		updateTab($currentTab[0]);
	}

	function fillTerritoryTab() {
		var $n, $border, $bordering,
		$tab = $('#territory'),
		$territory = $game.map.territories[$currentTab[1]],
		$player = $game.players[$territory.uid],
		$a = $($player.link).css('color', '#'+$player.color),
		$borders = $tab.find('[data-value="borders"]');
		$tab.find('[data-value="name"]').text($territory.name);
		$tab.find('[data-value="units"]').text($territory.units);
		$tab.find('[data-value="owner"]').html($a);
		$tab.find('[data-value="regions"]').html($('#territories [data-territory="'+$territory.id+'"] [data-value="regions"]').html());
		$tab.find('[data-value="flag"]').attr('src', getFlagFor($territory.id));
		$('#'+$territory.id).attr('class', 'active');
		$borders.empty();
		for($n = 0; $n < $territory.borders.length; $n++) {
			$bordering = $game.map.territories[$territory.borders[$n]];
			$('#'+$bordering.id).attr('class', 'active border');
			$border = $emptyBorder.clone();
			$border.find('[data-value="border-name"]')
				.text($bordering.name)
				.attr('href', '#tab-territory-'+$bordering.id)
				.css('color', '#'+$game.players[$bordering.uid].color);
			$border.find('[data-value="border-flag"]').attr('src', getFlagFor($bordering.id));
			$borders.append($border);
		}
	}

	function getFlagFor($territory) {
		return $('#territories [data-territory="'+$territory+'"] [data-value="flag"]').attr('src');
	}

	function getOffset($element, $side) {
		var $n, $add,
		$css = ['padding-?', 'border-?-width', 'margin-?'],
		$offset = 0;
		for($n = 0; $n < $css.length; $n++) {
			$add = Number.parseInt($element.css($css[$n].replace('?', $side)), 10);
			if(!isNaN($add)) {
				$offset += $add;
			}
		}
		return $offset;
	}

	function parseHash() {
		var $page = window.location.hash.replace('#', ''),
		$userIsPlayer = !$('#main').hasClass('not-player'),
		$a = $('#content nav a[href|="#tab-territory"]'),
		$territoryTab = $a.parent();
		$a.attr('href', '#tab-territory');
		$territoryTab.hide();
		$('#content svg g[id]').attr('class', '');
		if($page !== '') {
			$page = $page.split('-');
			if($page.length === 2) {
				if($page[1] == 'players' || $page[1] == 'regions' || $page[1] == 'territories' || $page[1] == 'map' || ($userIsPlayer && ($page[1] == 'cards' || $page[1] == 'chatbox' || $page[1] == 'settings' || $page[1] == 'log'))) {
					$currentTab = [$page[1]];
				}
			} else if($page.length === 3 && $page[1] == 'territory') {
				if($game.map.territories[$page[2]] !== undefined) {
					$page.shift();
					$currentTab = $page;
					$a.text($game.map.territories[$page[1]].name);
					$a.attr('href', '#tab-territory-'+$page[1]);
					$territoryTab.show();
					fillTerritoryTab();
				}
			}
		} else {
			$currentTab = ['territories'];
		}
		window.location.hash = 'tab-'+$currentTab.join('-');
	}

	function getUnitsPerTurnFor($uid) {
		var $id, $out,
		$territories = 0;
		for($id in $game.map.territories) {
			if($game.map.territories[$id].uid === $uid) {
				$territories++;
			}
		}
		$out = Math.floor($territories / 3);
		for($id in $game.map.regions) {
			if(regionOwnedBy($id, $uid)) {
				$out += $game.map.regions[$id].units;
			}
		}
		return $out;
	}

	function regionOwnedBy($region, $uid) {
		var $n,
		$territories = $game.map.regions[$region].territories;
		for($n = 0; $n < $territories.length; $n++) {
			if($game.map.territories[$territories[$n]].uid !== $uid) {
				return false;
			}
		}
		return true;
	}

	$(init);
})(jQuery);