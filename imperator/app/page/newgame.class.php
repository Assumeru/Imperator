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
		if($form->hasBeenSubmitted() && $form->validateRequest()) {
			$this->createNewGame($form, $user);
			return;
		}
		$this->setTitle($user->getLanguage()->translate(self::NAME));
		$this->setBodyContents($this->getNewGameForm($user, $form));
		$this->addCSS('newgame.css');
		parent::render($user);
	}

	private function createNewGame(form\NewGameForm $form, \imperator\User $user) {
		$player = new \imperator\game\Player($user);
		$player->setColor($form->getColor());
		$game = \imperator\Game::create($player, $form->getMap(), $form->getName(), $form->getPassword());
		Imperator::redirect(Game::getURL($game));
	}

	private function getNewGameForm(\imperator\User $user, form\NewGameForm $form) {
		$language = $user->getLanguage();
		$error = $form->getNameError();
		if(!empty($error)) {
			$defaultName = $form->getName();
		} else {
			$defaultName = $language->translate('%1$s\'s game', $user->getName());
		}
		return Template::getInstance('newgame', $language)->setVariables(array(
			'error' => $error,
			'name' => $defaultName,
			'colors' => Game::getColors($user),
			'settings' => Imperator::getSettings(),
			'maps' => \imperator\map\Map::getSortedMaps()
		));
	}
}