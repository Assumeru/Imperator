<?php
namespace imperator\page;

class MapList extends DefaultPage {
	const NAME = 'Maps';
	const URL = 'maps';

	public function render(\imperator\User $user) {
		$this->setTitle($user->getLanguage()->translate(static::NAME));
		$this->setBodyContents(Template::getInstance('maps', $user->getLanguage())->setVariables(array(
			'maps' => \imperator\map\Map::getSortedMaps()
		)));
		$this->addJavascript('jquery.tablesorter.min.js');
		$this->addJavascript('tablesorter.js');
		parent::render($user);
	}

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}
}