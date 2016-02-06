<?php
namespace imperator\page;

class InGame extends DefaultPage {
	/**
	 * @var \imperator\Game
	 */
	private $game;

	public function __construct(\imperator\Game $game) {
		$this->game = $game;
		$game->loadMap();
	}

	public function render(\imperator\User $user) {
		$inGame = $this->game->containsPlayer($user);
		$language = $user->getLanguage();
		$this->setBodyContents(Template::getInstance('game', $user->getLanguage())->setVariables(array(
			'mapurl' => new \imperator\url\ImageURL('maps/map_'.$this->game->getMap()->getId().'.svg'),
			'chat' => $inGame ? $this->getChatBox($user) : null,
			'unitgraphics' => new \imperator\url\ImageURL('game/units.svg'),
			'cards' => $inGame ? $this->getCardList($this->game->getPlayerByUser($user)) : '',
			'game' => $this->game
		)));
		$mainClass = ' not-player';
		if($inGame) {
			$this->addChatJavascript($user, $this->game->getId(), $user->canDeleteChatMessages() || $this->game->getOwner()->equals($user));
			$mainClass = '';
		} else {
			$this->addApiJavascript($this->game->getId(), $user->getLanguage());
		}
		$this->renderJavascript($user);
		$this->setTitle($this->game->getName());
		$this->setMainClass('container-fluid'.$mainClass);
		$this->addCSS('game.css');
		parent::render($user);
	}

	private function renderJavascript(\imperator\User $user) {
		$language = $user->getLanguage();
		$this->setJavascriptSetting('uid', $user->getId());
		$this->setJavascriptSetting('templates', array(
			'card' => Template::getInstance('game_card')->setVariables(array(
				'url' => \imperator\url\ImageURL::getCardURL(),
				'name' => '%2$s'
			))->execute(),
			'okbutton' => Template::getInstance('button_ok', $language)->execute(),
			'cancelbutton' => Template::getInstance('button_cancel', $language)->execute(),
			'maxbutton' => Template::getInstance('button_max', $language)->execute(),
			'attackagainbutton' => Template::getInstance('button_attack_again', $language)->execute(),
			'dialogformfortify' => Template::getInstance('dialog_form_stack', $language)->execute(),
			'dialogformattack' => Template::getInstance('dialog_form_attack', $language)->execute(),
			'dialogformmove' => Template::getInstance('dialog_form_move', $language)->execute(),
			'dialogformdefend' => Template::getInstance('dialog_form_defend', $language)->execute(),
			'die' => Template::getInstance('die')->setVariables(array('roll' => '{$roll}', 'type' => '{$type}'))->execute(),
			'dialogattackresult' => Template::getInstance('dialog_attack_result', $language)->execute(),
			'combatlogentry' => Template::getInstance('game_combatlog_entry')->execute(),
			'discardcard' => Template::getInstance('dialog_card_discard', $language)->setVariables(array(
				'url' => \imperator\url\ImageURL::getCardURL(),
				'names' => \imperator\game\Cards::getCardNames($language)
			))->execute()
		));
		$this->setJavascriptSetting('language', array(
			'attack' => $language->translate('Attack'),
			'autorolldisabled' => $language->translate('%1$s has disabled Autoroll'),
			'card' => \imperator\game\Cards::getCardNames($language),
			'confirmfortify' => $language->translate('Are you sure you want to place %1$d units in %2$s?'),
			'confirmmove' => $language->translate('Are you sure you want to stop attacking?'),
			'confirmend' => $language->translate('Are you sure you want to end your turn?'),
			'conquered' => $language->translate('%1$s has been conquered'),
			'endedmessage' => $language->translate('This game has ended.'),
			'forfeit' => $language->translate('Are you sure you want to forfeit?'),
			'fortify' => $language->translate('Fortify %1$s'),
			'gameover' => $language->translate('Game Over'),
			'move' => $language->translate('Move'),
			'newcard' => $language->translate('You have received a new card!'),
			'vs' => $language->translate('%1$s vs. %2$s'),
			'endturn' => $language->translate('End turn'),
			'error' => $language->translate('An error has occurred'),
			'disconnected' => $language->translate('Connection to the server has been lost.')
		));
		$this->addJavascript('classes.js');
		$this->addJavascript('map.js');
		$this->addJavascript('game.js');
	}

	private function getCardList(\imperator\game\Player $user) {
		return Template::getInstance('game_cards')->setVariables(array(
			'url' => \imperator\url\ImageURL::getCardURL(),
			'names' => \imperator\game\Cards::getCardNames($user->getUser()->getLanguage()),
			'cards' => $user->getCards()
		));
	}

	protected function getFooter(\imperator\User $user) {
		return '';
	}
}