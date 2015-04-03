<?php
namespace imperator\map;

class MapParser {
	private $xml;
	private $xpath;

	/**
	 * @param string $path The path to the map xml
	 */
	public function __construct($path) {
		$this->xml = new \DOMDocument();
		$this->xml->load($path);
		$this->xpath = new \DOMXPath($this->xml);
	}

	private function getOneElement($name, \DOMElement $element = null) {
		if($element === null) {
			$element = $this->xml;
		}
		return $element->getElementsByTagName($name)->item(0);
	}

	/**
	 * @return string The name of the map
	 */
	public function getName() {
		return $this->xpath->query('child::name')->item(0)->nodeValue;
	}

	/**
	 * @return int The number of players
	 */
	public function getPlayers() {
		return $this->xpath->query('child::players')->item(0)->nodeValue;
	}

	/**
	 * @return array A multidimensional array containing missions and mission distributions
	 */
	public function getMissionsAndDistribution() {
		$missionElements = $this->xpath->query('child::missions/mission');
		$missions = array();
		$distribution = array();
		foreach($missionElements as $mission) {
			$id = (int)$mission->getAttribute('missionId');
			$availability = (int)$mission->getAttribute('availability');
			while($availability > 0) {
				$distribution[] = $id;
				$availability--;
			}
			if($mission->hasAttribute('class')) {
				$class = '\\imperator\\mission\\'.$mission->getAttribute('class');
				$arguments = array($id);
				foreach($this->xpath->query('child::argument', $mission) as $argument) {
					$arguments[] = $this->parseArgument($argument);
				}
				$missions[$id] = $this->callWithArguments($class, $arguments);
			} else {
				$name = $this->getOneElement('name', $mission)->nodeValue;
				$description = $this->getOneElement('description', $mission)->nodeValue;
				$conditions = array();
				foreach($this->xpath->query('child::conditions/condition', $mission) as $condition) {
					$conditions[] = $this->getMissionCondition($condition);
				}
				$missions[$id] = new \imperator\mission\Mission($id, $name, $description, $conditions);
			}
		}
		return array($missions, $distribution);
	}

	private function callWithArguments($class, $arguments) {
		$numArguments = count($arguments);
		if($numArguments === 0) {
			return new $class();
		} else if($numArguments === 1) {
			return new $class($arguments[0]);
		} else if($numArguments === 2) {
			return new $class($arguments[0], $arguments[1]);
		}
		return new $class($arguments);
	}

	private function parseArgument($argument) {
		$value = $argument->nodeValue;
		if($argument->hasAttribute('type')) {
			$type = $argument->getAttribute('type');
			if($type == 'int') {
				return (int)$value;
			} else if($type == 'float') {
				return (float)$value;
			}
		}
		return $value;
	}

	private function getMissionCondition(\DOMElement $condition) {
		$class = '\\imperator\\mission\\'.$condition->getAttribute('class');
		$arguments = array();
		foreach($condition->getElementsByTagName('argument') as $argument) {
			$arguments[] = $this->parseArgument($argument);
		}
		return $this->callWithArguments($class, $arguments);
	}

	private function getRegions() {
		$regionElements = $this->xpath->query('child::regions/region');
		$regions = array();
		foreach($regionElements as $region) {
			$id = $region->getAttribute('id');
			$name = $this->getOneElement('name', $region)->nodeValue;
			$units = (int)$this->getOneElement('units', $region);
			$regions[$id] = new Region($id, $name, $units);
		}
		return $regions;
	}

	private function addRegions(\DOMElement $element, array $regions, Territory $territory) {
		$regionElements = $this->xpath->query('child::regions/region', $element);
		foreach($regionElements as $region) {
			$id = $region->nodeValue;
			$regions[$id]->addTerritory($territory);
			$territory->addRegion($regions[$id]);
		}
	}

	/**
	 * @return array A multidimensional array containing territories and regions
	 */
	public function getTerritoriesAndRegions(\imperator\Game $game = null) {
		$territoryElements = $this->xpath->query('child::territories/territory');
		$territories = array();
		$regions = $this->getRegions();
		foreach($territoryElements as $territory) {
			$id = $territory->getAttribute('id');
			$name = $this->getOneElement('name', $territory)->nodeValue;
			$territories[$id] = new Territory($id, $name);
			$this->addRegions($territory, $regions, $territories[$id]);
			$territories[$id]->setGame($game);
		}
		foreach($territoryElements as $territory) {
			$id = $territory->getAttribute('id');
			$borderElements = $this->getOneElement('borders', $territory)->getElementsByTagName('border');
			foreach($borderElements as $border) {
				$territories[$id]->addBorder($territories[$border->nodeValue]);
			}
		}
		return array($territories, $regions);
	}

	public function getDescription() {
		$descriptionElements = $this->xpath->query('child::description');
		$descriptions = array();
		foreach($descriptionElements as $description) {
			$lang = $description->getAttribute('lang');
			$descriptions[strtolower($lang)] = $description->nodeValue;
		}
		return $descriptions;
	}
}