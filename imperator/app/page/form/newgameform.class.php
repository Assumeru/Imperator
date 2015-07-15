<?php
namespace imperator\page\form;
use imperator\Imperator;

class NewGameForm extends Form {
	private $name;
	private $map;
	private $password;
	private $color;
	private $nameError;

	public function hasBeenSubmitted() {
		return $this->hasPost('name') && $this->hasPost('map') && $this->hasPost('color');
	}

	public function validateRequest() {
		$this->parseRequest();
		$name = $this->isValidName();
		if(!$name) {
			if(!empty($this->name)) {
				$this->nameError = 'Please enter a shorter name';
			} else {
				$this->nameError = 'Please enter a name';
			}
		}
		return $name
			&& $this->isValidColor()
			&& $this->isValidMap();
	}

	private function parseRequest() {
		$this->name = trim(Imperator::stripIllegalCharacters($this->getPost('name')));
		$this->map = $this->getPost('map');
		$this->color = $this->getPost('color');
		$this->password = $this->hasPost('password') ? $this->getPost('password') : null;
		if(empty($this->password)) {
			$this->password = null;
		}
	}
	
	private function isValidName() {
		return !empty($this->name) && strlen($this->name) <= Imperator::getSettings()->getMaxGameNameLength();
	}
	
	private function isValidMap() {
		if(is_numeric($this->map)) {
			$this->map = (int)$this->map;
			try {
				new \imperator\map\Map($this->map);
				return true;
			} catch(\InvalidArgumentException $e) {}
		}
		return false;
	}
	
	private function isValidColor() {
		$colors = array_keys(Imperator::getSettings()->getPlayerColors());
		return in_array($this->color, $colors);
	}

	public function getName() {
		return $this->name;
	}

	public function getColor() {
		return $this->color;
	}

	public function getMap() {
		return $this->map;
	}

	public function getPassword() {
		return $this->password;
	}

	public function getNameError() {
		return $this->nameError;
	}
}