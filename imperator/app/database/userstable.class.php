<?php
namespace imperator\database;

use imperator\Imperator;
class UsersTable extends Table {
	const NAME				= 'imperator_users';
	const COLUMN_UID		= 'uid';
	const COLUMN_WINS		= 'wins';
	const COLUMN_LOSSES		= 'losses';
	const COLUMN_SCORE		= 'score';

	/**
	 * @return \imperator\User[]:
	 */
	public function getUsersByScore() {
		$users = array();
		$u = $this->getManager()->getTable('OutsideUsers');
		$sql = 'SELECT s.*, u.'.$u::COLUMN_USERNAME.'
				FROM '.static::NAME.' AS s
				JOIN '.$u::NAME.' AS u
				ON(s.'.static::COLUMN_UID.' = u.'.$u::COLUMN_UID.')
				ORDER BY '.static::COLUMN_SCORE.' DESC';
		$query = $this->getManager()->query($sql);
		$userClass = Imperator::getSettings()->getUserClass();
		while($result = $query->fetchResult()) {
			$user = new $userClass(
				$result->getInt(static::COLUMN_UID),
				$result->get($u::COLUMN_USERNAME)
			);
			$user->setScore($result->getInt(static::COLUMN_SCORE));
			$user->setWins($result->getInt(static::COLUMN_WINS));
			$user->setLosses($result->getInt(static::COLUMN_LOSSES));
			$users[] = $user;
		}
		$query->free();
		return $users;
	}
}