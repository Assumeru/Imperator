<?php
namespace imperator\map;
use imperator\Imperator;

class Map {
	private static $maps = null;

	private $path;
	private $id;
	private $game = null;
	private $players = null;
	private $name = null;
	private $description = null;
	private $territories = null;
	private $regions = null;
	private $missions = null;
	private $missionDistribution = null;

	/**
	 * Creates a new, empty map.
	 * 
	 * @param int|path $map Either the map ID or the path to the map's xml file
	 * @throws \InvalidArgumentException
	 */
	public function __construct($map) {
		if(is_numeric($map)) {
			$this->id = (int)$map;
			$this->path = Imperator::getSettings()->getBasePath().'/etc/maps/'.$map.'.xml';
		} else {
			$this->id = (int)(basename($map, '.xml'));
			$this->path = $map;
		}
		if(!file_exists($this->path)) {
			throw new \InvalidArgumentException('Map "'.$this->id.'" not found.');
		}
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName() {
		if($this->name === null) {
			$this->initFromXML();
		}
		return $this->name;
	}

	/**
	 * @param string $lang The language of the description (defaults to en-US)
	 * @return string
	 */
	public function getDescription($lang = 'en-US') {
		if($this->description === null) {
			$this->initFromXML(false, false, true);
		}
		$lang = strtolower($lang);
		if(isset($this->description[$lang])) {
			return $this->description[$lang];
		} else {
			$matchingDescription = $this->getMatchingDescription($lang);
			if($matchingDescription !== null) {
				//cache
				$this->description[$lang] = $matchingDescription;
				return $matchingDescription;
			}
		}
		return $this->description[0];
	}

	private function getMatchingDescription($lang) {
		$maxMatches = 0;
		$bestMatch = '';
		$langBits = explode('-', $lang);
		$langLength = count($langBits);
		foreach($this->description as $key => $value) {
			$key = strtolower($key);
			$keyBits = explode('-', $key);
			for($n = 0; $n < $langLength && $n < count($keyBits); $n++) {
				if($langBits[$n] != $keyBits[$n]) {
					break;
				} else if($n > $maxMatches) {
					$maxMatches = $n;
					$bestMatch = $value;
				}
			}
		}
		if($maxMatches > 0) {
			return $bestMatch;
		}
		return null;
	}

	public function setGame(\imperator\Game $game) {
		if($this->game != $game) {
			$this->game = $game;
			foreach($this->territories as $territory) {
				$territory->setGame($game);
			}
		}
	}

	/**
	 * @return \imperator\Game
	 */
	public function getGame() {
		return $this->game;
	}

	private function getMissionDistribution() {
		if($this->missionDistribution === null) {
			$this->initFromXML(true);
		}
		return $this->missionDistribution;
	}

	/**
	 * @return \imperator\mission\MapMission[]
	 */
	public function getMissions() {
		if($this->missions === null) {
			$this->initFromXML(true);
		}
		return $this->missions;
	}

	/**
	 * @return int
	 */
	public function getPlayers() {
		if($this->players === null) {
			$this->initFromXML();
		}
		return $this->players;
	}

	private function initFromXML($loadMissions = false, $loadTerritories = false, $loadDescription = true) {
		$xml = new MapParser($this->path);
		if($this->name === null) {
			$this->name = $xml->getName();
		}
		if($this->players === null) {
			$this->players = $xml->getPlayers();
		}
		if($loadMissions) {
			list($this->missions, $this->missionDistribution) = $xml->getMissionsAndDistribution();
		}
		if($loadTerritories) {
			list($this->territories, $this->regions) = $xml->getTerritoriesAndRegions($this->game);
		}
		if($loadDescription) {
			$this->description = $xml->getDescription();
		}
	}

	/**
	 * @return Territory[]
	 */
	public function getTerritories() {
		if($this->territories === null) {
			$this->initFromXML(false, true);
		}
		return $this->territories;
	}

	/**
	 * @return Region[]
	 */
	public function getRegions() {
		if($this->regions === null) {
			$this->initFromXML(false, true);
		}
		return $this->regions;
	}

	/**
	 * Returns all territories controlled by a user.
	 * 
	 * @param \imperator\game\Player $user The user whose territories you want
	 * @return Territory[] An array of territories
	 */
	public function getTerritoriesFor(\imperator\game\Player $user) {
		$territories = array();
		foreach($this->getTerritories() as $territory) {
			if($territory->getOwner() == $user) {
				$territories[] = $territory;
			}
		}
		return $territories;
	}

	/**
	 * Checks if a player owns territories.
	 * 
	 * @param \imperator\game\Player $user
	 * @return boolean
	 */
	public function playerHasTerritories(\imperator\game\Player $user) {
		foreach($this->getTerritories() as $territory) {
			if($territory->getOwner() == $user) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns all maps in an associative array.
	 * 
	 * @return Map[] All maps
	 */
	public static function getMaps() {
		if(self::$maps == null) {
			self::loadMaps();
		}
		return self::$maps;
	}

	private static function loadMaps() {
		$files = glob(Imperator::getSettings()->getBasePath().'/etc/maps/*.xml');
		$maps = array();
		foreach($files as $file) {
			$maps[basename($file, '.xml')] = new Map($file);
		}
		self::$maps = $maps;
	}

	public function distributeTerritories(array $players) {
		$territories = array_values($this->getTerritories());
		shuffle($territories);
		$numNations = count($territories) / count($players);
		$n = 0;
		foreach($players as $player) {
			for($i=0; $i < $numNations; $i++, $n++) {
				$territories[$n]->setOwner($player);
				$territories[$n]->setUnits(3);
			}
		}
		Imperator::getDatabaseManager()->getTerritoriesTable()->saveTerritories($territories);
	}

	public function distributeMissions(array $players) {
		$missions = $this->getMissions();
		$missionDistribution = $this->getMissionDistribution();
		shuffle($missionDistribution);
		$numPlayers = count($players);
		foreach($players as $player) {
			$mission = new \imperator\mission\PlayerMission($missions[array_pop($missionDistribution)], $player);
			if($mission->containsEliminate()) {
				$index = mt_rand(0, $numPlayers-2);
				$target = $players[$index];
				if($player == $target) {
					$target = $players[$numPlayers-1];
				}
				$mission->setUid($target->getId());
			}
			$player->setMission($mission);
		}
		Imperator::getDatabaseManager()->getGamesJoinedTable()->saveMissions($players);
	}

	/**
	 * @return Territory
	 */
	public function getTerritoryById($id) {
		if($this->territories === null) {
			$this->initFromXML(false, true);
		}
		if(isset($this->territories[$id])) {
			return $this->territories[$id];
		}
		return null;
	}
}