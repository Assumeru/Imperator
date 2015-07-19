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
		if($this->game->getOwner()->getUser()->equals($user)) {
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
		$this->setBodyContents(Template::getInstance('game_pregame', $user->getLanguage())->setVariables(array(
			'chat' => $this->getChat($user),
			'game' => $this->game,
			'controls' => $this->getControls($user, $joinForm),
			'invitelink' => $this->game->containsPlayer($user) && $this->game->hasPassword() ? Game::getURL($this->game, true) : '',
			'canKick' => $user->equals($this->game->getOwner()->getUser()),
			'user' => $user
		)));
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
		$this->game->removeUser($this->game->getPlayerByUser($user));
		Imperator::redirect(GameList::getURL());
	}

	private function joinGame(\imperator\User $user, form\JoinGameForm $form) {
		$player = new \imperator\game\Player($user, $this->game);
		$player->setColor($form->getColor());
		$this->game->addUser($player);
		Imperator::redirect(Game::getURL($this->game));
	}

	private function getChat(\imperator\User $user) {
		if($this->game->containsPlayer($user)) {
			$this->addChatJavascript($user, $this->game->getId(), $user->canDeleteChatMessages() || $this->game->getOwner()->getUser()->equals($user));
			return $this->getChatBox($user);
		}
		return null;
	}

	private function getControls(\imperator\User $user, form\JoinGameForm $form) {
		$player = $this->game->getPlayerByUser($user);
		if($this->game->getOwner() == $player) {
			$this->setJavascriptSetting('language', array(
				'confirmkick' => $user->getLanguage()->translate('Are you sure you want to kick this player?')
			));
			$this->addJavascript('pregame.js');
			return $this->getOwnerGameForm($user);
		} else if($this->game->getNumberOfPlayers() < $this->game->getMap()->getPlayers() && $player === null) {
			$this->addCSS('newgame.css');
			return Template::getInstance('game_pregame_join', $user->getLanguage())->setVariables(array(
				'hasPassword' => $this->game->hasPassword(),
				'passwordError' => $form->getPasswordError(),
				'colorError' => $form->getColorError(),
				'colors' => Game::getColors($user, $this->game->getRemainingColors()),
				'code' => $form->getInviteCode()
			));
		} else if($player !== null) {
			$this->addJavascript('pregame.js');
			return Template::getInstance('game_pregame_leave', $user->getLanguage());
		}
		return null;
	}

	public function getOwnerGameForm(\imperator\User $user) {
		return Template::getInstance('game_pregame_owner', $user->getLanguage())->setVariables(array(
			'canStart' => $this->game->getNumberOfPlayers() == $this->game->getMap()->getPlayers()
		));
	}
}