<?php
namespace imperator\page\form;

abstract class Form {
	private $post;
	private $get;

	public function __construct() {
		$this->post = $_POST;
		\imperator\Imperator::getLogger()->log(\imperator\Logger::LEVEL_DEBUG, $_POST);
		$this->get = $_GET;
	}

	protected function hasPost($var) {
		return isset($this->post[$var]);
	}

	protected function hasGet($var) {
		return isset($this->get[$var]);
	}

	protected function getPost($var) {
		return $this->post[$var];
	}

	protected function getGet($var) {
		return $this->get[$var];
	}

	public function hasBeenSubmitted() {
		return false;
	}

	public function validateRequest() {
		return false;
	}
}