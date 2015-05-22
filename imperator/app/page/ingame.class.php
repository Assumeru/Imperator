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
			'unitgraphics' => Imperator::getSettings()->getBaseURL().'/img/game/units.svg',
			'turn' => $language->translate('%1$s\'s turn', DefaultPage::getProfileLink($this->game->getCurrentPlayer())),
			'stack' => $language->translate('Stack'),
			'move' => $language->translate('Move'),
			'forfeit' => $language->translate('Forfeit'),
			'stackTitle' => $language->translate('Place new units instead of attacking'),
			'moveTitle' => $language->translate('Stop attacking and start moving units'),
			'forfeitTitle' => $language->translate('Surrender the game'),
			'uptTitleTerritories' => $language->translate('Units gained from territories'),
			'uptTitleRegions' => $language->translate('Units gained from regions'),
			'unitsFortifyTitle' => $language->translate('Number of units left to place'),
			'unitsMoveTitle' => $language->translate('Number of units left to move'),
			'endturn' => $language->translate('End turn'),
			'endturnTitle' => $language->translate('Cede control and end your turn'),
			'cardList' => $inGame ? $this->getCardList($this->game->getPlayerByUser($user)) : '',
			'cardsButtonFour' => $language->translate('Place %1$d units', 4),
			'cardsButtonSix' => $language->translate('Place %1$d units', 6),
			'cardsButtonEight' => $language->translate('Place %1$d units', 8),
			'cardsButtonTen' => $language->translate('Place %1$d units', 10),
			'radialstack' => $language->translate('Fortify this territory'),
			'radialmoveto' => $language->translate('Move units to this territory'),
			'radialmovefrom' => $language->translate('Move units from this territory'),
			'radialattackto' => $language->translate('Attack this territory'),
			'radialattackfrom' => $language->translate('Attack from this territory')
		))->getData());
		$mainClass = ' not-player';
		if($inGame) {
			$this->addChatJavascript($this->game->getId());
			$mainClass = '';
		}
		$this->renderJavascript($user);
		$this->setTitle($this->game->getName());
		$this->setMainClass('container-fluid'.$mainClass);
		$this->addCSS('game.css');
		parent::render($user);
	}

	private function renderJavascript(\imperator\User $user) {
		$language = $user->getLanguage();
		$this->setJavascriptSetting('uid', $user->getId());
		$this->setJavascriptSetting('templates', array(
			'dialog' => Template::getInstance('dialog')->replace(array(
				'close' => $language->translate('Close window')
			))->getData(),
			'card' => Template::getInstance('game_card')->replace(array(
				'url' => $this->getCardURL(),
				'name' => '%2$s'
			))->getData(),
			'okbutton' => Template::getInstance('button_ok')->replace(array(
				'value' => $language->translate('Ok')
			))->getData(),
			'dialogform' => Template::getInstance('dialog_form')->getData()
		));
		$this->setJavascriptSetting('language', array(
			'wait' => $language->translate('Please wait...'),
			'contacting' => $language->translate('Contacting server.'),
			'newcard' => $language->translate('You have received a new card!'),
			'card' => \imperator\game\Cards::getCardNames($language),
			'forfeit' => $language->translate('Are you sure you want to forfeit?')
		));
		$this->addJavascript('dialog.js');
		$this->addJavascript('map.js');
		$this->addJavascript('game.js');
	}

	private function getCardList(\imperator\User $user) {
		$cards = $user->getCards($this->game);
		$cardList = '';
		$names = \imperator\game\Cards::getCardNames($user->getLanguage());
		$url = $this->getCardURL();
		foreach($cards->getCards() as $card) {
			$cardList .= Template::getInstance('game_card')->replace(array(
				'url' => sprintf($url, $card),
				'name' => $names[$card]
			))->getData();
		}
		return $cardList;
	}

	private function getCardURL() {
		return Imperator::getSettings()->getBaseURL().'/img/cards/%1$s.png';
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
				'player' => DefaultPage::getProfileLink($territory->getOwner()),
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
				'player' => DefaultPage::getProfileLink($player),
				'id' => $player->getId()
			))->getData();
		}
		return $players;
	}
}