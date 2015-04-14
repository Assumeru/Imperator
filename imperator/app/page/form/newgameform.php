<?php
namespace imperator\page\form;
use imperator\Imperator;

class NewGameForm extends Form {
	private $name;
	private $map;
	private $password;
	private $color;

	public function hasBeenSubmitted() {
		return $this->hasPost('name') && $this->hasPost('map') && $this->hasPost('color');
	}

	public function validateRequest() {
		$this->name = trim($this->getPost('name'));
		$this->map = $this->getPost('map');
		$this->color = $this->getPost('color');
		$this->password = $this->hasPost('password') ? $this->getPost('password') : null;
		if(empty($this->password)) {
			$this->password = null;
		}
		return $this->isValidName()
			&& $this->isValidColor()
			&& $this->isValidMap();
	}
	
	private function isValidName() {
		return !empty($this->name) && strlen($this->name) <= Imperator::getSettings()->getMaxGameNameLength();
	}
	
	private function isValidMap() {
		if(is_numeric($this->map)) {
			return \imperator\map\Map::getInstance((int)$this->map) !== null;
		}
		return false;
	}
	
	private function isValidColor() {
		$colors = array_keys(Imperator::getSettings()->getPlayerColors());
		return in_array($this->color, $colors);
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
}