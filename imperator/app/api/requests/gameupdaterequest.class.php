<?php
namespace imperator\api\requests;
use \imperator\Imperator;

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
		if(!$game) {
			return array(
				'gameState' => $user->getLanguage()->translate('This game has been disbanded.'),
				'update' => time(),
				'redirect' => \imperator\page\GameList::getURL()
			);
		}
		$messages = $db->getTable('Chat')->getMessagesAfter($this->getGid(), $this->getTime());
		$output = array(
			'messages' => $this->getJSONMessages($messages),
			'update' => time(),
			'state' => $game->getState()
		);
		if($game->getTime() > $this->getTime() && $game->getState() != \imperator\Game::STATE_FINISHED) {
			$output = $this->fillOutput($game, $user, $output);
		}
		return $output;
	}

	protected function fillOutput(\imperator\Game $game, \imperator\User $user, array $output) {
		$game->loadMap();
		$language = $user->getLanguage();
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
				$outTerritory['name'] = $language->translate($territory->getName());
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
				'name' => $player->getName(),
				'playing' => $player->getState() !== \imperator\game\Player::STATE_GAME_OVER
			);
		}
		$output['turn'] = $game->getTurn();
		$output['units'] = $game->getUnits();
		$output['attacks'] = $this->getAttacks($game);
		if($game->containsPlayer($user)) {
			$player = $game->getPlayerByUser($user);
			$output['autoroll'] = $player->getAutoRoll();
			$cards = $player->getCards();
			$output['cards'] = array(
				\imperator\game\Cards::CARD_ARTILLERY => $cards->getArtillery(),
				\imperator\game\Cards::CARD_CAVALRY => $cards->getCavalry(),
				\imperator\game\Cards::CARD_INFANTRY => $cards->getInfantry(),
				\imperator\game\Cards::CARD_JOKER => $cards->getJokers()
			);
			$output['mission'] = array(
				'name' => $player->getMission()->getName(),
				'description' => $player->getMission()->getDescription($language)
			);
		}
		$output['combatlog'] = array();
		$logs = Imperator::getDatabaseManager()->getTable('CombatLog')->getLogsAfter($game, $this->getTime());
		foreach($logs as $log) {
			$output['combatlog'][] = array(
				'time' => date(DATE_ATOM, $log->getTime()),
				'message' => $log->getMessage($language)
			);
		}
		return $output;
	}
}