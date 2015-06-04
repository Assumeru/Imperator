Imperator.Game = function($id, $players, $regions, $territories, $cards, $units, $state, $turn) {
	var $this = this;

	function getPlayersFromJSON($json) {
		var $id,
		$players = {};
		for($id in $json) {
			$players[$id] = new Imperator.Player($this, $json[$id].id, $json[$id].name, $json[$id].color, $json[$id].link);
		}
		return $players;
	}

	function getTerritoriesFromJSON($json) {
		var $id, $n,
		$territories = {};
		for($id in $json) {
			$territories[$id] = new Imperator.Territory($json[$id].id, $json[$id].name, $this.players[$json[$id].uid], $json[$id].units);
		}
		for($id in $json) {
			for($n = 0; $n < $json[$id].borders.length; $n++) {
				$territories[$id].borders.push($territories[$json[$id].borders[$n]]);
			}
		}
		return $territories;
	}

	function getRegionsFromJSON($json) {
		var $id, $n,
		$regions = {};
		for($id in $json) {
			$regions[$id] = new Imperator.Region($json[$id].id, $json[$id].units);
			for($n = 0; $n < $json[$id].territories.length; $n++) {
				$regions[$id].territories.push($this.map.territories[$json[$id].territories[$n]]);
				$this.map.territories[$json[$id].territories[$n]].regions.push($regions[$id]);
			}
		}
		return $regions;
	}

	this.id = $id;
	this.players = getPlayersFromJSON($players);
	this.map = {
		territories: getTerritoriesFromJSON($territories)
	};
	this.map.regions = getRegionsFromJSON($regions);
	this.cards = new Imperator.Cards($cards);
	this.units = $units;
	this.state = $state;
	this.turn = this.players[$turn];
};
Imperator.Game.STATE_TURN_START = 0;
Imperator.Game.STATE_FORTIFY = 1;
Imperator.Game.STATE_COMBAT = 2;
Imperator.Game.STATE_POST_COMBAT = 3;
Imperator.Game.STATE_FINISHED = 4;

Imperator.Player = function($game, $id, $name, $color, $link) {
	this.game = $game;
	this.id = $id;
	this.name = $name;
	this.color = $color;
	this.link = $link;
};
Imperator.Player.prototype.getUnitsPerTurnFromTerritories = function($optionalNumberOfTerritories) {
	var $id,
	$territories = 0;
	if($optionalNumberOfTerritories !== undefined) {
		$territories = $optionalNumberOfTerritories;
	} else {
		for($id in this.game.map.territories) {
			if(this.game.map.territories[$id].owner == this) {
				$territories++;
			}
		}
	}
	return Math.max(Math.floor($territories / 3), 3);
};
Imperator.Player.prototype.getUnitsPerTurnFromRegions = function() {
	var $id, $out = 0;
	for($id in this.game.map.regions) {
		if(this.game.map.regions[$id].isOwnedBy(this)) {
			$out += this.game.map.regions[$id].units;
		}
	}
	return $out;
};
Imperator.Player.prototype.getUnitsPerTurn = function() {
	return this.getUnitsPerTurnFromRegions() + this.getUnitsPerTurnFromTerritories();
};

Imperator.Territory = function($id, $name, $user, $units) {
	this.id = $id;
	this.name = $name;
	this.owner = $user;
	this.units = $units;
	this.borders = [];
	this.regions = [];
};
Imperator.Territory.prototype.bordersEnemyTerritory = function() {
	for(var $n = 0; $n < this.borders.length; $n++) {
		if(this.borders[$n].owner != this.owner) {
			return true;
		}
	}
	return false;
};
Imperator.Territory.prototype.bordersFriendlyTerritory = function() {
	for(var $n = 0; $n < this.borders.length; $n++) {
		if(this.borders[$n].owner == this.owner) {
			return true;
		}
	}
	return false;
};
Imperator.Territory.prototype.canBeAttackedBy = function($player) {
	for(var $n = 0; $n < this.borders.length; $n++) {
		if(this.borders[$n].owner == $player && this.borders[$n].units > 1) {
			return true;
		}
	}
	return false;
};
Imperator.Territory.prototype.canReceiveReinforcements = function() {
	return this.canBeAttackedBy(this.owner);
};

Imperator.Region = function($id, $units) {
	this.id = $id;
	this.units = $units;
	this.territories = [];
};
Imperator.Region.prototype.isOwnedBy = function($player) {
	for(var $n = 0; $n < this.territories.length; $n++) {
		if(this.territories[$n].owner != $player) {
			return false;
		}
	}
	return true;
};

Imperator.Cards = function($json) {
	this.artillery = $json[Imperator.Cards.CARD_ARTILLERY];
	this.cavalry = $json[Imperator.Cards.CARD_CAVALRY];
	this.infantry = $json[Imperator.Cards.CARD_INFANTRY];
	this.jokers = $json[Imperator.Cards.CARD_JOKER];
};
Imperator.Cards.CARD_NONE = -1;
Imperator.Cards.CARD_ARTILLERY = 0;
Imperator.Cards.CARD_CAVALRY = 1;
Imperator.Cards.CARD_INFANTRY = 2;
Imperator.Cards.CARD_JOKER = 3;
Imperator.Cards.prototype.setCard = function($card, $amount) {
	if($card == Imperator.Cards.CARD_ARTILLERY) {
		this.artillery = $amount;
	} else if($card == Imperator.Cards.CARD_CAVALRY) {
		this.cavalry = $amount;
	} else if($card == Imperator.Cards.CARD_INFANTRY) {
		this.infantry = $amount;
	} else if($card == Imperator.Cards.CARD_JOKER) {
		this.jokers = $amount;
	}
};
Imperator.Cards.prototype.getCard = function($card) {
	if($card == Imperator.Cards.CARD_ARTILLERY) {
		return this.artillery;
	} else if($card == Imperator.Cards.CARD_CAVALRY) {
		return this.cavalry;
	} else if($card == Imperator.Cards.CARD_INFANTRY) {
		return this.infantry;
	} else if($card == Imperator.Cards.CARD_JOKER) {
		return this.jokers;
	}
};
Imperator.Cards.prototype.canPlayCombination = function($units) {
	if($units == 4) {
		return this.artillery + this.jokers >= 3;
	} else if($units == 6) {
		return this.infantry + this.jokers >= 3;
	} else if($units == 8) {
		return this.cavalry + this.jokers >= 3;
	}
	return (this.artillery + this.infantry + this.cavalry >= 1 && this.jokers >= 2)
		|| (this.artillery >= 1 && this.infantry >= 1 && this.cavalry >= 1)
		|| (this.jokers >= 1
			&& ((this.artillery >= 1 && this.infantry >= 1)
			|| (this.artillery >= 1 && this.cavalry >= 1)
			|| (this.infantry >= 1 && this.cavalry >= 1)));
};

Imperator.Attack = function($attacker, $defender, $roll) {
	this.attacker = $attacker;
	this.defender = $defender;
	this.roll = $roll;
};