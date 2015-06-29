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
		$language = $user->getLanguage();
		$this->setBodyContents(Template::getInstance('game_postgame')->replace(array(
			'title' => $this->game->getName(),
			'players' => $this->getPlayers($user),
			'player' => $language->translate('Player'),
			'mission' => $language->translate('Mission'),
			'mapheading' => $language->translate('Map'),
			'map' => $this->game->getMap()->getName(),
			'mapurl' => Map::getURL($this->game->getMap()->getId(), $this->game->getMap()->getName()),
			'chat' => $this->game->containsPlayer($user) ? $this->getChatBox($user) : ''
		))->getData());
		$this->addChatJavascript($this->game->getId());
		$this->setJavascriptSetting('postgame', true);
		parent::render($user);
	}

	private function getPlayers(\imperator\User $user) {
		$language = $user->getLanguage();
		$players = '';
		foreach($this->game->getPlayers() as $player) {
			$players .= Template::getInstance('game_postgame_player')->replace(array(
				'owner' => $player == $this->game->getOwner() ? $language->translate('(Owner)') : '',
				'user' => DefaultPage::getProfileLink($player),
				'mission' => $player->getMission()->getName(),
				'winner' => $player->getState() == \imperator\game\Player::STATE_VICTORIOUS ? $language->translate('(Winner)') : '',
				'missionDesc' => $player->getMission()->getDescription($language)
			))->getData();
		}
		return $players;
	}
}