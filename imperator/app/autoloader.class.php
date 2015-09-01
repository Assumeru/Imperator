<?php
namespace imperator;

class AutoLoader {
	private $basePath;

	public function __construct($basePath) {
		$this->basePath = $basePath;
	}

	protected function getBasePath() {
		return $this->basePath;
	}

	public function register() {
		spl_autoload_register(array($this, 'autoLoad'));
	}

	public function autoLoad($className) {
		$path = substr($className, 10);
		$path = str_replace('\\', '/', $path);
		$path = strtolower($path);

		$locations = array(
			$this->basePath.'/app/'.$path.'.class.php',
			$this->basePath.'/app/'.$path.'.interface.php',
			$this->basePath.'/etc/'.$path.'.class.php',
			$this->basePath.'/etc/'.$path.'.interface.php'
		);
		if(strpos($path, 'outside/') === 0) {
			$path = substr($path, 8);
			$locations[] = $this->basePath.'/etc/'.$path.'.class.php';
			$locations[] = $this->basePath.'/etc/'.$path.'.interface.php';
		}

		foreach($locations as $location) {
			if(file_exists($location)) {
				include $location;
				return;
			}
		}
	}
}