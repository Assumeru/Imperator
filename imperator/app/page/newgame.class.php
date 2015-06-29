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

	private function createNewGame(form\Form $form, \imperator\User $user) {
		$player = new \imperator\game\Player($user);
		$player->setColor($form->getColor());
		$game = \imperator\Game::create($player, $form->getMap(), $form->getName(), $form->getPassword());
		Imperator::redirect(Game::getURL($game));
	}

	private function getNewGameForm(\imperator\User $user, form\NewGameForm $form) {
		$language = $user->getLanguage();
		$error = $form->getNameError();
		$defaultName = $language->translate('%1$s\'s game', $user->getName());
		$hasError = '';
		if(!empty($error)) {
			$defaultName = $form->getName();
			$hasError = ' has-error';
		}
		return Template::getInstance('newgame')->replace(array(
			'title' => $language->translate(self::NAME),
			'maps' => $this->getMaps($language),
			'colors' => Game::getColors($user),
			'create' => $language->translate('Create game'),
			'entergamename' => $language->translate('Enter game name'),
			'enterpassword' => $language->translate('Enter password (optional)'),
			'choosecolor' => $language->translate('Choose a color'),
			'choosemap' => $language->translate('Choose a map'),
			'defaultname' => $defaultName,
			'maxlength' => Imperator::getSettings()->getMaxGameNameLength(),
			'hasError' => $hasError,
			'nameError' => $language->translate($error)
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