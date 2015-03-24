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
			if($this->startHasBeenSubmitted() && $this->game->getNumberOfPlayers() == $this->game->getMap()->getPlayers()) {
				$this->startGame($user);
			} else if($this->disbandHadBeenSubmitted()) {
				$this->disbandGame($user);
			}
		} else if($this->leaveHasBeenSubmitted() && $this->game->containsPlayer($user)) {
			$this->leaveGame($user);
		} else if($this->joinHasBeenSubmitted() && !$this->game->containsPlayer($user)) {
			if($this->validateRequest()) {
				$this->joinGame($user);
			} else {
				//TODO error
			}
		}
		$this->setTitle($this->game->getName());
		$this->setBodyContents($this->getPregame($user));
		parent::render($user);
	}

	private function disbandGame(\imperator\User $user) {
		//TODO disband
	}

	private function startGame(\imperator\User $user) {
		Imperator::getDatabaseManager()->getTable('Games')->startGame($this->game);
	}

	private function leaveGame(\imperator\User $user) {
		Imperator::getDatabaseManager()->getTable('GamesJoined')->removeUserFromGame($user, $this->game->getId());
		$players = $this->game->getPlayers();
		foreach($players as $key => $player) {
			unset($players[$key]);
		}
		$this->game->setPlayers($players);
	}

	private function startHasBeenSubmitted() {
		return isset($_POST['startgame']);
	}

	private function leaveHasBeenSubmitted() {
		return isset($_POST['leavegame']);
	}

	private function joinHasBeenSubmitted() {
		return isset($_POST['color']) && (($this->game->hasPassword() && isset($_POST['password'])) || !$this->game->hasPassword());
	}

	private function validateRequest() {
		$this->joinColor = $_POST['color'];
		$password = $this->game->hasPassword() ? $_POST['password'] : null;
		$colors = $this->game->getRemainingColors();
		return isset($colors[$this->joinColor]) && $this->game->isValidPassword($password);
	}

	private function joinGame(\imperator\User $user) {
		Imperator::getDatabaseManager()->getTable('GamesJoined')->addUserToGame($user, $this->game->getId(), $this->joinColor);
		$players = $this->game->getPlayers();
		$user->setColor($this->joinColor);
		$players[] = $user;
		$this->game->setPlayers($players);
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
			$out = $this->getDisbandGameForm($user);
			if($this->game->getNumberOfPlayers() == $this->game->getMap()->getPlayers()) {
				$out .= $this->getStartGameForm($user);
			}
			return $out;
		} else if($this->game->getNumberOfPlayers() < $this->game->getMap()->getPlayers() && !$this->game->containsPlayer($user)) {
			return $this->getJoinGameForm($user);
		} else if($this->game->containsPlayer($user)) {
			return $this->getLeaveForm($user);
		}
	}

	private function getDisbandGameForm(\imperator\User $user) {
		return Template::getInstance('game_pregame_disband')->replace(array(
			'disband' => 'Disband game'
		))->getData();
	}

	private function getLeaveForm(\imperator\User $user) {
		return Template::getInstance('game_pregame_leave')->replace(array(
			'leave' => $user->getLanguage()->translate('Leave game')
		))->getData();
	}

	private function getStartGameForm(\imperator\User $user) {
		return Template::getInstance('game_pregame_start')->replace(array(
			'start' => $user->getLanguage()->translate('Start game')
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