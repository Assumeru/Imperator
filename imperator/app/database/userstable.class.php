<?php
namespace imperator\database;
use imperator\Imperator;

class UsersTable extends Table {
	protected function register(DatabaseManager $manager) {
		$manager->registerTable('USERS', 'imperator_users', array(
			'USER' => 'uid',
			'WINS' => 'wins',
			'LOSSES' => 'losses',
			'SCORE' => 'score'
		));
	}

	public function create() {
		$this->getManager()->preparedStatement(
			'CREATE TABLE @USERS (
				@-USERS.USER INT REFERENCES @OUTSIDEUSERS(@-OUTSIDEUSERS.USER),
				@-USERS.WINS INT,
				@-USERS.LOSSES INT,
				@-USERS.SCORE INT,
				PRIMARY KEY(@-USERS.USER)
			)'
		);
	}

	/**
	 * @return \imperator\User[]:
	 */
	public function getUsersByScore() {
		$users = array();
		$query = $this->getManager()->preparedStatement(
			'SELECT @USERS.USER, @USERS.WINS, @USERS.LOSSES, @USERS.SCORE, @OUTSIDEUSERS.USERNAME
			FROM @USERS
			JOIN @OUTSIDEUSERS
			ON (@USERS.USER = @OUTSIDEUSERS.USER)
			ORDER BY @USERS.SCORE DESC');
		$userClass = Imperator::getSettings()->getUserClass();
		while($result = $query->fetchResult()) {
			$user = new $userClass(
				$result->getInt(0),
				$result->get(4)
			);
			$user->setScore($result->getInt(3));
			$user->setWins($result->getInt(1));
			$user->setLosses($result->getInt(2));
			$users[] = $user;
		}
		$query->free();
		return $users;
	}

	public function addLoss(\imperator\User $user) {
		$db = $this->getManager();
		if($db->exists($db->preparedStatement('SELECT 1 FROM @USERS WHERE @USERS.USER = %d', $user->getId()))) {
			$db->preparedStatement(
				'UPDATE @USERS
				SET @USERS.LOSSES = @USERS.LOSSES + 1, @USERS.SCORE = @USERS.SCORE - 1
				WHERE @USERS.USER = %d',
				$user->getId()
			)->free();
		} else {
			$this->getManager()->insert('@USERS', array(
				'@USERS.USER' => $user->getId(),
				'@USERS.LOSSES' => 1,
				'@USERS.SCORE' => -1
			))->free();
		}
	}

	public function addWin(\imperator\User $user, $score) {
		if($db->exists($db->preparedStatement('SELECT 1 FROM @USERS WHERE @USERS.USER = %d', $user->getId()))) {
			$db->preparedStatement(
				'UPDATE @USERS
				SET @USERS.WINS = @USERS.WINS + 1, @USERS.SCORE = @USERS.SCORE + %d
				WHERE @USERS.USER = %d',
				$score, $user->getId()
			)->free();
		} else {
			$this->getManager()->insert('@USERS', array(
				'@USERS.USER' => $user->getId(),
				'@USERS.WINS' => 1,
				'@USERS.SCORE' => 1
			))->free();
		}
	}
}