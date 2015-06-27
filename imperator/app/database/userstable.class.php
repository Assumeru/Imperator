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

	public function addLoss(\imperator\User $user) {
		if($this->getManager()->rowExists(static::NAME, static::COLUMN_UID.' = '.$user->getId())) {
			$this->getManager()->query(
				'UPDATE '.static::NAME.'
				SET '.static::COLUMN_LOSSES.' = '.static::COLUMN_LOSSES.' + 1,
				'.static::COLUMN_SCORE.' = '.static::COLUMN_SCORE.' - 1
				WHERE '.static::COLUMN_UID.' = '.$user->getId())->free();
		} else {
			$this->getManager()->insert(static::NAME, array(
				static::COLUMN_UID => $user->getId(),
				static::COLUMN_LOSSES => 1,
				static::COLUMN_SCORE => -1
			))->free();
		}
	}

	public function addWin(\imperator\User $user, $score) {
		if($this->getManager()->rowExists(static::NAME, static::COLUMN_UID.' = '.$user->getId())) {
			$this->getManager()->query(
				'UPDATE '.static::NAME.'
				SET '.static::COLUMN_WINS.' = '.static::COLUMN_WINS.' + 1,
				'.static::COLUMN_SCORE.' = '.static::COLUMN_SCORE.' + '.$score.'
				WHERE '.static::COLUMN_UID.' = '.$user->getId())->free();
		} else {
			$this->getManager()->insert(static::NAME, array(
				static::COLUMN_UID => $user->getId(),
				static::COLUMN_WINS => 1,
				static::COLUMN_SCORE => 1
			))->free();
		}
	}
}