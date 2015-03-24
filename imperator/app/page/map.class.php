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
		$map = $this->map;
		$language = $user->getLanguage();
		return Template::getInstance('map')->replace(array(
			'title' => $language->translate($map->getName()),
			'mapalt' => $language->translate('Map of %1$s', $map->getName()),
			'mapurl' => Imperator::getSettings()->getBaseURL().'/img/maps/image_'.$map->getId().'.svg'
		))->getData();
	}

	public static function getURL($mapId = null, $name = '') {
		if($mapId === null) {
			return parent::getURL();
		} else {
			return parent::getURL().'/'.$mapId.'/'.urlencode($name);
		}
	}
}