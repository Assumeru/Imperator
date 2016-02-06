<?php
namespace imperator\page;

class Map extends DefaultPage {
	const URL = 'map';
	private $map;

	public function __construct(array $arguments = null) {
		$mapId = null;
		if(isset($arguments[0]) && is_numeric($arguments[0])) {
			$mapId = (int)$arguments[0];
		}
		try {
			$this->map = new \imperator\map\Map($mapId);
		} catch(InvalidArgumentException $e) {
			$this->map = null;
		}
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
		$this->setBodyContents(Template::getInstance('map', $user->getLanguage())->setVariables(array(
			'map' => $this->map,
			'mapurl' => new \imperator\url\ImageURL('maps/image_'.$this->map->getId().'.svg'),
			'language' => $user->getLanguage()
		)));
		$this->addCSS('map.css');
		$this->addJavascript('map.js');
		parent::render($user);
	}

	public static function getURL(\imperator\map\Map $map = null) {
		if($map === null) {
			return parent::getURL();
		}
		return new \imperator\url\PageURL(static::URL, $map->getId(), $map->getName());
	}
}