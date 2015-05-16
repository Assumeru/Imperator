(function($) {
	var $currentTab = ['territories'],
	$game = {
		map: {
			territories: {},
			regions: {}
		},
		players: {},
		id: Imperator.settings.gid,
		turn: -1,
		state: -1,
		units: 0,
		cards: {
			0: 0,
			1: 0,
			2: 0,
			3: 0
		}
	},
	$time = 0,
	$resizeTimeout,
	$emptyBorder,
	$dialogs = {},
	STATE_TURN_START = 0,
	STATE_FORTIFY = 1,
	STATE_COMBAT = 2,
	STATE_POST_COMBAT = 3,
	STATE_FINISHED = 4,
	CARD_NONE = -1,
	CARD_ARTILLERY = 0,
	CARD_CAVALRY = 1,
	CARD_INFANTRY = 2,
	CARD_JOKER = 3;
	if(Number.parseInt === undefined) {
		Number.parseInt = parseInt;
	}

	function init() {
		var $window = $(window),
		$unitGraphics = Imperator.Store.getItem('unit-graphics', 'default');
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
		$('#settings input[name="unitgraphics"][value="'+$unitGraphics+'"]').prop('checked', true);
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
		$('#settings input[name="unitgraphics"]').change(setUnitGraphics)
		$('#regions [data-button="highlight"]').click(highlightRegion);
		$('#controls-box [data-button="stack"]').click(sendFortify);
		$('#controls-box [data-button="endturn"]').click(sendEndTurn);
		$('#card-controls [data-button="cards"]').click(sendCards);
	}

	function sendCards() {
		var $this = $(this),
		$num = $this.attr('data-value');
		if($game.turn == Imperator.settings.uid && ($game.state === STATE_TURN_START || $game.state === STATE_FORTIFY)) {
			if($dialogs.playcards !== undefined) {
				$dialogs.playcards.close();
			}
			$dialogs.playcards = Imperator.Dialog.showDialog(Imperator.settings.language.wait, $('<p class="loading"></p>').text(Imperator.settings.language.contacting), false, 'loading');
			Imperator.API.send({
				gid: $game.id,
				mode: 'game',
				action: 'play-cards',
				units: $num
			});
		}
	}

	function sendEndTurn() {
		if($game.turn == Imperator.settings.uid) {
			if($dialogs.endturn !== undefined) {
				$dialogs.endturn.close();
			}
			$dialogs.endturn = Imperator.Dialog.showDialog(Imperator.settings.language.wait, $('<p class="loading"></p>').text(Imperator.settings.language.contacting), false, 'loading');
			Imperator.API.send({
				gid: $game.id,
				mode: 'game',
				action: 'end-turn'
			});
		}
	}

	function sendFortify() {
		if($game.state == STATE_TURN_START) {
			if($dialogs.fortify !== undefined) {
				$dialogs.fortify.close();
			}
			$dialogs.fortify = Imperator.Dialog.showDialog(Imperator.settings.language.wait, $('<p class="loading"></p>').text(Imperator.settings.language.contacting), false, 'loading');
			Imperator.API.send({
				gid: $game.id,
				mode: 'game',
				type: 'fortify'
			});
		}
	}

	function highlightRegion() {
		var $n,
		$this = $(this),
		$id = $this.attr('data-region'),
		$region = $game.map.regions[$id];
		if($this.attr('data-highlight') === 'true') {
			$('#map svg g[id]').attr('class', '');
			$this.attr('data-highlight', 'false');
		} else {
			$('#map svg g[id]').attr('class', 'active border');
			for($n = 0; $n < $region.territories.length; $n++) {
				$('#'+$region.territories[$n]).attr('class', 'active');
			}
			$('#regions .btn[data-highlight="true"]').attr('data-highlight', 'false');
			$this.attr('data-highlight', 'true');
		}
	}

	function setUnitGraphics() {
		var $this = $(this);
		Imperator.Store.setItem('unit-graphics', $this.val());
		updateUnitBoxes();
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
			if($msg.cards !== undefined) {
				$game.cards = $msg.cards;
				updateCards(CARD_NONE);
			}
			if($msg.card !== undefined && $msg.card !== CARD_NONE) {
				$game.cards[$msg.card]++;
				updateCards($msg.card);
			}
			if($msg.turn !== undefined && $msg.turn !== $game.turn) {
				$game.turn = $msg.turn;
				updateTurn();
			}
			if($msg.state !== undefined && $msg.state !== $game.state) {
				if($msg.state == STATE_FORTIFY && $msg.units !== undefined && $dialogs.fortify !== undefined) {
					$dialogs.fortify.close();
					delete $dialogs.fortify;
				}
				$game.state = $msg.state;
				updateState();
			}
			if($msg.units !== undefined) {
				$game.units = $msg.units;
				updateUnits();
			}
		}
		if($territoriesUpdated) {
			updateTerritories();
		}
	}

	function updateCards($newCard) {
		var $n,
		$cards = $('#cards [data-value="card-list"]'),
		$controls = $('#card-controls'),
		$buttons = {
			4: $controls.find('[data-button="cards"][data-value="4"]'),
			6: $controls.find('[data-button="cards"][data-value="6"]'),
			8: $controls.find('[data-button="cards"][data-value="8"]'),
			10: $controls.find('[data-button="cards"][data-value="10"]')
		},
		$artillery = Imperator.settings.templates.card.replace('%1$s', CARD_ARTILLERY).replace('%2$s', Imperator.settings.language.card[CARD_ARTILLERY]),
		$infantry = Imperator.settings.templates.card.replace('%1$s', CARD_INFANTRY).replace('%2$s', Imperator.settings.language.card[CARD_INFANTRY]),
		$cavalry = Imperator.settings.templates.card.replace('%1$s', CARD_CAVALRY).replace('%2$s', Imperator.settings.language.card[CARD_CAVALRY]),
		$joker = Imperator.settings.templates.card.replace('%1$s', CARD_JOKER).replace('%2$s', Imperator.settings.language.card[CARD_JOKER]);
		if($newCard !== CARD_NONE) {
			Imperator.Dialog.showDialog(Imperator.settings.language.newcard, Imperator.settings.templates.card.replace('%1$s', $newCard).replace('%2$s', Imperator.settings.language.card[$newCard]), true, 'text-center');
		}
		$cards.empty();
		for($n = 0; $n < $game.cards[CARD_ARTILLERY]; $n++) {
			$cards.append($artillery);
		}
		for($n = 0; $n < $game.cards[CARD_INFANTRY]; $n++) {
			$cards.append($infantry);
		}
		for($n = 0; $n < $game.cards[CARD_CAVALRY]; $n++) {
			$cards.append($cavalry);
		}
		for($n = 0; $n < $game.cards[CARD_JOKER]; $n++) {
			$cards.append($joker);
		}
		for($n in $buttons) {
			if(canPlayCardsFor($n)) {
				$buttons[$n].show();
			} else {
				$buttons[$n].hide();
			}
		}
	}

	function canPlayCardsFor($units) {
		if($units == 4) {
			return $game.cards[CARD_ARTILLERY] + $game.cards[CARD_JOKER] >= 3;
		} else if($units == 6) {
			return $game.cards[CARD_INFANTRY] + $game.cards[CARD_JOKER] >= 3;
		} else if($units == 8) {
			return $game.cards[CARD_CAVALRY] + $game.cards[CARD_JOKER] >= 3;
		}
		return ($game.cards[CARD_ARTILLERY] + $game.cards[CARD_INFANTRY] + $game.cards[CARD_CAVALRY] >= 1 && $game.cards[CARD_JOKER] >= 2)
			|| ($game.cards[CARD_ARTILLERY] >= 1 && $game.cards[CARD_INFANTRY] >= 1 && $game.cards[CARD_CAVALRY] >= 1)
			|| ($game.cards[CARD_JOKER] >= 1
				&& (($game.cards[CARD_ARTILLERY] >= 1 && $game.cards[CARD_INFANTRY] >= 1)
				|| ($game.cards[CARD_ARTILLERY] >= 1 && $game.cards[CARD_CAVALRY] >= 1)
				|| ($game.cards[CARD_INFANTRY] >= 1 && $game.cards[CARD_CAVALRY] >= 1)));
	}

	function updateUnits() {
		var $box = $('#controls-box'),
		$unitsF = $box.find('[data-value="units-left-fortify"] .number'),
		$unitsM = $box.find('[data-value="units-left-move"] .number');
		$unitsF.text(0);
		$unitsM.text(0);
		if($game.state === STATE_TURN_START || $game.state === STATE_FORTIFY) {
			$unitsF.text($game.units);
		} else if($game.state === STATE_POST_COMBAT) {
			$unitsM.text($game.units);
		}
	}

	function updateTurn() {
		var $btn,
		$a = $('#controls-box .user'),
		$player = $game.players[$game.turn];
		$a.css('color', '#'+$player.color);
		$a.text($player.name);
		$a.attr('href', $player.link);
		if($game.turn === Imperator.settings.uid) {
			$('body').addClass('my-turn');
			$btn = $('#turn-controls [data-toggle="collapse"]');
			if($btn.hasClass('collapsed')) {
				$btn.click();
			}
		} else {
			if($dialog.endturn !== undefined) {
				$dialog.endturn.close();
				delete $dialog.endturn;
			}
			$('body').removeClass('my-turn');
		}
	}

	function updateState() {
		var $box = $('#controls-box'),
		$stack = $box.find('[data-button="stack"]'),
		$move = $box.find('[data-button="move"]'),
		$unitsF = $box.find('[data-value="units-left-fortify"]'),
		$unitsM = $box.find('[data-value="units-left-move"]');
		$stack.css('display', 'none');
		$move.css('display', 'none');
		$unitsF.css('display', 'none');
		$unitsM.css('display', 'none');
		if($game.state === STATE_TURN_START || $game.state === STATE_FORTIFY) {
			$unitsF.css('display', '');
		}
		if($game.state === STATE_TURN_START) {
			$stack.css('display', '');
		} else if($game.state === STATE_COMBAT) {
			$move.css('display', '');
		} else if($game.state === STATE_POST_COMBAT) {
			$unitsM.css('display', '');
		}
	}

	function updateTerritories() {
		var $id, $territory, $player, $upt, $players = [];
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
			$upt = {
				territories: getUnitsPerTurnFromTerritoriesFor($id, $players[$id].territories),
				regions: getUnitsPerTurnFromRegionsFor($id),
			};
			$player = $('#players *[data-player="'+$id+'"]');
			$player.find('*[data-value="territories"]').text($players[$id].territories);
			$player.find('*[data-value="units"]').text($players[$id].units);
			$player.find('*[data-value="unitsperturn"]').text($upt.territories + $upt.regions);
			$player.find('*[data-value="unitsperturn-regions"]').text($upt.regions);
			$player.find('*[data-value="unitsperturn-territories"]').text($upt.territories);
			if(Imperator.settings.uid == $id) {
				$('#controls-box [data-button="stack"] .number').text($upt.territories);
			}
		}
		updateRegionDivision();
		updateUnitBoxes();
	}

	function updateUnitBoxes() {
		var $id, $units,
		$unitGraphics = Imperator.Store.getItem('unit-graphics', 'default');
		for($id in $game.map.territories) {
			Imperator.Map.updateUnitBox($unitGraphics, $id, $game.map.territories[$id].units);
		}
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
		return getUnitsPerTurnFromTerritoriesFor($uid) + getUnitsPerTurnFromRegionsFor($uid);
	}

	function getUnitsPerTurnFromTerritoriesFor($uid, $numberOfTerritories) {
		var $id,
		$territories = 0;
		if($numberOfTerritories !== undefined) {
			$territories = $numberOfTerritories;
		} else {
			for($id in $game.map.territories) {
				if($game.map.territories[$id].uid == $uid) {
					$territories++;
				}
			}
		}
		return Math.max(Math.floor($territories / 3), 3);
	}

	function getUnitsPerTurnFromRegionsFor($uid) {
		var $id, $out = 0;
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