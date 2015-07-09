<?php
namespace imperator\api\requests;
use imperator\Imperator;

class PreGameUpdateRequest extends GameUpdateRequest {
	public function getType() {
		return 'pregame';
	}

	protected function fillOutput(\imperator\Game $game, \imperator\User $user, array $output) {
		if($game->hasStarted()) {
			$output['gameState'] = $user->getLanguage()->translate('This game has started.');
			$output['redirect'] = \imperator\page\Game::getURL($game);
		} else {
			$output['players'] = array();
			foreach($game->getPlayers() as $player) {
				$output['players'][] = \imperator\page\Template::getInstance('game_player', $user->getLanguage())->setVariables(array(
					'player' => $player,
					'game' => $game
				))->execute();
			}
			$output['maxPlayers'] = $game->getMap()->getPlayers();
			if($user->equals($game->getOwner()->getUser())) {
				$page = new \imperator\page\PreGame($game);
				$output['ownerControls'] = $page->getOwnerGameForm($user)->execute();
			}
		}
		return $output;
	}
}