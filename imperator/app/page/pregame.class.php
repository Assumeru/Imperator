<?php
namespace imperator\page;
use imperator\Imperator;

class PreGame extends DefaultPage {
	/**
	 * @var \imperator\Game
	 */
	private $game = null;

	public function __construct(\imperator\Game $game) {
		$this->game = $game;
	}

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	public function render(\imperator\User $user) {
		$joinForm = new form\JoinGameForm($this->game);
		$controlForm = new form\PreGameControlForm();
		if($this->game->getOwner()->equals($user)) {
			if($controlForm->startHasBeenSubmitted() && $this->game->getNumberOfPlayers() == $this->game->getMap()->getPlayers()) {
				$this->startGame();
			} else if($controlForm->disbandHasBeenSubmitted()) {
				$this->disbandGame();
			}
		} else if($controlForm->leaveHasBeenSubmitted() && $this->game->containsPlayer($user)) {
			$this->leaveGame($user);
		} else if($joinForm->hasBeenSubmitted() && !$this->game->containsPlayer($user) && $joinForm->validateRequest()) {
			$this->joinGame($user, $joinForm);
		}
		$this->setTitle($this->game->getName());
		$this->setBodyContents($this->getPregame($user, $joinForm));
		$this->addJavascript('pregame.js');
		parent::render($user);
	}

	private function disbandGame() {
		$this->game->disband();
		Imperator::redirect(Index::getURL());
	}

	private function startGame() {
		$this->game->start();
		Imperator::redirect(Game::getURL($this->game));
	}

	private function leaveGame(\imperator\User $user) {
		$this->game->removeUser($user);
	}

	private function joinGame(\imperator\User $user, form\JoinGameForm $form) {
		$user->setColor($form->getColor());
		$this->game->addUser($user);
	}

	private function getPregame(\imperator\User $user, form\JoinGameForm $form) {
		$language = $user->getLanguage();
		return Template::getInstance('game_pregame')->replace(array(
			'title' => $this->game->getName(),
			'players' => $this->getPlayers($user),
			'mapheading' => $language->translate('Map'),
			'playersheading' => $language->translate('Players'),
			'map' => $this->game->getMap()->getName(),
			'mapurl' => Map::getURL($this->game->getMap()->getId(), $this->game->getMap()->getName()),
			'startjoingame' => $this->getControls($user, $form),
			'chat' => $this->getChat($user)
		))->getData();
	}

	private function getChat(\imperator\User $user) {
		if($this->game->containsPlayer($user)) {
			$this->addChatJavascript($this->game->getId());
			return $this->getChatBox($user);
		}
		return '';
	}

	private function getControls(\imperator\User $user, form\JoinGameForm $form) {
		if($this->game->getOwner()->equals($user)) {
			return $this->getOwnerGameForm($user);
		} else if($this->game->getNumberOfPlayers() < $this->game->getMap()->getPlayers() && !$this->game->containsPlayer($user)) {
			return $this->getJoinGameForm($user, $form);
		} else if($this->game->containsPlayer($user)) {
			return $this->getLeaveForm($user);
		}
		return '';
	}

	public function getOwnerGameForm(\imperator\User $user) {
		$language = $user->getLanguage();
		$disband = Template::getInstance('game_pregame_disband')->replace(array(
			'disband' => $language->translate('Disband game')
		))->getData();
		$start = '';
		if($this->game->getNumberOfPlayers() == $this->game->getMap()->getPlayers()) {
			$start = Template::getInstance('game_pregame_start')->replace(array(
				'start' => $user->getLanguage()->translate('Start game')
			))->getData();
		}
		return Template::getInstance('game_pregame_owner')->replace(array(
			'disband' => $disband,
			'start' => $start
		))->getData();
	}

	private function getLeaveForm(\imperator\User $user) {
		return Template::getInstance('game_pregame_leave')->replace(array(
			'leave' => $user->getLanguage()->translate('Leave game')
		))->getData();
	}

	private function getJoinGameForm(\imperator\User $user, form\JoinGameForm $form) {
		$language = $user->getLanguage();
		$passwordError = $form->getPasswordError();
		$hasPasswordError = '';
		if(!empty($passwordError)) {
			$hasPasswordError = ' has-error';
			$passwordError = $language->translate($passwordError);
		}
		$colorError = $form->getColorError();
		$hasColorError = '';
		if(!empty($colorError)) {
			$hasColorError = ' has-error';
			$colorError = $language->translate($colorError);
		}
		return Template::getInstance('game_pregame_join')->replace(array(
			'choosecolor' => $language->translate('Choose a color'),
			'password' => $this->getJoinPassword($user),
			'join' => $language->translate('Join game'),
			'colors' => Game::getColors($user, $this->game->getRemainingColors()),
			'hasPasswordError' => $hasPasswordError,
			'hasColorError' => $hasColorError,
			'colorError' => $colorError,
			'passwordError' => $passwordError
		))->getData();
	}

	private function getJoinPassword(\imperator\User $user) {
		if($this->game->hasPassword()) {
			return Template::getInstance('game_pregame_password')->replace(array(
				'enterpassword' => $user->getLanguage()->translate('Enter password')
			))->getData();
		}
		return '';
	}

	private function getPlayers(\imperator\User $user) {
		$players = '';
		foreach($this->game->getPlayers() as $player) {
			$players .= Template::getInstance('game_player')->replace(array(
				'color' => $player->getColor(),
				'owner' => $player->equals($this->game->getOwner()) ? $user->getLanguage()->translate('(Owner)') : '',
				'user' => DefaultPage::getProfileLink($player)
			))->getData();
		}
		return $players;
	}
}