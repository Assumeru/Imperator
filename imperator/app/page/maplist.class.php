<?php
namespace imperator\page;

class MapList extends DefaultPage {
	const NAME = 'Maps';
	const URL = 'maps';

	public function render(\imperator\User $user) {
		$this->setTitle($user->getLanguage()->translate(static::NAME));
		$this->setBodyContents($this->getMapList($user));
		parent::render($user);
	}

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	private function getMapList(\imperator\User $user) {
		$mapList = \imperator\map\Map::getMaps();
		$language = $user->getLanguage();
		$maps = '';
		foreach($mapList as $map) {
			$maps .= Template::getInstance('maps_map')->replace(array(
				'url' => \imperator\page\Map::getURL($map->getId(), $map->getName()),
				'name' => $language->translate($map->getName()),
				'players' => $map->getPlayers()
			))->getData();
		}
		return Template::getInstance('maps')->replace(array(
			'title' => $language->translate(self::NAME),
			'name' => $language->translate('Name'),
			'players' => $language->translate('Players'),
			'maps' => $maps
		))->getData();
	}
}