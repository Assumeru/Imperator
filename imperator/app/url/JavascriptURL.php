<?php
namespace imperator\url;
use imperator\Imperator;

class JavascriptURL extends URL {
	public function __construct($path) {
		parent::__construct(sprintf(Imperator::getSettings()->getJavascriptURL(), $path));
	}
}