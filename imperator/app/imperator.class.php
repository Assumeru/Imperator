<?php
namespace imperator;
require_once __DIR__.'/settings.php';
require_once __DIR__.'/autoloader.class.php';

class Imperator {
	/**
	 * @var \imperator\Logger
	 */
	private static $logger = null;
	/**
	 * @var \imperator\Settings
	 */
	private static $settings = null;
	private static $initialised = false;

	/**
	 * @return \imperator\Logger The logger
	 */
	public static function getLogger() {
		return self::$logger;
	}

	public static function redirect($url) {
		header('Location: '.$url);
		die;
	}

	/**
	 * Determines what page the user is on and renders it.
	 */
	public static function showPage() {
		$page = 'Index';
		$args = null;
		if(isset($_GET['page'])) {
			$args = explode('/', $_GET['page']);
			$class = array_shift($args);
			$page = \imperator\page\Page::getInstanceByURL($class);
		}
		self::renderPage($page, $args);
	}

	/**
	 * Outputs a page.
	 * 
	 * @param string $pageClass The class of the page to render
	 */
	public static function renderPage($pageClass, $args = null) {
		$pageClass = '\\imperator\\page\\'.$pageClass;
		$userClass = self::$settings->getUserClass();
		$user = $userClass::getCurrentUser();
		if(!class_exists($pageClass)) {
			$page = new \imperator\page\HTTP404();
		} else {
			$page = new $pageClass($args);
			if(!$page->canBeUsedBy($user)) {
				$page = new \imperator\page\HTTP403();
			}
		}
		try {
			$page->render($user);
		} catch(\Exception $e) {
			$page = new \imperator\page\HTTP500();
			$page->render($user, $e);
		}
	}

	/**
	 * @return \imperator\database\DatabaseManager The database manager
	 */
	public static function getDatabaseManager() {
		return self::$settings->getDatabaseManager();
	}

	/**
	 * @return \imperator\Settings The settings
	 */
	public static function getSettings() {
		return self::$settings;
	}

	public static function init() {
		if(!self::$initialised) {
			$class = self::getSettingsClass();
			self::$settings = new $class();
			$class = self::$settings->getAutoLoaderClass();
			$autoLoader = new $class(self::$settings->getBasePath());
			$autoLoader->register();
			self::$logger = new Logger(self::$settings->getBasePath().'/var/log/', Logger::LEVEL_DEBUG);
			self::$initialised = true;
		}
	}

	private static function getSettingsClass() {
		$file = dirname(__DIR__).'/etc/settings.php';
		if(file_exists($file)) {
			$classes = get_declared_classes();
			require_once $file;
			foreach(array_diff(get_declared_classes(), $classes) as $class) {
				if(is_subclass_of($class, '\\imperator\\Settings')) {
					return $class;
				}
			}
		}
		return '\\imperator\\Settings';
	}

	public static function handleApiRequest($type, \imperator\api\Request $request, User $user) {
		$api = new $type($request, $user);
		return $api->handleRequest();
	}
}

Imperator::init();