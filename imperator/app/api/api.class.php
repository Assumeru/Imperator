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
						'id' => $territory->getId(),
						'name' => $this->user->getLanguage()->translate($territory->getName()),
						'units' => $territory->getUnits(),
						'uid' => $territory->getOwner()->getId()
					);
					if($this->request->getTime() === 0) {
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
				}
			}
		}
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
			return $this->reply(array(
				'error' => $this->user->getLanguage()->translate('All battles need to finish before units can be moved.')
			));
		}
		$game->startMove();
		return $this->reply(array(
			'state' => $game->getState(),
			'units' => $game->getUnits()
		));
	}

	protected function handleEndTurnRequest(\imperator\Game $game) {
		if($game->getState() == \imperator\Game::STATE_COMBAT && $game->hasOngoingBattles()) {
			return $this->reply(array(
				'error' => $this->user->getLanguage()->translate('You cannot end your turn without finishing all battles.')
			));
		}
		$reply = array();
		if($game->hasConquered()) {
			$reply['card'] = $game->giveCard($this->user, $this->request->getCard());
		}
		$game->nextTurn();
		$reply['turn'] = $game->getTurn();
		$reply['time'] = $game->getTime();
		$reply['state'] = $game->getState();
		return $this->reply($reply);
	}

	protected function handleForfeitRequest(\imperator\Game $game) {
		if($game->getState() == \imperator\Game::STATE_COMBAT && $game->playerHasToDefend($this->user)) {
			return $this->reply(array(
				'error' => $this->user->getLanguage()->translate('You cannot forfeit without finishing all battles.')
			));
		}
		$game->forfeit($user);
	}

	protected function reply($json) {}

	protected function handleInvalidRequest() {}

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
}