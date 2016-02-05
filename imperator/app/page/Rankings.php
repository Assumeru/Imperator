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
		$this->setBodyContents(Template::getInstance('rankings', $user->getLanguage())->setVariables(array(
			'users' => Imperator::getDatabaseManager()->getUsersTable()->getUsersByScore()
		)));
		$this->addJavascript('jquery.tablesorter.min.js');
		$this->addJavascript('tablesorter.js');
		parent::render($user);
	}
}