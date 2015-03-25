<?php
namespace imperator\page;
use imperator\Imperator;

class PreGame extends DefaultPage {
	/**
	 * @var \imperator\Game
	 */
	private $game = null;
	private $joinColor;

	public function __construct(\imperator\Game $game) {
		$this->game = $game;
	}

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	public function render(\imperator\User $user) {
		if($this->game->getOwner()->equals($user)) {
			if(isset($_POST['startgame']) && $this->game->getNumberOfPlayers() == $this->game->getMap()->getPlayers()) {
				$this->startGame();
			} else if(isset($_POST['disband'])) {
				$this->disbandGame();
			}
		} else if(isset($_POST['leavegame']) && $this->game->containsPlayer($user)) {
			$this->leaveGame($user);
		} else if($this->joinHasBeenSubmitted() && !$this->game->containsPlayer($user)) {
			if($this->validateJoinRequest()) {
				$this->joinGame($user);
			} else {
				//TODO error
			}
		}
		$this->setTitle($this->game->getName());
		$this->setBodyContents($this->getPregame($user));
		parent::render($user);
	}

	private function disbandGame() {
		$this->game->disband();
		Imperator::redirect(Index::getURL());
	}

	private function startGame() {
		$this->game->start();
		//TODO redirect
	}

	private function leaveGame(\imperator\User $user) {
		$this->game->removeUser($user);
	}

	private function joinHasBeenSubmitted() {
		return isset($_POST['color']) && (($this->game->hasPassword() && isset($_POST['password'])) || !$this->game->hasPassword());
	}

	private function validateJoinRequest() {
		$this->joinColor = $_POST['color'];
		$password = $this->game->hasPassword() ? $_POST['password'] : null;
		$colors = $this->game->getRemainingColors();
		return isset($colors[$this->joinColor]) && $this->game->isValidPassword($password);
	}

	private function joinGame(\imperator\User $user) {
		$user->setColor($this->joinColor);
		$this->game->addUser($user);
	}

	private function getPregame(\imperator\User $user) {
		$language = $user->getLanguage();
		return Template::getInstance('game_pregame')->replace(array(
			'title' => $this->game->getName(),
			'players' => $this->getPlayers($user),
			'mapheading' => $language->translate('Map'),
			'playersheading' => $language->translate('Players'),
			'map' => $this->game->getMap()->getName(),
			'mapurl' => Map::getURL($this->game->getMap()->getId(), $this->game->getMap()->getName()),
			'startjoingame' => $this->getControls($user)
		))->getData();
	}

	private function getControls(\imperator\User $user) {
		if($this->game->getOwner()->equals($user)) {
			return $this->getOwnerGameForm($user);
		} else if($this->game->getNumberOfPlayers() < $this->game->getMap()->getPlayers() && !$this->game->containsPlayer($user)) {
			return $this->getJoinGameForm($user);
		} else if($this->game->containsPlayer($user)) {
			return $this->getLeaveForm($user);
		}
		return '';
	}

	private function getOwnerGameForm(\imperator\User $user) {
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

	private function getJoinGameForm(\imperator\User $user) {
		$language = $user->getLanguage();
		return Template::getInstance('game_pregame_join')->replace(array(
			'choosecolor' => $language->translate('Choose a color'),
			'password' => $this->getJoinPassword($user),
			'join' => $language->translate('Join game'),
			'colors' => Game::getColors($user, $this->game->getRemainingColors())
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