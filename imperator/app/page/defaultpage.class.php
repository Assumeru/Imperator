<?php
namespace imperator\page;
use imperator\Imperator;

abstract class DefaultPage extends Page {
	private $title = '';
	private $head = '';
	private $js = array();
	private $css = array();
	private $content;
	private $jsSettings = array();
	private $mainClass = 'container';

	public function render(\imperator\User $user) {
		$language = $user->getLanguage();
		Template::getInstance('page', $language)->setVariables(array(
			'language' => $language,
			'title' => $this->title,
			'settings' => Imperator::getSettings(),
			'javascriptSettings' => $this->getJavascriptSettings(),
			'css' => $this->css,
			'javascript' => $this->js,
			'footer' => $this->getFooter($user),
			'mainClass' => $this->mainClass,
			'body' => $this->content
		))->execute(true);
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
	 * Adds a javascript file to the head.
	 * 
	 * @param string $file The name of the file to add
	 */
	protected function addJavascript($file) {
		$this->js[] = $file;
	}

	/**
	 * Adds a css file to the head.
	 * 
	 * @param string $file The name of the file to add
	 */
	protected function addCSS($file) {
		$this->css[] = $file;
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
	protected function setBodyContents(Template $content) {
		$this->content = $content;
	}

	protected function getFooter(\imperator\User $user) {
		return Template::getInstance('footer', $user->getLanguage())->setVariables(array(
			'date' => date('Y')
		))->execute();
	}

	public static function getProfileLink(\imperator\Member $user) {
		return Template::getInstance('profile_link')->setVariables(array('user' => $user))->execute();
	}

	protected function addChatJavascript($gid, $canDelete = false) {
		$this->addJavascript('store.js');
		$this->addJavascript('api.js');
		$this->addJavascript('chat.js');
		$this->setJavascriptSetting('API', array(
			'longpollingURL' => Ajax::getURL()
		));
		$this->setJavascriptSetting('gid', $gid);
		$this->setJavascriptSetting('chat', array(
			'canDelete' => $canDelete,
			'template' => Template::getInstance('chat_message')->execute()
		));
	}

	protected function getChatBox(\imperator\User $user) {
		return Template::getInstance('chat', $user->getLanguage());
	}
}