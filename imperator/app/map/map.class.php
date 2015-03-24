<?php
namespace imperator\map;
use imperator\Imperator;

class Map {
	private static $maps = null;

	private $path;
	private $id;
	private $players = null;
	private $name = null;
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
	 * @return int
	 */
	public function getPlayers() {
		if($this->players === null) {
			$this->initFromXML();
		}
		return $this->players;
	}

	private function initFromXML($loadMissions = false, $loadTerritories = false) {
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
			list($this->territories, $this->regions) = $xml->getTerritoriesAndRegions();
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
}