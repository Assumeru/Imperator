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
		if(strpos($className, 'imperator\\') === 0) {
			$path = substr($className, 10);
			$path = str_replace('\\', '/', $path);
	
			$locations = array(
				$this->basePath.'/app/'.$path.'.php',
				$this->basePath.'/etc/'.$path.'.php',
			);
			if(strpos($path, 'outside/') === 0) {
				$path = substr($path, 8);
				$locations[] = $this->basePath.'/etc/'.$path.'.php';
			}

			foreach($locations as $location) {
				if(file_exists($location)) {
					include $location;
					return;
				}
			}
		} else {
			$path = $this->basePath.'/lib/'.str_replace('\\', '/', $className).'.php';
			if(file_exists($path)) {
				include $path;
			}
		}
	}
}