<?php
namespace imperator;

class Language {
	private static $languages = array();
	private $lang;
	private $locale;
	private $direction;

	/**
	 * Returns a translator instance for the given language and locale.
	 * 
	 * @param string $lang The language
	 * @param string $locale The locale
	 * @return Language A translator object
	 */
	public static function getInstance($lang, $locale, $direction = 'ltr') {
		$index = $lang.'_'.$locale;
		if(!isset(self::$languages[$index])) {
			$class = Imperator::getSettings()->getLanguageClass();
			self::$languages[$index] = new $class($lang, $locale, $direction);
		}
		return self::$languages[$index];
	}

	protected function __construct($lang, $locale, $direction) {
		$this->lang = $lang;
		$this->locale = $locale;
		$this->direction = $direction;
	}

	public function getHtmlLang() {
		if(!empty($this->locale)) {
			return $this->lang.'-'.$this->locale;
		}
		return $this->lang;
	}

	public function getTextDirection() {
		return $this->direction;
	}

	/**
	 * Translates a string using this object's language and locale.
	 * 
	 * @param string $string The string to translate
	 * @param mixed $args,... Optional values to insert into the string
	 * @return string The translated string
	 */
	public function translate($string) {
		if(func_num_args() > 1) {
			$args = func_get_args();
			array_shift($args);
			return vsprintf($string, $args);
		}
		return $string;
	}

	/**
	 * Constructs a plural string to be translated.
	 * 
	 * @param string $singular
	 * @param string $plural
	 * @param int $amount
	 */
	public function plural($singular, $plural, $amount) {
		return new PluralString($singular, $plural, $amount);
	}
}