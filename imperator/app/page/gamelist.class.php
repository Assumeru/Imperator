<?php
namespace imperator\page;
use imperator\Imperator;

class GameList extends DefaultPage {
	const NAME = 'Games';

	public function render(\imperator\User $user) {
		$this->addChatJavascript(0);
		$this->setTitle($user->getLanguage()->translate(static::NAME));
		$this->setBodyContents($this->getGameList($user));
		parent::render($user);
	}

	public function canBeUsedBy(\imperator\User $user) {
		return $user->isLoggedIn();
	}

	/**
	 * @param \imperator\User $user
	 * @return \imperator\Game[]
	 */
	protected function getGames(\imperator\User $user) {
		return Imperator::getDatabaseManager()->getTable('Games')->getAllGames();
	}

	private function getGameList(\imperator\User $user) {
		$lang = $user->getLanguage();
		$games = $this->getGames($user);
		$gameHTML = '';
		if(count($games) === 0) {
			$gameHTML = Template::getInstance('games_nogames')->replace(array(
				'nogames' => $lang->translate('There are no games available at this time.')
			))->getData();
		} else {
			foreach($games as $game) {
				$map = $game->getMap();
				$startedEndedLocked = '';
				if($game->hasStarted()) {
					$startedEndedLocked = 'started';
				} else if($game->hasEnded()) {
					$startedEndedLocked = 'ended';
				}
				if($game->hasPassword()) {
					$startedEndedLocked .= ' password';
				}
				$gameHTML .= Template::getInstance('games_game')->replace(array(
					'game' => $game->getName(),
					'map' => $lang->translate($map->getName()),
					'mapurl' => Map::getURL($map->getId(), $map->getName()),
					'players' => $lang->translate('%1$d / %2$d', $game->getNumberOfPlayers(), $map->getPlayers()),
					'host' => DefaultPage::getProfileLink($game->getOwner()->getUser()),
					'url' => Game::getURL($game),
					'startedendedlocked' => $startedEndedLocked
				))->getData();
			}
			$gameHTML = Template::getInstance('games_list')->replace(array(
				'games' => $gameHTML,
				'name' => $lang->translate('Name'),
				'map' => $lang->translate('Map'),
				'players' => $lang->translate('Players'),
				'host' => $lang->translate('Host')
			))->getData();
		}
		return Template::getInstance('games')->replace(array(
			'title' => $lang->translate('Games'),
			'games' => $gameHTML,
			'chat' => $this->getChatBox($user)
		))->getData();
	}
}