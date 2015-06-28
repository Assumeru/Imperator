<?php
namespace imperator\page;

class Template {
	private $data;

	/**
	 * Returns a template.
	 * 
	 * @param string $template The template to retreive
	 * @return Template The template
	 */
	public static function getInstance($template) {
		$class = \imperator\Imperator::getSettings()->getTemplateClass();
		return new $class($template);
	}

	protected function __construct($template) {
		$path = \imperator\Imperator::getSettings()->getBasePath().'/etc/templates/'.$template.'.html';
		$data = null;
		if(file_exists($path)) {
			$data = file_get_contents($path);
		}
		$this->setData($data);
	}

	public function getData() {
		return $this->data;
	}

	protected function setData($data) {
		$this->data = $data;
	}

	public function replace(array $replace) {
		//extract($replace);
		foreach($replace as $key => $value) {
			$this->data = str_replace('{$'.$key.'}', $value, $this->data);
		}
		return $this;
	}
}