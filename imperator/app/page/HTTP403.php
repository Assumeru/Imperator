<?php
namespace imperator\page;

class HTTP403 extends HTTPError {
	public function __construct() {
		parent::__construct('403 Forbidden', 'You are not allowed to view this page.');
	}
}