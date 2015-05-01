<?php
namespace imperator\page;
use imperator\Imperator;

class Rankings extends DefaultPage {
	const NAME = 'Rankings';
	const URL = 'rankings';

	public function canBeUsedBy(\imperator\User $user) {
		return true;
	}

	public function render(\imperator\User $user) {
		$this->setTitle($user->getLanguage()->translate(self::NAME));
		$this->setBodyContents($this->getRankings($user));
		$this->addJavascript('jquery.tablesorter.min.js');
		$this->addJavascript('tablesorter.js');
		parent::render($user);
	}

	private function getRankings(\imperator\User $user) {
		$language = $user->getLanguage();
		return Template::getInstance('rankings')->replace(array(
			'title' => $language->translate(self::NAME),
			'num' => $language->translate('#'),
			'name' => $language->translate('Name'),
			'score' => $language->translate('Score'),
			'wins' => $language->translate('Wins'),
			'losses' => $language->translate('Losses'),
			'users' => $this->getUsers()
		))->getData();
	}

	private function getUsers() {
		$users = '';
		$number = 1;
		$userList = Imperator::getDatabaseManager()->getTable('Users')->getUsersByScore();
		foreach($userList as $user) {
			$users .= Template::getInstance('rankings_user')->replace(array(
				'number' => $number,
				'name' => DefaultPage::getProfileLink($user),
				'score' => $user->getScore(),
				'wins' => $user->getWins(),
				'losses' => $user->getLosses()
			))->getData();
			$number++;
		}
		return $users;
	}
}