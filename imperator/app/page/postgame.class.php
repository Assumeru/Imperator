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
		$this->setBodyContents(Template::getInstance('game_postgame')->replace(array(
			'title' => $this->game->getName(),
			'players' => $this->getPlayers($user),
			'mapheading' => $language->translate('Map'),
			'playersheading' => $language->translate('Players'),
			'map' => $this->game->getMap()->getName(),
			'mapurl' => Map::getURL($this->game->getMap()->getId(), $this->game->getMap()->getName()),
			'chat' => $this->getChatBox($user)
		))->getData());
		$this->addChatJavascript($this->game->getId());
		parent::render($user);
	}

	private function getPlayers(\imperator\User $user) {
		$players = '';
		foreach($this->game->getPlayers() as $player) {
			$players .= Template::getInstance('game_player')->replace(array(
				'owner' => $player->equals($this->game->getOwner()) ? $user->getLanguage()->translate('(Owner)') : '',
				'user' => DefaultPage::getProfileLink($player)
			))->getData();
		}
		return $players;
	}
}