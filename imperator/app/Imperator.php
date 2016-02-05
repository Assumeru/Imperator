<?php
namespace imperator;
require_once __DIR__.'/settings.php';
require_once __DIR__.'/AutoLoader.php';

class Imperator {
	/**
	 * @var \imperator\Logger
	 */
	private static $logger = null;
	/**
	 * @var \imperator\Settings
	 */
	private static $settings = null;
	private static $shutDownHandler = null;
	private static $initialised = false;

	/**
	 * @return \imperator\Logger The logger
	 */
	public static function getLogger() {
		return self::$logger;
	}

	/**
	 * @return \imperator\ShutDownHandler
	 */
	public static function getShutDownHandler() {
		return self::$shutDownHandler;
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

	public static function getCurrentUser() {
		$userClass = self::$settings->getUserClass();
		return $userClass::getCurrentUser();
	}

	/**
	 * Outputs a page.
	 * 
	 * @param string $pageClass The class of the page to render
	 */
	public static function renderPage($pageClass, $args = null) {
		self::$shutDownHandler->setMode(ShutDownHandler::MODE_OUTPUT_PAGE);
		$pageClass = '\\imperator\\page\\'.$pageClass;
		$user = self::getCurrentUser();
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
		} catch(exceptions\ImperatorException $e) {
			self::$logger->log(Logger::LEVEL_WARNING, $e);
			self::renderErrorPage($user);
		} catch(\Exception $e) {
			self::$logger->log(Logger::LEVEL_FATAL, $e);
			self::renderErrorPage($user);
		}
		self::$shutDownHandler->setMode(ShutDownHandler::MODE_OUTPUT_NOTHING);
	}

	public static function renderErrorPage(User $user) {
		$page = new \imperator\page\HTTP500();
		$page->render($user);
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
			self::$shutDownHandler = new ShutDownHandler();
			self::$shutDownHandler->register();
			self::$logger = new Logger(self::$settings->getLogPath(), Logger::LEVEL_DEBUG);
			error_reporting(0);
			ini_set('display_errors', false);
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

	public static function handleApiRequest($type, \imperator\api\Request $request, User $user, \imperator\websocket\ApiHandler $apiHandler = null) {
		$api = new $type($request, $user);
		if($apiHandler) {
			$api->setApiHandler($apiHandler);
		}
		return $api->handleRequest();
	}

	public static function stripIllegalCharacters($string) {
		$string = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
		$string = str_replace(self::$settings->getIllegalCharacters(), '', $string);
		return $string;
	}
}

Imperator::init();