<?php
namespace imperator\api\requests;
use imperator\Imperator;

class GameUpdateRequest extends UpdateRequest {
	public function getType() {
		return 'game';
	}

	public function handle(\imperator\User $user) {
		$db = Imperator::getDatabaseManager();
		/**
		 * @var $game \imperator\Game
		*/
		$game = $db->getTable('Games')->getGameById($this->getGid());
		$messages = $db->getTable('Chat')->getMessagesAfter($this->getGid(), $this->getTime());
		$output = array(
			'messages' => $this->getJSONMessages($messages),
			'update' => time(),
			'state' => $game->getState(),
			'turn' => $game->getTurn()
		);
		if($game->getTime() > $this->getTime() && $game->getState() != \imperator\Game::STATE_FINISHED) {
			$game->loadMap();
			$output = $this->fillOutput($game, $user, $output);
		}
		return $output;
	}

	protected function fillOutput(\imperator\Game $game, \imperator\User $user, array $output) {
		if($this->getTime() === 0) {
			$output['regions'] = array();
			foreach($game->getMap()->getRegions() as $region) {
				$json = array(
					'id' => $region->getId(),
					'territories' => array(),
					'units' => $region->getUnitsPerTurn()
				);
				foreach($region->getTerritories() as $territory) {
					$json['territories'][] = $territory->getId();
				}
				$output['regions'][$region->getId()] = $json;
			}
		}
		$output['territories'] = array();
		foreach($game->getMap()->getTerritories() as $territory) {
			$outTerritory = array(
				'units' => $territory->getUnits(),
				'uid' => $territory->getOwner()->getId()
			);
			if($this->getTime() === 0) {
				$outTerritory['id'] = $territory->getId();
				$outTerritory['name'] = $user->getLanguage()->translate($territory->getName());
				$outTerritory['borders'] = array();
				foreach($territory->getBorders() as $border) {
					$outTerritory['borders'][] = $border->getId();
				}
			}
			$output['territories'][$territory->getId()] = $outTerritory;
		}
		$output['players'] = array();
		foreach($game->getPlayers() as $player) {
			$output['players'][$player->getId()] = array(
				'color' => $player->getColor(),
				'link' => \imperator\page\DefaultPage::getProfileLink($player),
				'id' => $player->getId(),
				'name' => $player->getName()
			);
		}
		$output['units'] = $game->getUnits();
		$output['attacks'] = $this->getAttacks($game);
		if($game->containsPlayer($user)) {
			$player = $game->getPlayerByUser($user);
			$output['autoroll'] = $player->getAutoRoll();
			$cards = $player->getCards($game);
			$output['cards'] = array(
				\imperator\game\Cards::CARD_ARTILLERY => $cards->getArtillery(),
				\imperator\game\Cards::CARD_CAVALRY => $cards->getCavalry(),
				\imperator\game\Cards::CARD_INFANTRY => $cards->getInfantry(),
				\imperator\game\Cards::CARD_JOKER => $cards->getJokers()
			);
		}
		return $output;
	}
}