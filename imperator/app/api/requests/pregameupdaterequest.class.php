<?php
namespace imperator\api\requests;
use imperator\Imperator;

class PreGameUpdateRequest extends UpdateRequest {
	public function getType() {
		return 'pregame';
	}

	protected function fillOutput(\imperator\Game $game, \imperator\User $user, array $output) {
		$output['players'] = array();
		foreach($game->getPlayers() as $player) {
			$output['players'][] = \imperator\page\Template::getInstance('game_player')->replace(array(
				'color' => $player->getColor(),
				'owner' => $player->equals($game->getOwner()) ? $user->getLanguage()->translate('(Owner)') : '',
				'user' => \imperator\page\DefaultPage::getProfileLink($player)
			))->getData();
		}
		$output['maxPlayers'] = $game->getMap()->getPlayers();
		if($this->user->equals($game->getOwner())) {
			$page = new \imperator\page\PreGame($game);
			$output['ownerControls'] = $page->getOwnerGameForm($user);
		}
		return $output;
	}
}