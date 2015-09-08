<?php
namespace imperator\url;
use imperator\Imperator;

class ImageURL extends URL {
	public function __construct($path) {
		parent::__construct(sprintf(Imperator::getSettings()->getImageURL(), $path));
	}

	public static function getCardURL() {
		return new static('cards/%1$s.png');
	}

	public static function getTerritoryFlag(\imperator\map\Territory $territory) {
		return new static('flags/'.str_replace('_', '/', $territory->getId()).'.png');
	}

	public static function getRegionFlag(\imperator\map\Region $region) {
		return new static('flags/'.str_replace('_', '/', $region->getId()).'.png');
	}
}