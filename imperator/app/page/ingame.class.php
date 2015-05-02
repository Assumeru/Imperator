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
		$game->loadMap();
	}

	public function render(\imperator\User $user) {
		$inGame = $this->game->containsPlayer($user);
		$language = $user->getLanguage();
		$this->setBodyContents(Template::getInstance('game')->replace(array(
			'zoomin' => $language->translate('Zoom in'),
			'zoomout' => $language->translate('Zoom out'),
			'mapalt' => $language->translate('Map of %1$s', $this->game->getMap()->getName()),
			'mapurl' => $this->getMapURL(),
			'regions' => $language->translate('Regions'),
			'territories' => $language->translate('Territories'),
			'tabChat' => $language->translate('Chat'),
			'tabMap' => $language->translate('Map'),
			'tabLog' => $language->translate('Combat Log'),
			'tabPlayers' => $language->translate('Players'),
			'tabCards' => $language->translate('Cards'),
			'tabSettings' => $language->translate('Settings'),
			'chat' => $inGame ? $this->getChatBox($user) : '',
			'flag' => $language->translate('Flag'),
			'territory' => $language->translate('Territory'),
			'player' => $language->translate('Player'),
			'units' => $language->translate('Units'),
			'territoryList' => $this->getTerritoryList($user),
			'regionList' => $this->getRegionList($user),
			'unitsperturn' => $language->translate('Units per turn'),
			'playerList' => $this->getPlayerList($user),
			'autoroll' => $language->translate('Autoroll'),
			'unitgraphicsdefault' => $language->translate('Default unit graphics'),
			'unitgraphicsnumeric' => $language->translate('Numeric unit graphics'),
			'unitgraphicsnone' => $language->translate('No unit graphics'),
			'unitgraphicsdefaultdesc' => $language->translate('Select this to use unit icons.'),
			'unitgraphicsnumericdesc' => $language->translate('Select this to use unit numbers.'),
			'unitgraphicsnonedesc' => $language->translate('Select this to disable units.'),
			'borders' => $language->translate('Borders'),
			'unitgraphics' => Imperator::getSettings()->getBaseURL().'/img/game/units.svg'
		))->getData());
		$mainClass = ' not-player';
		if($inGame) {
			$this->addChatJavascript($this->game->getId());
			$mainClass = '';
		}
		$this->setTitle($this->game->getName());
		$this->setMainClass('container-fluid'.$mainClass);
		$this->addCSS('game.css');
		$this->addJavascript('map.js');
		$this->addJavascript('game.js');
		parent::render($user);
	}

	private function getMapURL() {
		return Imperator::getSettings()->getBaseURL().'/img/maps/map_'.$this->game->getMap()->getId().'.svg';
	}

	protected function getFooter(\imperator\User $user) {
		return '';
	}

	private function getTerritoryList(\imperator\User $user) {
		$language = $user->getLanguage();
		$territories = '';
		foreach($this->game->getMap()->getTerritories() as $territory) {
			$territories .= Template::getInstance('game_territories_territory')->replace(array(
				'id' => $territory->getId(),
				'flagURL' => Game::getTerritoryFlag($territory),
				'flag' => $language->translate('Flag of %1$s', $territory->getName()),
				'territory' => $language->translate($territory->getName()),
				'player' => $this->getProfileLink($territory->getOwner()),
				'color' => $territory->getOwner()->getColor(),
				'units' => $territory->getUnits(),
				'regions' => $this->getRegionsForTerritory($territory, $user)
			))->getData();
		}
		return $territories;
	}

	private function getRegionsForTerritory(\imperator\map\Territory $territory, \imperator\User $user) {
		$regions = '';
		foreach($territory->getRegions() as $region) {
			$regions .= Template::getInstance('game_territories_region')->replace(array(
				'flagURL' => Game::getRegionFlag($region),
				'region' => $user->getLanguage()->translate($region->getName())
			))->getData();
		}
		return $regions;
	}

	private function getRegionList(\imperator\User $user) {
		$regions = '';
		$language = $user->getLanguage();
		$highlight = $language->translate('Highlight');
		foreach($this->game->getMap()->getRegions() as $region) {
			$regions .= Template::getInstance('game_regions_region')->replace(array(
				'id' => $region->getId(),
				'flagURL' => Game::getRegionFlag($region),
				'region' => $language->translate($region->getName()),
				'unitsTerritories' => $language->translate('%1$d territories, %2$d units per turn', count($region->getTerritories()), $region->getUnitsPerTurn()),
				'territories' => $this->getTerritoriesForRegion($region, $user),
				'highlight' => $highlight
			))->getData();
		}
		return $regions;
	}

	private function getTerritoriesForRegion(\imperator\map\Region $region, \imperator\User $user) {
		$territories = '';
		foreach($region->getTerritories() as $territory) {
			$territories .= Template::getInstance('game_regions_territory')->replace(array(
				'id' => $territory->getId(),
				'territory' => $user->getLanguage()->translate($territory->getName()),
				'color' => $territory->getOwner()->getColor(),
				'flagURL' => Game::getTerritoryFlag($territory)
			))->getData();
		}
		return $territories;
	}

	private function getPlayerList(\imperator\User $user) {
		$players = '';
		foreach($this->game->getPlayers() as $player) {
			$players .= Template::getInstance('game_players_player')->replace(array(
				'player' => Game::getProfileLink($player),
				'id' => $player->getId(),
				'color' => $player->getColor()
			))->getData();
		}
		return $players;
	}
}