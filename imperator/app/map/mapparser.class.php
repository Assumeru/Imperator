<?php
namespace imperator\map;

class MapParser {
	private $xml;

	/**
	 * @param string $path The path to the map xml
	 */
	public function __construct($path) {
		$this->xml = new \DOMDocument();
		$this->xml->load($path);
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
		return $this->getOneElement('name')->nodeValue;
	}

	/**
	 * @return int The number of players
	 */
	public function getPlayers() {
		return (int)($this->getOneElement('players')->nodeValue);
	}

	/**
	 * @return array A multidimensional array containing missions and mission distributions
	 */
	public function getMissionsAndDistribution() {
		$missionElements = $this->getOneElement('missions')->getElementsByTagName('mission');
		$missions = array();
		$distribution = array();
		foreach($missionElements as $mission) {
			$id = (int)$mission->getAttribute('missionId');
			$availability = (int)$mission->getAttribute('availability');
			while($availability > 0) {
				$distribution[] = $id;
				$availability--;
			}
			$name = $this->getOneElement('name', $mission)->nodeValue;
			$description = $this->getOneElement('description', $mission)->nodeValue;
			$conditions = array();
			foreach($this->getOneElement('conditions', $mission)->getElementsByTagName('condition') as $condition) {
				$conditions[] = $this->getMissionCondition($condition);
			}
			$missions[$id] = new \imperator\mission\Mission($name, $description, $conditions);
		}
		return array($missions, $distribution);
	}

	private function getMissionCondition(\DOMElement $condition) {
		$class = '\\imperator\\mission\\'.$condition->getAttribute('class');
		$arguments = array();
		foreach($condition->getElementsByTagName('argument') as $argument) {
			$value = $argument->nodeValue;
			if($argument->hasAttribute('type')) {
				$type = $argument->getAttribute('type');
				if($type == 'int') {
					$value = (int)$value;
				} else if($type == 'float') {
					$value = (float)$value;
				}
			}
			$arguments[] = $value;
		}
		if(count($arguments) === 1) {
			return new $class($arguments[0]);
		} else {
			return new $class($arguments);
		}
	}

	private function getRegions() {
		$regionElements = $this->getOneElement('regions')->getElementsByTagName('region');
		$regions = array();
		foreach($regionElements as $region) {
			$id = $region->getAttribute('id');
			$name = $this->getOneElement('name', $region);
			$units = (int)$this->getOneElement('units', $region);
			$regions[$id] = new Region($id, $name, $units);
		}
		return $regions;
	}

	private function addRegions(\DOMElement $element, array $regions, Territory $territory) {
		$regionElements = $this->getOneElement('regions', $element)->getElementsByTagName('region');
		foreach($regionElements as $region) {
			$id = $region->nodeValue;
			$regions[$id]->addTerritory($territory);
			$territory->addRegion($regions[$id]);
		}
	}

	/**
	 * @return array A multidimensional array containing territories and regions
	 */
	public function getTerritoriesAndRegions() {
		$territoryElements = $this->getOneElement('territories')->getElementsByTagName('territory');
		$territories = array();
		$regions = $this->getRegions();
		foreach($territoryElements as $territory) {
			$id = $territory->getAttribute('id');
			$name = $this->getOneElement('name', $territory)->nodeValue;
			$territories[$id] = new Territory($id, $name);
			$this->addRegions($territory, $regions, $territories[$id]);
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
}