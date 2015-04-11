<?php
namespace imperator\page;
use imperator\Imperator;

class Map extends DefaultPage {
	const URL = 'map';
	private $map;

	public function __construct(array $arguments = null) {
		$mapId = null;
		if(isset($arguments[0]) && is_numeric($arguments[0])) {
			$mapId = (int)$arguments[0];
		}
		$this->map = \imperator\map\Map::getInstance($mapId);
	}

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	public function render(\imperator\User $user) {
		$map = $this->map;
		if(!$map) {
			$page = new HTTP404();
			$page->render($user);
			return;
		}
		$this->setTitle($user->getLanguage()->translate($map->getName()));
		$this->setBodyContents($this->getMapBody($user));
		$this->addJavascript('map.js');
		parent::render($user);
	}

	private function getMapBody(\imperator\User $user) {
		$language = $user->getLanguage();
		return Template::getInstance('map')->replace(array(
			'title' => $language->translate($this->map->getName()),
			'mapalt' => $language->translate('Map of %1$s', $this->map->getName()),
			'mapurl' => $this->getMapURL(),
			'territories' => $this->getTerritoryList($user),
			'noscript' => $language->translate('Javascript needs to be enabled to interact with this map.'),
			'regionsheader' => $language->translate('Regions'),
			'regions' => $this->getRegionList($language),
			'region' => $language->translate('Region'),
			'units' => $language->translate('Units per turn'),
			'regionterritories' => $language->translate('Number of territories'),
			'zoomin' => $language->translate('Zoom in'),
			'zoomout' => $language->translate('Zoom out'),
			'description' => $this->map->getDescription($language->getHtmlLang()),
			'missionsheader' => $language->translate('Missions'),
			'missions' => $this->getMissions($language)
		))->getData();
	}

	private function getMissions(\imperator\Language $language) {
		$missions = '';
		foreach($this->map->getMissions() as $mission) {
			$missions .= Template::getInstance('map_mission')->replace(array(
				'name' => $language->translate($mission->getName()),
				'description' => $mission->getDescription($language)
			))->getData();
		}
		return $missions;
	}

	private function getRegionList(\imperator\Language $language) {
		$regions = '';
		foreach($this->map->getRegions() as $region) {
			$regions .= Template::getInstance('map_regions_region')->replace(array(
				'url' => Game::getRegionFlag($region),
				'name' => $language->translate($region->getName()),
				'units' => $region->getUnitsPerTurn(),
				'territories' => count($region->getTerritories())
			))->getData();
		}
		return $regions;
	}

	private function getMapURL() {
		return Imperator::getSettings()->getBaseURL().'/img/maps/image_'.$this->map->getId().'.svg';
	}

	private function getTerritoryList(\imperator\User $user) {
		$territories = '';
		$language = $user->getLanguage();
		$flag = $language->translate('Flag');
		foreach($this->map->getTerritories() as $territory) {
			$territories .= Template::getInstance('map_territory')->replace(array(
				'url' => Game::getTerritoryFlag($territory),
				'flag' => $flag,
				'territory' => $language->translate($territory->getName()),
				'regions' => $this->getRegionsForTerritory($territory, $language, $flag),
				'id' => $territory->getId()
			))->getData();
		}
		return $territories;
	}

	private function getRegionsForTerritory(\imperator\map\Territory $territory, \imperator\Language $language, $flag) {
		$regions = '';
		foreach($territory->getRegions() as $region) {
			$regions .= Template::getInstance('map_region')->replace(array(
				'region' => $language->translate($region->getName()),
				'url' => Game::getRegionFlag($region),
				'flag' => $flag
			))->getData();
		}
		return $regions;
	}

	public static function getURL($mapId = null, $name = '') {
		if($mapId === null) {
			return parent::getURL();
		} else {
			return parent::getURL().'/'.$mapId.'/'.urlencode($name);
		}
	}
}