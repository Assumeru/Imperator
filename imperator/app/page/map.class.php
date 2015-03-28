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
		parent::render($user);
	}

	private function getMapBody(\imperator\User $user) {
		$language = $user->getLanguage();
		return Template::getInstance('map')->replace(array(
			'title' => $language->translate($this->map->getName()),
			'mapalt' => $language->translate('Map of %1$s', $this->map->getName()),
			'mapurl' => $this->getMapURL(),
			'territories' => $this->getTerritoryList($user)
		))->getData();
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
				'regions' => $this->getRegionList($territory, $language, $flag)
			))->getData();
		}
		return $territories;
	}

	private function getRegionList(\imperator\map\Territory $territory, \imperator\Language $language, $flag) {
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