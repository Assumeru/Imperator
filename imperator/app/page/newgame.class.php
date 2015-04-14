<?php
namespace imperator\page;
use imperator\Imperator;

class NewGame extends DefaultPage {
	const NAME = 'New Game';
	const URL = 'new-game';

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	public function render(\imperator\User $user) {
		$form = new form\NewGameForm();
		if($form->hasBeenSubmitted()) {
			if($form->validateRequest()) {
				$this->createNewGame($form, $user);
				return;
			} else {
				//TODO error
			}
		}
		$this->setTitle($user->getLanguage()->translate(self::NAME));
		$this->setBodyContents($this->getNewGameForm($user));
		parent::render($user);
	}

	private function createNewGame(page\Form $form, \imperator\User $user) {
		$user->setColor($form->getColor());
		$game = \imperator\Game::create($user, $form->getMap(), $form->getName(), $form->getPassword());
		Imperator::redirect(Game::getURL($game));
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