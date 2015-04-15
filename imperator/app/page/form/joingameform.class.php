<?php
namespace imperator\page\form;

class JoinGameForm extends Form {
	private $game;
	private $color;
	private $passwordError = '';
	private $colorError = '';

	public function __construct(\imperator\Game $game) {
		parent::__construct();
		$this->game = $game;
	}

	public function hasBeenSubmitted() {
		return $this->hasPost('color')
			&& (($this->hasPost('password') && $this->game->hasPassword())
			|| !$this->game->hasPassword());
	}

	public function validateRequest() {
		$this->color = $this->getPost('color');
		$password = $this->game->hasPassword() ? $this->getPost('password') : null;
		$colors = $this->game->getRemainingColors();
		$validColor = isset($colors[$this->color]);
		$validPassword = $this->game->isValidPassword($password);
		if(!$validColor) {
			$this->colorError = 'This color is already in use';
		}
		if(!$validPassword) {
			$this->passwordError = 'The password you entered was incorrect';
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
}