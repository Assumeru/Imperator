<?php
namespace imperator\page;
use imperator\Imperator;

class Game extends DefaultPage {
	const URL = 'game';
	/**
	 * @var \imperator\Game
	 */
	private $game = null;

	public function __construct(array $arguments = null) {
		$gameId = null;
		if(isset($arguments[0]) && is_numeric($arguments[0])) {
			$gameId = (int)$arguments[0];
			$this->game = Imperator::getDatabaseManager()->getTable('Games')->getGameById($gameId);
		}
	}

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	public function render(\imperator\User $user) {
		if(!$this->game) {
			$page = new HTTP404();
		} else if($this->game->hasEnded()) {
			$page = new PostGame($this->game);
		} else if($this->game->hasStarted()) {
			$page = new InGame($this->game);
		} else {
			$page = new PreGame($this->game);
		}
		$page->render($user);
	}

	public static function getURL(\imperator\Game $game = null, $invite = false) {
		if($game === null) {
			return parent::getURL();
		}
		$url = parent::getURL().'/'.$game->getId().'/'.urlencode($game->getName());
		if($invite && $game->hasPassword()) {
			if(strpos($url, '?') === false) {
				$url .= '?';
			} else {
				$url .= '&';
			}
			$url .= 'code='.urlencode($game->getInviteCode());
		}
		return $url;
	}

	public static function getTerritoryFlag(\imperator\map\Territory $territory) {
		return Imperator::getSettings()->getBaseURL().'/img/flags/'.str_replace('_', '/', $territory->getId()).'.png';
	}

	public static function getRegionFlag(\imperator\map\Region $region) {
		return Imperator::getSettings()->getBaseURL().'/img/flags/'.str_replace('_', '/', $region->getId()).'.png';
	}

	public static function getColors(\imperator\User $user, array $colorList = null) {
		if($colorList === null) {
			$colorList = Imperator::getSettings()->getPlayerColors();
		}
		$language = $user->getLanguage();
		$first = true;
		return Template::getInstance('newgame_color', $language)->setVariables(array(
			'colors' => $colorList
		));
	}
}