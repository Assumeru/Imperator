<?php
namespace imperator\page;

abstract class ErrorPage extends DefaultPage {
	private $error;
	private $description;

	public function render(\imperator\User $user) {
		$this->setBodyContents(Template::getInstance('errorpage')->setVariables(array(
			'error' => $this->error,
			'description' => $this->description
		)));
		parent::render($user);
	}

	protected function setError($error) {
		$this->error = $error;
		$this->setTitle($error);
	}

	protected function setDescription($description) {
		$this->description = $description;
	}

	public function canBeUsedBy(\imperator\User $user) {
		return true;
	}
}