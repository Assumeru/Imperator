<?php
namespace imperator\test;
use imperator\Imperator;

require_once '../imperator/app/imperator.class.php';
require_once './testlogger.class.php';

$logger = new TestLogger();
$maps = glob(Imperator::getSettings()->getBasePath().'/etc/maps/*.xml');
echo '<pre>';
foreach($maps as $map) {
	$validator = new MapValidator($logger, $map);
	try {
		$validator->validate();
	} catch (\Exception $e) {
		$logger->log(\imperator\Logger::LEVEL_WARNING, $e);
	}
}
echo '</pre>';

class MapValidator {
	private $logger;
	private $file;
	private $numErrors;
	private $xpath;

	public function __construct(\imperator\Logger $logger, $file) {
		$this->logger = $logger;
		$this->file = realpath($file);
	}

	public function validate() {
		$this->numErrors = 0;
		$this->logger->log(\imperator\Logger::LEVEL_INFO, 'Validating '.$this->file);
		$this->validateXSD();
		if($this->xpath) {
			$this->validateXML();
			if($this->numErrors === 0) {
				$this->instantiateMap();
			}
		}
		if($this->numErrors > 0) {
			$this->w($this->file . ' is invalid');
		} else {
			$this->logger->log(\imperator\Logger::LEVEL_INFO, $this->file.' is a valid map');
		}
	}

	private function w($msg) {
		$this->numErrors++;
		$this->logger->log(\imperator\Logger::LEVEL_WARNING, $msg);
	}

	private function validateXSD() {
		$xml = new \DOMDocument();
		if($xml->load($this->file) === false) {
			throw new \Exception('Failed to load file');
		}
		libxml_use_internal_errors(true);
		if(!$xml->schemaValidate(Imperator::getSettings()->getBasePath().'/app/map/map.xsd')) {
			$errors = libxml_get_errors();
			foreach($errors as $error) {
				$this->w(new \ErrorException($error->message, $error->code, $error->level, $error->file, $error->line));
			}
			libxml_clear_errors();
		} else {
			$this->logger->log(\imperator\Logger::LEVEL_INFO, 'Passed XML schema validation');
			$this->xpath = new \DOMXPath($xml);
		}
	}

	private function validateXML() {
		$name = $this->xpath->query('child::name')->item(0)->nodeValue;
		if(empty($name)) {
			$this->w('Map name should not be empty');
		}
		$territoryElements = $this->xpath->query('child::territories/territory');
		$territoryIds = array();
		$borders = array();
		$regions = array();
		foreach($territoryElements as $territory) {
			$id = $territory->getAttribute('id');
			if(empty($id)) {
				$this->w('Map id should not be empty');
			} else {
				if(isset($territoryIds[$id])) {
					$this->w('Map ids should be unique: "'.$id.'" already defined');
				}
				$territoryIds[$id] = true;
			}
			$name = $this->xpath->query('child::name', $territory)->item(0)->nodeValue;
			if(empty($name)) {
				$this->w('Territory name should not be empty: '.$id);
			}
			$borderElements = $this->xpath->query('child::borders', $territory)->item(0)->getElementsByTagName('border');
			foreach($borderElements as $border) {
				$bId = $border->nodeValue;
				if(empty($bId)) {
					$this->w('Territory border should not be empty: '.$id);
				} else {
					$borders[] = $bId;
					if($bId == $id) {
						$this->w('Territories cannot border themselves: '.$bId);
					}
				}
			}
			$regionElements = $this->xpath->query('child::regions/region', $territory);
			foreach($regionElements as $region) {
				$rId = $region->nodeValue;
				if(empty($rId)) {
					$this->w('Region id should not be empty: '.$id);
				} else {
					$regions[] = $rId;
				}
			}
		}
		foreach($borders as $border) {
			if(!isset($territoryIds[$border])) {
				$this->w('Border "'.$border.'" does not exist');
			}
		}
		$regionElements = $this->xpath->query('child::regions/region');
		$actualRegions = array();
		foreach($regionElements as $region) {
			$id = $region->getAttribute('id');
			if(empty($id)) {
				$this->w('Region id should not be empty');
			} else {
				if(!in_array($id, $regions)) {
					$this->w('Region "'.$id.'" is not used');
				}
				$actualRegions[] = $id;
			}
			$name = $this->xpath->query('child::name', $region)->item(0)->nodeValue;
			if(empty($name)) {
				$this->w('Region name should not be empty: '.$id);
			}
			$units = (int)$this->xpath->query('child::units', $region)->item(0)->nodeValue;
			if($units < 1) {
				$this->logger->log(\imperator\Logger::LEVEL_WARNING, 'Possible bug: region '.$id.' yields '.$units.' units per turn');
			}
		}
		foreach($regions as $region) {
			if(!in_array($region, $actualRegions)) {
				$this->w('Region "'.$region.'" does not exist');
			}
		}
		if($this->numErrors === 0) {
			$this->logger->log(\imperator\Logger::LEVEL_INFO, 'Passed territory and region validation');
		}
		$this->validateMissionXML();
	}

	private function validateMissionXML() {
		$numErrors = $this->numErrors;
		$players = (int)$this->xpath->query('child::players')->item(0)->nodeValue;
		$missionElements = $this->xpath->query('child::missions/mission');
		$totalAvailability = 0;
		$fallBacks = array();
		$missions = array();
		foreach($missionElements as $mission) {
			$id = (int)$mission->getAttribute('missionId');
			if(isset($missions[$id])) {
				$this->w('Mission ids should be unique: '.$id);
			} else {
				$missions[$id] = true;
			}
			$availability = (int)$mission->getAttribute('availability');
			$totalAvailability += $availability;
			if($mission->hasAttribute('fallback')) {
				$fallback = $mission->getAttribute('fallback');
				if($fallback == $id) {
					$this->w('Missions cannot fallback on themselves: '.$id);
				}
				$fallBacks[] = $fallback;
			}
			if($mission->hasAttribute('class')) {
				$class = '\\imperator\\mission\\'.$mission->getAttribute('class');
				if(!class_exists($class)) {
					$this->w('Mission class "'.$class.'" does not exist');
				}
			} else {
				$name = $this->xpath->query('child::name', $mission)->item(0)->nodeValue;
				if(empty($name)) {
					$this->w('Mission name should not be empty: '.$id);
				}
				$description = $this->xpath->query('child::description', $mission)->item(0)->nodeValue;
				if(empty($description)) {
					$this->w('Mission description should not be empty: '.$id);
				}
				$conditions = $this->xpath->query('child::conditions/condition', $mission);
				if($conditions->length === 0) {
					$this->w('A mission must have conditions: '.$id);
				}
				foreach($conditions as $condition) {
					$class = '\\imperator\\mission\\'.$condition->getAttribute('class');
					if(!class_exists($class)) {
						$this->w('Mission condition class "'.$class.'" does not exist');
					}
				}
			}
		}
		if($totalAvailability < $players) {
			$this->w('Mission availability is less than the number of players: '.$totalAvailability.' < '.$players);
		}
		foreach($fallBacks as $fallback) {
			if(!isset($missions[$fallback])) {
				$this->w('Fallback mission "'.$fallback.'" does not exist');
			}
		}
		if($this->numErrors === $numErrors) {
			$this->logger->log(\imperator\Logger::LEVEL_INFO, 'Passed mission validation');
		}
	}

	private function instantiateMap() {
		try {
			$map = new \imperator\map\Map($this->file);
			$map->getId();
			$map->getName();
			$map->getPlayers();
			$map->getTerritories();
			$map->getRegions();
			$map->getMissions();
		} catch(\Exception $e) {
			$this->w($e);
		}
	}
}