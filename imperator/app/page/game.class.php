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
		} else if(!$this->game->hasStarted()) {
			$page = new PreGame($this->game);
		}
		$page->render($user);
	}

	public static function getURL($gameId = null, $name = '') {
		if($gameId === null) {
			return parent::getURL();
		} else {
			return parent::getURL().'/'.$gameId.'/'.urlencode($name);
		}
	}

	public static function getColors(\imperator\User $user, array $colorList = null) {
		$colors = '';
		if($colorList === null) {
			$colorList = Imperator::getSettings()->getPlayerColors();
		}
		$first = true;
		foreach($colorList as $value => $color) {
			$colors .= Template::getInstance('newgame_color')->replace(array(
				'value' => $value,
				'name' => $color,
				'checked' => $first ? 'checked' : ''
			))->getData();
			$first = false;
		}
		return $colors;
	}
}