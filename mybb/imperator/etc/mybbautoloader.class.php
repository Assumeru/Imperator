<?php
namespace imperator\outside\mybb;

class MyBBAutoLoader extends \imperator\AutoLoader {
	public function autoLoad($className) {
		if(strpos($className, 'imperator\\outside\\mybb\\') === 0) {
			$path = substr($className, 23);
			$path = str_replace('\\', '/', $path);
			$path = strtolower($path);
			include $this->getBasePath().'/etc/'.$path.'.class.php';
		} else {
			parent::autoLoad($className);
		}
	}
}