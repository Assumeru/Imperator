<?php
namespace imperator\page;

abstract class HTTPError extends ErrorPage {
	private $error;
	private $description;

	protected function __construct($error, $description) {
		$this->error = $error;
		$this->description = $description;
	}

	public function render(\imperator\User $user) {
		header('HTTP/1.0 '.$this->error);
		$language = $user->getLanguage();
		$this->setError($language->translate($this->error));
		$this->setDescription($language->translate($this->description));
		parent::render($user);
	}
}