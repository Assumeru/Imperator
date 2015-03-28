<?php
namespace imperator\page;
use imperator\Imperator;

class NewGame extends DefaultPage {
	const NAME = 'New Game';
	const URL = 'new-game';
	private $validated = array();

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	public function render(\imperator\User $user) {
		if($this->hasBeenSubmitted()) {
			if($this->validateRequest()) {
				$this->createNewGame($user);
				return;
			} else {
				//TODO error
			}
		}
		$this->setTitle($user->getLanguage()->translate(self::NAME));
		$this->setBodyContents($this->getNewGameForm($user));
		parent::render($user);
	}

	private function hasBeenSubmitted() {
		return isset($_POST['name']) && isset($_POST['map']) && isset($_POST['color']);
	}

	private function createNewGame(\imperator\User $user) {
		$user->setColor($this->validated['color']);
		$game = \imperator\Game::create($user, $this->validated['map'], $this->validated['name'], $this->validated['password']);
		Imperator::redirect(Game::getURL($game));
	}

	private function validateRequest() {
		$this->validated['name'] = trim($_POST['name']);
		$this->validated['map'] = $_POST['map'];
		$this->validated['color'] = $_POST['color'];
		$this->validated['password'] = isset($_POST['password']) ? trim($_POST['password']) : null;
		if(empty($this->validated['password'])) {
			$this->validated['password'] = null;
		}
		return $this->isValidName($this->validated['name'])
			&& $this->isValidColor($this->validated['color'])
			&& $this->isValidMap($this->validated['map']);
	}

	private function isValidName($name) {
		return !empty($name) && strlen($name) <= Imperator::getSettings()->getMaxGameNameLength();
	}

	private function isValidMap($mapId) {
		if(is_numeric($mapId)) {
			return \imperator\map\Map::getInstance((int)$mapId) !== null;
		}
		return false;
	}

	private function isValidColor($color) {
		$colors = array_keys(Imperator::getSettings()->getPlayerColors());
		return in_array($color, $colors);
	}

	private function getNewGameForm(\imperator\User $user) {
		$language = $user->getLanguage();
		return Template::getInstance('newgame')->replace(array(
			'title' => $language->translate(self::NAME),
			'maps' => $this->getMaps($language),
			'colors' => Game::getColors($user),
			'create' => $language->translate('Create game'),
			'entergamename' => $language->translate('Enter game name'),
			'enterpassword' => $language->translate('Enter password (optional)'),
			'choosecolor' => $language->translate('Choose a color'),
			'choosemap' => $language->translate('Choose a map'),
			'defaultname' => $language->translate('%1$s\'s game', $user->getName()),
			'maxlength' => Imperator::getSettings()->getMaxGameNameLength()
		))->getData();
	}

	private function getMaps(\imperator\Language $language) {
		$maps = '';
		$mapList = \imperator\map\Map::getMaps();
		foreach($mapList as $map) {
			$maps .= Template::getInstance('newgame_map')->replace(array(
				'value' => $map->getId(),
				'name' => $language->translate('%1$s (%2$d players)', $language->translate($map->getName()), $map->getPlayers())
			))->getData();
		}
		return $maps;
	}
}