<?php
namespace imperator\page;
use imperator\Imperator;

abstract class DefaultPage extends Page {
	private $title = '';
	private $head = '';
	private $js = '';
	private $css = '';
	private $content = '';
	private $jsSettings = array();
	private $mainClass = 'container';

	public function render(\imperator\User $user) {
		$language = $user->getLanguage();
		echo Template::getInstance('page')->replace(array(
			'lang' => $language->getHtmlLang(),
			'dir' => $language->getTextDirection(),
			'head' => $this->getHead($user),
			'header' => $this->getHeader($user),
			'body' => $this->getBody($user),
			'footer' => $this->getFooter($user),
			'container' => $this->mainClass
		))->getData();
	}

	/**
	 * Sets the class attribute of the main div.
	 * 
	 * @param string $mainClass The contents of the class attribute
	 */
	protected function setMainClass($mainClass) {
		$this->mainClass = $mainClass;
	}

	/**
	 * Sets the page title.
	 * 
	 * @param string $title The title of the page
	 */
	protected function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Adds additional content to the head.
	 * 
	 * @param string $head Additional HTML
	 */
	protected function setHead($head) {
		$this->head = $head;
	}

	/**
	 * Adds a javascript file to the head.
	 * 
	 * @param string $file The name of the file to add
	 */
	protected function addJavascript($file) {
		$this->js .= '<script src="'.Imperator::getSettings()->getBaseURL().'/js/'.$file.'"></script>'."\n";
	}

	/**
	 * Adds a css file to the head.
	 * 
	 * @param string $file The name of the file to add
	 */
	protected function addCSS($file) {
		$this->css .= '<link href="'.Imperator::getSettings()->getBaseURL().'/css/'.$file.'" type="text/css" rel="stylesheet" />'."\n";
	}

	/**
	 * Makes a variable available in javascript.
	 * 
	 * @param string $key The name of the variable
	 * @param multitype $value The variable
	 */
	protected function setJavascriptSetting($key, $value) {
		$this->jsSettings[$key] = $value;
	}

	protected function getJavascriptSettings() {
		return '<script>var Imperator = '.json_encode(
			array('settings' => $this->jsSettings)
		).';</script>'."\n";
	}

	/**
	 * Adds content to the body of the page.
	 * 
	 * @param string $content The HTML to add
	 */
	protected function setBodyContents($content) {
		$this->content = $content;
	}

	protected function getHead(\imperator\User $user) {
		return Template::getInstance('head')->replace(array(
			'title' => $user->getLanguage()->translate('Imperator | %1$s', $this->title),
			'head' => $this->getJavascriptSettings().$this->head."\n".$this->css.$this->js,
			'basepath' => Imperator::getSettings()->getBaseURL()
		))->getData();
	}

	protected function getHeader(\imperator\User $user) {
		return Template::getInstance('header')->replace(array(
			'brandlink' => Imperator::getSettings()->getBrandLink(),
			'nav' => $this->getNavigation($user)
		))->getData();
	}

	protected function getBody(\imperator\User $user) {
		return Template::getInstance('body')->replace(array(
			'content' => $this->content
		))->getData();
	}

	protected function getFooter(\imperator\User $user) {
		return Template::getInstance('footer')->replace(array(
			'copyright' => $user->getLanguage()->translate('&copy; %1$d %2$s.', date('Y'), Template::getInstance('copyright')->getData())
		))->getData();
	}

	protected function getNavigation(\imperator\User $user) {
		$settings = Imperator::getSettings();
		$language = $user->getLanguage();
		$nav = '';
		$elements = array('Index', 'YourGameList', 'NewGame', 'Rankings', 'MapList', 'About');
		foreach($elements as $element) {
			$page = '\\imperator\\page\\'.$element;
			$nav .= Template::getInstance('nav_element')->replace(array(
				'url' => $page::getURL(),
				'name' => $language->translate($page::NAME)
			))->getData();
		}
		return $nav;
	}

	public static function getProfileLink(\imperator\User $user) {
		if($url = $user->getProfileLink()) {
			if($user->getColor()) {
			return Template::getInstance('profile_link_color')->replace(array(
				'url' => $url,
				'name' => htmlentities($user->getName()),
				'color' => $user->getColor()
			))->getData();
			}
			return Template::getInstance('profile_link')->replace(array(
				'url' => $url,
				'name' => htmlentities($user->getName())
			))->getData();
		}
		return Template::getInstance('profile_nolink')->replace(array(
			'name' => htmlentities($user->getName())
		))->getData();
	}

	protected function addChatJavascript($gid) {
		$this->addJavascript('store.js');
		$this->addJavascript('api.js');
		$this->addJavascript('chat.js');
		$this->setJavascriptSetting('API', array(
			'longpollingURL' => Ajax::getURL()
		));
		$this->setJavascriptSetting('gid', $gid);
	}

	protected function getChatBox(\imperator\User $user) {
		$language = $user->getLanguage();
		return Template::getInstance('chat')->replace(array(
			'loading' => $language->translate('Loading messages...'),
			'submit' => $language->translate('Chat'),
			'placeholder' => $language->translate('Say something'),
			'chatscrollingtitle' => $language->translate('Enable this to automatically scroll down to the latest message')
		))->getData();
	}
}