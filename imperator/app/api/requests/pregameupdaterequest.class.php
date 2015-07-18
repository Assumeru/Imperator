<?php
namespace imperator\api\requests;

class PreGameUpdateRequest extends GameUpdateRequest {
	public function getType() {
		return 'pregame';
	}

	protected function fillOutput(\imperator\Game $game, \imperator\User $user, array $output) {
		if(!$game->containsPlayer($user)) {
			$output['gameState'] = $user->getLanguage()->translate('You have been kicked from this game.');
			$output['redirect'] = \imperator\page\GameList::getURL();
		} else if($game->hasStarted()) {
			$output['gameState'] = $user->getLanguage()->translate('This game has started.');
			$output['redirect'] = \imperator\page\Game::getURL($game);
		} else {
			$isOwner = $user->equals($game->getOwner()->getUser());
			$output['players'] = array();
			foreach($game->getPlayers() as $player) {
				$output['players'][] = \imperator\page\Template::getInstance('game_player', $user->getLanguage())->setVariables(array(
					'player' => $player,
					'game' => $game,
					'canKick' => $isOwner,
					'user' => $user
				))->execute();
			}
			$output['maxPlayers'] = $game->getMap()->getPlayers();
			if($isOwner) {
				$page = new \imperator\page\PreGame($game);
				$output['ownerControls'] = $page->getOwnerGameForm($user)->execute();
			}
		}
		return $output;
	}
}