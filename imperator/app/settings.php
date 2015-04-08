<?php
namespace imperator;

class Settings {
	private $basePath;
	private $baseURL;

	public function __construct() {
		$this->basePath = dirname(__DIR__);
		$this->baseURL = '';

		$url = explode('/', $_SERVER['REQUEST_URI']);
		if(count($url) > 1) {
			$path = str_replace(DIRECTORY_SEPARATOR, '/', $this->basePath);
			$start = strrpos($path, '/'.$url[1]);
			$this->baseURL = substr($path, $start);
		}
	}

	public function getBrandLink() {
		return $this->getBaseURL();
	}

	public function getBasePath() {
		return $this->basePath;
	}

	public function getBaseURL() {
		return $this->baseURL;
	}

	/**
	 * @return array An associative array of color names by hex value
	 */
	public function getPlayerColors() {
		return array(
			'FF0000' => 'Red',
			'FF7F00' => 'Orange',
			'FFFF00' => 'Yellow',
			'7FFF00' => 'Lime',
			'00FF00' => 'Green',
			'00FF7F' => 'Spring',
			'00FFFF' => 'Cyan',
			'007FFF' => 'Light blue',
			'0000FF' => 'Blue',
			'FF00FF' => 'Purple',
			'FF007F' => 'Pink',
			'7F00FF' => 'Indigo',
			'000000' => 'Black'
		);
	}

	public function getMaxGameNameLength() {
		return 128;
	}

	public function getLanguageClass() {
		return '\\imperator\\Language';
	}

	public function getTemplateClass() {
		return '\\imperator\\page\\Template';
	}

	public function getUserClass() {
		return '\\imperator\\User';
	}

	public function getAutoLoaderClass() {
		require_once $this->getBasePath().'/app/autoloader.class.php';
		return '\\imperator\\AutoLoader';
	}

	/**
	 * Returns the database manager.
	 * 
	 * @return imperator\database\DatabaseManager The database manager
	 */
	public function getDatabaseManager() {
		return null;
	}

	/**
	 * Returns the time between each update check in seconds.
	 * 
	 * @return number
	 */
	public function getLongPollingTimeout() {
		return 1;
	}

	/**
	 * Returns the maximum number of tries before timing out. Set to 0 for infinite tries.
	 * 
	 * @return number
	 */
	public function getMaxLongPollingTries() {
		return 30;
	}
}