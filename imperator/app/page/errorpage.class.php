<?php
namespace imperator\page;

abstract class ErrorPage extends DefaultPage {
	private $error;
	private $description;

	public function render(\imperator\User $user) {
		$this->setBodyContents($this->getErrorPage($user));
		parent::render($user);
	}

	protected function setError($error) {
		$this->error = $error;
		$this->setTitle($error);
	}

	protected function setDescription($description) {
		$this->description = $description;
	}

	protected function getErrorPage(\imperator\User $user) {
		return Template::getInstance('errorpage')->replace(array(
			'error' => $this->error,
			'description' => $this->description
		))->getData();
	}

	public function canBeUsedBy(\imperator\User $user) {
		return true;
	}
}