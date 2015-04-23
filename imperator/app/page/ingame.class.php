<?php
namespace imperator\page;
use imperator\Imperator;

class InGame extends DefaultPage {
	/**
	 * @var \imperator\Game
	 */
	private $game;

	public function __construct(\imperator\Game $game) {
		$this->game = $game;
	}

	public function render(\imperator\User $user) {
		$language = $user->getLanguage();
		$this->setBodyContents(Template::getInstance('game')->replace(array(
			'zoomin' => $language->translate('Zoom in'),
			'zoomout' => $language->translate('Zoom out'),
			'mapalt' => $language->translate('Map of %1$s', $this->game->getMap()->getName()),
			'mapurl' => $this->getMapURL(),
			'tabRegions' => $language->translate('Regions'),
			'tabTerritories' => $language->translate('Territories'),
			'tabChat' => $language->translate('Chat'),
			'tabMap' => $language->translate('Map'),
			'tabLog' => $language->translate('Combat Log'),
			'tabPlayers' => $language->translate('Players'),
			'tabCards' => $language->translate('Cards'),
			'tabSettings' => $language->translate('Settings'),
			'chat' => $this->getChatBox($user)
		))->getData());
		$this->addChatJavascript($this->game->getId());
		$this->setTitle($this->game->getName());
		$this->setMainClass('container-fluid');
		$this->addCSS('game.css');
		$this->addJavascript('map.js');
		parent::render($user);
	}

	private function getMapURL() {
		return Imperator::getSettings()->getBaseURL().'/img/maps/map_'.$this->game->getMap()->getId().'.svg';
	}

	protected function getFooter(\imperator\User $user) {
		return '';
	}
}