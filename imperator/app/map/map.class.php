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

	private function __construct($path) {
		$this->id = (int)(basename($path, '.xml'));
		$this->path = $path;
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
		$this->game = $game;
		foreach($this->territories as $territory) {
			$territory->setGame($game);
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
	 * @return \imperator\mission\Mission[]
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
	 * @param User $user The user whose territories you want
	 * @return Territory[] An array of territories
	 */
	public function getTerritoriesFor(\imperator\User $user) {
		$territories = array();
		foreach($this->getTerritories() as $territory) {
			if($territory->getOwner() == $user) {
				$territories[] = $territory;
			}
		}
		return $territories;
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

	/**
	 * Returns a new instance of the specified map ID.
	 * 
	 * @param int $mapId The ID of the map
	 * @return Map A new map without users
	 */
	public static function getInstance($mapId) {
		$path = Imperator::getSettings()->getBasePath().'/etc/maps/'.$mapId.'.xml';
		if(file_exists($path)) {
			return new Map($path);
		}
		return null;
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
		Imperator::getDatabaseManager()->getTable('Territories')->saveTerritories($territories);
	}

	public function distributeMissions(array $players) {
		$missions = $this->getMissions();
		$missionDistribution = $this->getMissionDistribution();
		shuffle($missionDistribution);
		$numPlayers = count($players);
		foreach($players as $player) {
			$mission = $missions[array_pop($missionDistribution)];
			if($mission->containsEliminate()) {
				$index = mt_rand(0, $numPlayers-2);
				$target = $players[$index];
				if($player->equals($target)) {
					$target = $players[$numPlayers-1];
				}
				$mission->setUid($target->getId());
			}
			$player->setMission($mission);
		}
		Imperator::getDatabaseManager()->getTable('GamesJoined')->saveMissions($players);
	}

	/**
	 * @param \imperator\User $user
	 * @return bool
	 */
	public function isOwnedBy(\imperator\User $user) {
		foreach($this->getTerritories() as $territory) {
			if(!$territory->getOwner()->equals($user)) {
				return false;
			}
		}
		return true;
	}
}