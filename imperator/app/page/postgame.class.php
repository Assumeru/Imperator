<?php
namespace imperator\page;

class PostGame extends DefaultPage {
	private $game;

	public function __construct(\imperator\Game $game) {
		$this->game = $game;
	}

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	public function render(\imperator\User $user) {
		$this->setTitle($this->game->getName());
		$this->setBodyContents(Template::getInstance('game_postgame', $user->getLanguage())->setVariables(array(
			'game' => $this->game,
			'chat' => $this->game->containsPlayer($user) ? $this->getChatBox($user) : null,
			'language' => $user->getLanguage()
		)));
		$this->addChatJavascript($user, $this->game->getId());
		$this->setJavascriptSetting('postgame', true);
		parent::render($user);
	}
}