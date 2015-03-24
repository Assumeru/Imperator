<?php
namespace imperator\page;

class HTTP404 extends HTTPError {
	public function __construct() {
		parent::__construct('404 Not found', 'The specified page could not be found.');
	}
}