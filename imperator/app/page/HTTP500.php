<?php
namespace imperator\page;

class HTTP500 extends HTTPError {
	public function __construct() {
		parent::__construct('500 Internal Server Error', 'An error occurred while loading this page.');
	}
}