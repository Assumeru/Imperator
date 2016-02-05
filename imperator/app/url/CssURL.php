<?php
namespace imperator\url;
use imperator\Imperator;

class CssURL extends URL {
	public function __construct($path) {
		parent::__construct(sprintf(Imperator::getSettings()->getStylesheetURL(), $path));
	}
}