<?php
namespace imperator;

class GetText extends Language {
	private $mo = null;

	protected function __construct($lang, $locale, $direction) {
		parent::__construct($lang, $locale, $direction);
		$this->loadMOs();
	}

	private function loadMOs() {
		$settings = Imperator::getSettings();
		$directory = $settings->getLanguagePath().'/'.$this->getHtmlLang();
		if(!is_dir($directory)) {
			//Fall back on language without locale
			$directory = $settings->getLanguagePath().'/'.$this->getLang();
			if(!is_dir($directory)) {
				return;
			}
		}
		foreach(glob($directory.'/*.mo') as $file) {
			try {
				$mo = new \gettext\MO(file_get_contents($file));
			} catch(\InvalidArgumentException $e) {
				Imperator::getLogger()->w($e);
				continue;
			} catch(\gettext\pluralparser\ParseException $e) {
				Imperator::getLogger()->w($e);
				continue;
			}
			if($this->mo === null) {
				$this->mo = $mo;
			} else {
				$this->mo->merge($mo);
			}
		}
	}

	public function translate($string) {
		$args = func_get_args();
		$args[0] = $this->getTranslation($string);
		return call_user_func_array('parent::translate', $args);
	}

	private function getTranslation($key) {
		if($this->mo === null) {
			return $key;
		} else if($key instanceof PluralString) {
			return $this->mo->translate($key->getSingular(), $key->getPlural(), $key->getAmount());
		}
		return $this->mo->translate($key);
	}
}