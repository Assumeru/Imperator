<?php
namespace imperator\page\form;

class JoinGameForm extends Form {
	private $game;
	private $color;
	private $passwordError = '';
	private $colorError = '';
	private $inviteCode = '';

	public function __construct(\imperator\Game $game) {
		parent::__construct();
		$this->game = $game;
		if($this->hasPost('code')) {
			$this->inviteCode = $this->getPost('code');
		} else if($this->hasGet('code')) {
			$this->inviteCode = $this->getGet('code');
		}
	}

	public function hasBeenSubmitted() {
		return $this->hasPost('color')
			&& ((($this->hasPost('password') || $this->hasPost('code')) && $this->game->hasPassword())
			|| !$this->game->hasPassword());
	}

	public function validateRequest() {
		$this->color = $this->getPost('color');
		$password = $this->game->hasPassword() && $this->hasPost('password') ? $this->getPost('password') : '';
		$code = $this->game->hasPassword() && $this->hasPost('code') ? $this->getPost('code') : '';
		$colors = $this->game->getRemainingColors();
		$validColor = isset($colors[$this->color]);
		$validPassword = $this->game->isValidPassword($password) || ($code !== '' && $this->game->isValidInviteCode($code));
		if(!$validColor) {
			$this->colorError = 'This color is already in use';
		}
		if(!$validPassword) {
			if($password !== '') {
				$this->passwordError = 'The password you entered was incorrect';
			} else {
				$this->passwordError = 'The code you entered was incorrect';
			}
		}
		return $validColor && $validPassword;
	}

	public function getColor() {
		return $this->color;
	}

	public function getPasswordError() {
		return $this->passwordError;
	}

	public function getColorError() { 
		return $this->colorError;
	}

	public function getInviteCode() {
		return $this->inviteCode;
	}
}