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

	public function getLogPath() {
		return $this->getBasePath().'/var/log/';
	}

	public function getBaseURL() {
		return $this->baseURL;
	}

	public function getImageURL() {
		return $this->getBaseURL().'/img/%1$s';
	}

	public function getStylesheetURL() {
		return $this->getBaseURL().'/css/%1$s';
	}

	public function getJavascriptURL() {
		return $this->getBaseURL().'/js/%1$s';
	}

	public function getNavigationPages() {
		return array(
			'\\imperator\\page\\Index',
			'\\imperator\\page\\YourGameList',
			'\\imperator\\page\\NewGame',
			'\\imperator\\page\\Rankings',
			'\\imperator\\page\\MapList',
			'\\imperator\\page\\About'
		);
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
	 * @return int
	 */
	public function getLongPollingTimeout() {
		return 1;
	}

	/**
	 * Returns the maximum number of tries before timing out. Set to 0 for infinite tries.
	 * 
	 * @return int
	 */
	public function getMaxLongPollingTries() {
		return 30;
	}

	public function getIllegalCharacters() {
		return array(
			chr(160),
			chr(173),
			chr(202)
		);
	}

	/**
	 * Returns the time until a chat message is deleted.
	 * 
	 * @return int Time in seconds
	 */
	public function getMaxChatMessageAge() {
		return 86400; //24h
	}

	public function getMinNumChatMessagesToPreserve() {
		return 10;
	}

	/**
	 * Returns the time until a finished game is deleted.
	 *
	 * @return int Time in seconds
	 */
	public function getMaxFinishedGameAge() {
		return 86400; //24h
	}

	/**
	 * Returns the time a game can be inactive for until it is deleted.
	 * 
	 * @return int Time in seconds
	 */
	public function getInactiveGameTime() {
		return 1209600; //2w
	}

	public function getWebSocketAddress() {
		return '127.0.0.1';
	}

	public function getWebSocketPort() {
		return '8080';
	}

	public function getWebSocketPath() {
		return '/websocket';
	}

	public function getWebSocketHandler() {
		return '\\imperator\\websocket\\ApiHandler';
	}
}