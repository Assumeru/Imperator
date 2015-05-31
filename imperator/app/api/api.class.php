<?php
namespace imperator\api;
use imperator\Imperator;

abstract class Api {
	const LONGPOLLING = '\\imperator\\api\\LongPolling';
	const WEBSOCKET = '\\imperator\\api\\WebSocket';
	private $user;
	private $request;

	public function __construct(Request $request, \imperator\User $user) {
		$this->request = $request;
		$this->user = $user;
	}

	protected function getUser() {
		return $this->user;
	}

	protected function getRequest() {
		return $this->request;
	}

	protected function sendError($message) {
		return $this->reply(array(
			'error' => array(
				'message' => $message,
				'mode' => $this->request->getMode(),
				'type' => $this->request->getType()
			)
		));
	}

	public function handleRequest() {
		if($this->request->isValid()) {
			if($this->request->getMode() == Request::MODE_UPDATE) {
				return $this->handleUpdateRequest();
			} else if($this->request->getMode() == Request::MODE_CHAT) {
				return $this->handleChatRequest();
			} else if($this->request->getMode() == Request::MODE_GAME) {
				return $this->handleGameRequest();
			}
		}
		return $this->handleInvalidRequest();
	}

	protected function handleUpdateRequest() {
		if($this->request->getType() == 'chat' && $this->canUseChat()) {
			return $this->handleChatUpdateRequest();
		} else if($this->request->getType() == 'game') {
			return $this->handleGameUpdateRequest();
		} else if($this->request->getType() == 'pregame') {
			return $this->handleGameUpdateRequest(true);
		}
		return $this->handleInvalidRequest();
	}

	protected function handleGameUpdateRequest($pregame = false) {
		$db = Imperator::getDatabaseManager();
		/**
		 * @var $game \imperator\Game
		 */
		$game = $db->getTable('Games')->getGameById($this->request->getGid());
		$messages = $db->getTable('Chat')->getMessagesAfter($this->request->getGid(), $this->request->getTime());
		$output = array(
			'messages' => $this->getJSONMessages($messages),
			'update' => time()
		);
		if($game->getTime() > $this->request->getTime()) {
			$game->loadMap();
			if($pregame) {
				$output['players'] = array();
				foreach($game->getPlayers() as $player) {
					$output['players'][] = \imperator\page\Template::getInstance('game_player')->replace(array(
						'color' => $player->getColor(),
						'owner' => $player->equals($game->getOwner()) ? $this->user->getLanguage()->translate('(Owner)') : '',
						'user' => \imperator\page\DefaultPage::getProfileLink($player)
					))->getData();
				}
				$output['maxPlayers'] = $game->getMap()->getPlayers();
				if($this->user->equals($game->getOwner())) {
					$page = new \imperator\page\PreGame($game);
					$output['ownerControls'] = $page->getOwnerGameForm($this->user);
				}
			} else {
				if($this->request->getTime() === 0) {
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
					if($this->request->getTime() === 0) {
						$outTerritory['id'] = $territory->getId();
						$outTerritory['name'] = $this->user->getLanguage()->translate($territory->getName());
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
				$output['state'] = $game->getState();
				$output['turn'] = $game->getTurn();
				$output['units'] = $game->getUnits();
				$output['attacks'] = $this->getAttacks($game);
				if($game->containsPlayer($this->user)) {
					$cards = $game->getPlayerByUser($this->user)->getCards($game);
					$output['cards'] = array(
						\imperator\game\Cards::CARD_ARTILLERY => $cards->getArtillery(),
						\imperator\game\Cards::CARD_CAVALRY => $cards->getCavalry(),
						\imperator\game\Cards::CARD_INFANTRY => $cards->getInfantry(),
						\imperator\game\Cards::CARD_JOKER => $cards->getJokers()
					);
				}
			}
		}
		return $this->reply($output);
	}

	protected function handleChatUpdateRequest() {
		$messages = Imperator::getDatabaseManager()->getTable('Chat')->getMessagesAfter($this->request->getGid(), $this->request->getTime());
		return $this->replyWithMessages($messages);
	}

	protected function getJSONMessages(array $messages) {
		$json = array();
		foreach($messages as $message) {
			$user = $message->getUser();
			$jsonMessage = array(
				'message' => $message->getMessage(),
				'user' => array(
					'id' => $user->getId(),
					'name' => $user->getName(),
					'url' => $user->getProfileLink()
				),
				'time' => date(DATE_ATOM, $message->getTime())
			);
			if($user->getColor()) {
				$jsonMessage['user']['color'] = $user->getColor();
			}
			$json[] = $jsonMessage;
		}
		return $json;
	}

	protected function replyWithMessages(array $messages) {
		return $this->reply(array('messages' => $this->getJSONMessages($messages), 'update' => time()));
	}

	protected function handleChatRequest() {
		if($this->request->getType() == 'add' && $this->canUseChat()) {
			return $this->handleChatAddRequest();
		} else if($this->request->getType() == 'delete' && $this->canDeleteFromChat()) {
			return $this->handleChatDeleteRequest();
		}
		return $this->handleInvalidRequest();
	}

	protected function handleChatAddRequest() {
		$message = new \imperator\chat\ChatMessage($this->request->getGid(), time(), $this->user, $this->request->getMessage());
		$message->insert();
	}

	protected function handleChatDeleteRequest() {
		$userClass = Imperator::getSettings()->getUserClass();
		$message = new \imperator\chat\ChatMessage($this->request->getGid(), $this->request->getTime(), new $userClass($this->request->getUid()), '');
		$message->delete();
	}

	protected function handleGameRequest() {
		$game = Imperator::getDatabaseManager()->getTable('Games')->getGameById($this->request->getGid());
		if($game->containsPlayer($this->user)) {
			if($this->request->getType() == 'forfeit') {
				return $this->handleForfeitRequest($game);
			} else if($game->getTurn() == $this->user->getId()) {
				if($this->request->getType() == 'fortify' && $game->getState() == \imperator\Game::STATE_TURN_START) {
					return $this->handleFortifyRequest($game);
				} else if($this->request->getType() == 'start-move' && $game->getState() == \imperator\Game::STATE_COMBAT) {
					return $this->handleStartMoveRequest($game);
				} else if($this->request->getType() == 'end-turn') {
					return $this->handleEndTurnRequest($game);
				} else if($this->request->getType() == 'play-cards' && ($game->getState() == \imperator\Game::STATE_TURN_START || $game->getState() == \imperator\Game::STATE_FORTIFY)) {
					return $this->handleCardsRequest($game);
				} else if($this->request->getType() == 'place-units' && ($game->getState() == \imperator\Game::STATE_TURN_START || $game->getState() == \imperator\Game::STATE_FORTIFY) && $game->getUnits() >= $this->request->getUnits()) {
					return $this->handlePlaceUnitsRequest($game);
				} else if($this->request->getType() == 'attack' && ($game->getState() == \imperator\Game::STATE_TURN_START || $game->getState() == \imperator\Game::STATE_COMBAT)) {
					return $this->handleAttackRequest($game);
				}
			}
		}
		return $this->handleInvalidRequest();
	}

	protected function handleAttackRequest(\imperator\Game $game) {
		$to = $game->getMap()->getTerritoryById($this->request->getTo());
		$from = $game->getMap()->getTerritoryById($this->request->getFrom());
		if($to && $from) {
			$game->loadMap();
			if($from->getOwner()->equals($this->user) && !$to->getOwner()->equals($this->user) && $this->request->getUnits() < $from->getUnits() && $this->request->getMove() < $from->getUnits() && $from->borders($to)) {
				if($game->territoriesAreInCombat($to, $from)) {
					return $this->sendError($this->user->getLanguage()->translate('One of these territories is already engaged in combat.'));
				}
				$attack = new \imperator\game\Attack($to, $from);
				$attack->rollAttack($this->request->getUnits());
				$game->setState(\imperator\Game::STATE_COMBAT);
				$game->setTime(time());
				Imperator::getDatabaseManager()->getTable('Games')->saveState($game);
				if($to->getUnits() === 1 || $to->getOwner()->getAutoRoll() || $attack->attackerCannotWin()) {
					$attack->autoRollDefence();
					$game->executeAttack($attack);
					return $this->reply(array(
						'territories' => array(
							$to->getId() => array(
								'uid' => $to->getOwner()->getId(),
								'units' => $to->getUnits()
							),
							$from->getId() => array(
								'uid' => $from->getOwner()->getId(),
								'units' => $from->getUnits()
							)
						),
						'state' => $game->getState(),
						'time' => $game->getTime()
					));
				}
				$attack->save();
				return $this->reply(array(
					'attacks' => $this->getAttacks($game)
				));
			}
		}
		return $this->handleInvalidRequest();
	}

	protected function handlePlaceUnitsRequest(\imperator\Game $game) {
		$territory = $game->getMap()->getTerritoryById($this->request->getTerritory());
		if($territory) {
			$game->loadMap();
			if($territory->getOwner()->equals($this->user)) {
				$game->placeUnits($territory, $this->request->getUnits());
				return $this->reply(array(
					'state' => $game->getState(),
					'time' => $game->getTime(),
					'units' => $game->getUnits(),
					'territories' => array(
						$territory->getId() => array(
							'units' => $territory->getUnits()
						)
					)
				));
			}
		}
		return $this->handleInvalidRequest();
	}

	protected function handleCardsRequest(\imperator\Game $game) {
		$user = $game->getPlayerByUser($this->user);
		$cards = $user->getCards($game);
		if($cards->canPlayCombination($this->request->getUnits())) {
			$game->playCardCombination($user, $this->request->getUnits());
			return $this->reply(array(
				'units' => $game->getUnits(),
				'cards' => array(
					\imperator\game\Cards::CARD_ARTILLERY => $cards->getArtillery(),
					\imperator\game\Cards::CARD_CAVALRY => $cards->getCavalry(),
					\imperator\game\Cards::CARD_INFANTRY => $cards->getInfantry(),
					\imperator\game\Cards::CARD_JOKER => $cards->getJokers()
				),
				'time' => $game->getTime()
			));
		}
		return $this->sendError($this->user->getLanguage()->translate('You do not have the required cards to place %1$d units.', $this->request->getUnits()));
	}

	protected function handleFortifyRequest(\imperator\Game $game) {
		$game->fortify($this->user);
		return $this->reply(array(
			'units' => $game->getUnits(),
			'state' => $game->getState()
		));
	}

	protected function handleStartMoveRequest(\imperator\Game $game) {
		if($game->hasOngoingBattles()) {
			return $this->sendError($this->user->getLanguage()->translate('All battles need to finish before units can be moved.'));
		}
		$game->startMove();
		return $this->reply(array(
			'state' => $game->getState(),
			'units' => $game->getUnits()
		));
	}

	protected function handleEndTurnRequest(\imperator\Game $game) {
		if($game->getState() == \imperator\Game::STATE_COMBAT && $game->hasOngoingBattles()) {
			return $this->sendError($this->user->getLanguage()->translate('You cannot end your turn without finishing all battles.'));
		}
		$game->loadMap();
		$reply = array();
		if($game->hasConquered()) {
			$reply['card'] = $game->giveCard($game->getPlayerByUser($this->user), $this->request->getCard());
		}
		$game->nextTurn();
		$reply['turn'] = $game->getTurn();
		$reply['time'] = $game->getTime();
		$reply['state'] = $game->getState();
		return $this->reply($reply);
	}

	protected function handleForfeitRequest(\imperator\Game $game) {
		if($game->getState() == \imperator\Game::STATE_COMBAT && $game->playerHasToDefend($this->user)) {
			return $this->sendError($this->user->getLanguage()->translate('You cannot forfeit without finishing all battles.'));
		}
		$game->forfeit($user);
	}

	protected function reply($json) {}

	protected function handleInvalidRequest() {
		return $this->sendError('Bad request');
	}

	protected function canUseChat() {
		if($this->request->getGid() === 0) {
			return true;
		}
		return $this->isPlayerInGame();
	}

	protected function isPlayerInGame() {
		return Imperator::getDatabaseManager()->getTable('GamesJoined')->gameContainsPlayer($this->request->getGid(), $this->user);
	}

	protected function isGameOwner() {
		return Imperator::getDatabaseManager()->getTable('Games')->gameOwnerEquals($this->request->getGid(), $this->user);
	}

	protected function canDeleteFromChat() {
		return $this->user->canDeleteChatMessages() || $this->isGameOwner();
	}

	protected function getAttacks(\imperator\Game $game) {
		$out = array();
		foreach($game->getAttacks() as $attack) {
			$out[] = array(
				'attacker' => $attack->getAttacker()->getId(),
				'defender' => $attack->getDefender()->getId(),
				'roll' => $attack->getAttackRoll()
			);
		}
		return $out;
	}
}