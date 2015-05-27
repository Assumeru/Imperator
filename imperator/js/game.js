(function($) {
	var $game,
	$currentTab = ['territories'],
	$time = 0,
	$resizeTimeout,
	$emptyBorder,
	$dialogs = {};
	if(Number.parseInt === undefined) {
		Number.parseInt = parseInt;
	}

	function init() {
		var $window = $(window),
		$unitGraphics = Imperator.Store.getItem('unit-graphics', 'default'),
		$radialMenu = $('#radial-menu');
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
			}).on('contextmenu', function($e) {
				if($game.turn == Imperator.settings.uid) {
					$e.preventDefault();
					showRadialMenu(this.id, $e.pageX, $e.pageY);
				}
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
		$('#controls-box [data-button="forfeit"]').click(sendForfeit);
		$('#card-controls [data-button="cards"]').click(sendCards);
		$radialMenu.mouseleave(closeRadialMenu);
		$radialMenu.find('.inner').click(closeRadialMenu);
		$radialMenu.find('[data-button="stack"]').click(function() {
			showFortifyFor($radialMenu.attr('data-territory'));
			closeRadialMenu();
		});
		$radialMenu.find('[data-button="attack-to"]').click(function() {
			showAttackDialog(undefined, $radialMenu.attr('data-territory'));
			closeRadialMenu();
		});
		$radialMenu.find('[data-button="attack-from"]').click(function() {
			showAttackDialog($radialMenu.attr('data-territory'));
			closeRadialMenu();
		});
	}

	function showAttackDialog($from, $to) {
		var $selectF, $selectT, $territory, $n, $inputA, $inputM, $move,
		$ok = $(Imperator.settings.templates.okbutton),
		$cancel = $(Imperator.settings.templates.cancelbutton);
		function change() {
			var $mMax = $game.map.territories[$selectF.val()].units - 1,
			$aMax = Math.min(3, $mMax),
			$aVal = $inputA.val(),
			$mVal = $inputM.val();
			$inputA.attr('max', $aMax);
			if($aVal !== '' && !isNaN($aVal) && $aVal > 0) {
				$inputA.val(Math.min($aMax, $aVal));
			}
			$inputM.attr('max', $mMax);
			if($mVal !== '' && !isNaN($mVal) && $mVal > 0) {
				$inputM.val(Math.min($mMax, $mVal));
			}
			if($aMax < $game.map.territories[$selectT.val()].units) {
				$move.hide();
			} else {
				$move.show();
			}
		}
		if($dialogs.attackInput !== undefined) {
			$dialogs.attackInput.close();
			delete $dialogs.attackInput;
		}
		$dialogs.attackInput = Imperator.Dialog.showDialogForm(
			Imperator.settings.language.attack,
			Imperator.settings.templates.dialogformattack,
			$('<div>').append($ok).append(' ').append($cancel), true);
		$selectF = $dialogs.attackInput.message.find('[name="from"]');
		$selectT = $dialogs.attackInput.message.find('[name="to"]');
		$inputA = $dialogs.attackInput.message.find('[name="attack"]');
		$inputM = $dialogs.attackInput.message.find('[name="move"]');
		$move = $dialogs.attackInput.message.find('[data-value="move"]');
		if($from !== undefined) {
			$territory = $game.map.territories[$from];
			$selectF.append('<option value="'+$from+'">'+$territory.name+'</option>');
			$selectF.prop('disabled', true);
			for($n = 0; $n < $territory.borders.length; $n++) {
				$selectT.append('<option value="'+$territory.borders[$n]+'">'+$game.map.territories[$territory.borders[$n]].name+'</option>');
			}
			$selectT.focus();
		} else {
			$territory = $game.map.territories[$to];
			$selectT.append('<option value="'+$to+'">'+$territory.name+'</option>');
			$selectT.prop('disabled', true);
			for($n = 0; $n < $territory.borders.length; $n++) {
				$selectF.append('<option value="'+$territory.borders[$n]+'">'+$game.map.territories[$territory.borders[$n]].name+'</option>');
			}
			$selectF.focus();
		}
		$selectF.change(change);
		$selectT.change(change);
		change();
		$cancel.click(function($e) {
			$e.preventDefault();
			$dialogs.attackInput.close();
			delete $dialogs.attackInput;
		});
		$dialogs.attackInput.message.find('form').submit(function($e) {
			$e.preventDefault();
			Imperator.API.send({
				gid: $game.id,
				mode: 'game',
				type: 'attack',
				to: $selectT.val(),
				from: $selectF.val(),
				units: $inputA.val(),
				move: $inputM.val()
			});
		});
	}

	function showFortifyFor($id) {
		var $input,
		$territory = $game.map.territories[$id],
		$ok = $(Imperator.settings.templates.okbutton),
		$cancel = $(Imperator.settings.templates.cancelbutton),
		$max = $(Imperator.settings.templates.maxbutton);
		if($dialogs.stackInput !== undefined) {
			$dialogs.stackInput.close();
		}
		$dialogs.stackInput = Imperator.Dialog.showDialogForm(
			Imperator.settings.language.fortify.replace('%1$s', $territory.name),
			Imperator.settings.templates.dialogformfortify,
			$('<div>').append($ok).append(' ').append($max).append(' ').append($cancel), true);
		$input = $dialogs.stackInput.message.find('[name="stack"]');
		$input.attr('max', $game.units);
		$input.focus();
		$dialogs.stackInput.message.find('form').submit(function($e) {
			var $num = Number.parseInt($input.val(), 10);
			$e.preventDefault();
			if(isNaN($num) || $num > $game.units || $num < 1 || !window.confirm(Imperator.settings.language.confirmfortify.replace('%1$d', $num).replace('%2$s', $territory.name))) {
				$input.focus();
			} else {
				$dialogs.stackInput.close();
				delete $dialogs.stackInput;
				Imperator.API.send({
					mode: 'game',
					type: 'place-units',
					gid: $game.id,
					units: $num,
					territory: $territory.id
				});
			}
		});
		$cancel.click(function($e) {
			$e.preventDefault();
			$dialogs.stackInput.close();
			delete $dialogs.stackInput;
		});
		$max.click(function($e) {
			$e.preventDefault();
			$input.val($game.units);
		});
	}

	function showRadialMenu($id, $x, $y) {
		var $menu = $('#radial-menu'),
		$stack = $menu.find('[data-button="stack"]'),
		$moveTo = $menu.find('[data-button="move-to"]'),
		$moveFrom = $menu.find('[data-button="move-from"]'),
		$attackTo = $menu.find('[data-button="attack-to"]'),
		$attackFrom = $menu.find('[data-button="attack-from"]'),
		$territory = $game.map.territories[$id];
		$menu.find('g').attr('class', 'disabled');
		if(($game.state === STATE_TURN_START || $game.state === STATE_FORTIFY) && $game.units > 0) {
			$stack.attr('class', '');
		}
		if($game.state === STATE_COMBAT || $game.state === STATE_TURN_START) {
			if($territory.uid == Imperator.settings.uid) {
				if($territory.units > 1 && territoryBordersForeignTerritories($territory)) {
					$attackFrom.attr('class', '');
				}
			} else if(territoryCanBeAttackedBy($territory, Imperator.settings.uid)) {
				$attackTo.attr('class', '');
			}
		} else if($game.state === STATE_POST_COMBAT && $territory.uid == Imperator.settings.uid && $game.units > 0) {
			if($territory.units > 1 && territoryBordersFriendlyTerritories($territory)) {
				$moveFrom.attr('class', '');
			}
			if(territoryCanReceiveReinforcements($territory)) {
				$moveTo.attr('class', '');
			}
		}
		$menu.attr('data-territory', $id);
		$menu.css('left', $x - $menu.outerWidth() / 2);
		$menu.css('top', $y - $menu.outerHeight() / 2);
		$menu.show();
	}

	function closeRadialMenu() {
		$('#radial-menu').hide();
	}

	function sendForfeit() {
		if(window.confirm(Imperator.settings.language.forfeit)) {
			Imperator.API.send({
				mode: 'game',
				gid: $game.id,
				type: 'forfeit'
			});
		}
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
				type: 'play-cards',
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
				type: 'end-turn'
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
			if($game === undefined && $msg.regions !== undefined && $msg.territories !== undefined && $msg.players !== undefined && $msg.cards !== undefined && $msg.units !== undefined && $msg.state !== undefined && $msg.turn !== undefined) {
				$game = new Imperator.Game(Imperator.settings.gid, $msg.players, $msg.regions, $msg.territories, $msg.cards, $msg.units, $msg.state, $msg.turn);
			}
			if($game !== undefined) {
				if($msg.territories !== undefined) {
					for($id in $msg.territories) {
						if($msg.territories.units !== undefined) {
							$game.map.territories[$id].units = $msg.territories.units;
							$territoriesUpdated = true;
						}
						if($msg.territories.uid !== undefined) {
							$game.map.territories[$id].owner = $game.players[$msg.territories.uid];
							$territoriesUpdated = true;
						}
					}
				}
				if($msg.cards !== undefined) {
					for($key in $msg.cards) {
						$game.cards.setCard($key, $msg.cards[$key]);
					}
					updateCards(Imperator.Cards.CARD_NONE);
				}
				if($msg.card !== undefined && $msg.card !== Imperator.Cards.CARD_NONE) {
					$game.cards.setCard($msg.card, $game.cards.getCard($msg.card) + 1);
					updateCards($msg.card);
				}
				if($msg.turn !== undefined && $msg.turn !== $game.turn) {
					$game.turn = $msg.turn;
					updateTurn();
				}
				if($msg.state !== undefined && $msg.state !== $game.state) {
					if($msg.state == Imperator.Game.STATE_FORTIFY && $msg.units !== undefined && $dialogs.fortify !== undefined) {
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
		}
		if($territoriesUpdated) {
			updateTerritories();
		}
	}

	function updateCards($newCard) {
		var $n, $ok, $dialog,
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
			$ok = $(Imperator.settings.templates.okbutton);
			$dialog = Imperator.Dialog.showDialogForm(Imperator.settings.language.newcard,
				Imperator.settings.templates.card.replace('%1$s', $newCard).replace('%2$s', Imperator.settings.language.card[$newCard]),
				$ok, true, 'text-center');
			$ok.click(function($e) {
				$e.preventDefault();
				$dialog.close();
			});
		}
		if($dialogs.playcards !== undefined) {
			$dialogs.playcards.close();
			delete $dialogs.playcards;
			
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
			if($dialogs.endturn !== undefined) {
				$dialogs.endturn.close();
				delete $dialogs.endturn;
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
			$player = $('#players [data-player="'+$id+'"]');
			$player.find('[data-value="territories"]').text($players[$id].territories);
			$player.find('[data-value="units"]').text($players[$id].units);
			$player.find('[data-value="unitsperturn"]').text($upt.territories + $upt.regions);
			$player.find('[data-value="unitsperturn-regions"]').text($upt.regions);
			$player.find('[data-value="unitsperturn-territories"]').text($upt.territories);
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

	$(init);
})(jQuery);