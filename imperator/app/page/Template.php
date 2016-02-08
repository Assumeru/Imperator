<?php
namespace imperator\page;
use imperator\Imperator;

class Template {
	private $template;
	private $language;
	private $variables;

	/**
	 * Returns a template.
	 * 
	 * @param string $template The template to retreive
	 * @return Template The template
	 */
	public static function getInstance($template, \imperator\Language $language = null) {
		$class = Imperator::getSettings()->getTemplateClass();
		return new $class($template, $language);
	}

	protected function __construct($template, \imperator\Language $language = null) {
		$this->language = $language;
		$this->template = $template;
	}

	protected function __() {
		return call_user_func_array(array($this->language, 'translate'), func_get_args());
	}

	protected function _p() {
		return call_user_func_array(array($this->language, 'plural'), func_get_args());
	}

	protected function includeTemplate($imperatorTemplate, array $imperatorVariables = null) {
		$imperatorPath = Imperator::getSettings()->getBasePath().'/etc/templates/'.$imperatorTemplate.'.phtml';
		if(file_exists($imperatorPath)) {
			if($imperatorVariables) {
				extract($imperatorVariables, EXTR_SKIP);
			}
			if($this->variables) {
				extract($this->variables, EXTR_SKIP);
			}
			include $imperatorPath;
		} else {
			throw new \imperator\exceptions\ImperatorException('Template "'.$imperatorTemplate.'" not found.');
		}
	}

	public function setVariables(array $variables) {
		$this->variables = $variables;
		return $this;
	}

	public function execute($print = false) {
		$level = ob_get_level() - 1;
		if(!$print) {
			ob_start();
		}
		try {
			$this->includeTemplate($this->template);
			if(!$print) {
				$out = ob_get_contents();
				ob_end_clean();
				return $out;
			}
		} catch(\Exception $e) {
			while(ob_get_level() > $level && ob_get_level() > 0) {
				ob_end_clean();
			}
			Imperator::getLogger()->w($e);
			throw new \imperator\exceptions\ImperatorException('Template execution failed for "'.$this->template.'"', $e->getCode(), $e);
		}
	}
}