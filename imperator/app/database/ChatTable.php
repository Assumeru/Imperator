<?php
namespace imperator\database;
use imperator\Imperator;

class ChatTable extends Table {
	protected function register(DatabaseManager $manager) {
		$manager->registerTable('CHAT', 'imperator_chat', array(
			'GAME' => 'gid',
			'USER' => 'uid',
			'TIME' => 'time',
			'MESSAGE' => 'message'
		));
	}

	public function create() {
		$db = $this->getManager();
		$db->preparedStatement(
			'CREATE TABLE @CHAT (
				@-CHAT.GAME INT,
				@-CHAT.USER INT REFERENCES @OUTSIDEUSERS(@-OUTSIDEUSERS.USER),
				@-CHAT.TIME INT,
				@-CHAT.MESSAGE VARCHAR(512),
				PRIMARY KEY(@-CHAT.GAME, @-CHAT.USER, @-CHAT.TIME)
			) CHARACTER SET %s COLLATE %s',
			$db->getCharset(), $db->getCollation()
		);
	}

	public function drop() {
		$this->getManager()->preparedStatement('DROP TABLE IF EXISTS @CHAT');
	}

	public function removeMessagesFromGame(\imperator\Game $game) {
		$this->getManager()->preparedStatement(
			'DELETE FROM @CHAT WHERE @CHAT.GAME = %d',
			$game->getId()
		)->free();
	}

	public function hasMessagesAfter($gid, $time) {
		$db = $this->getManager();
		return $db->exists($db->preparedStatement(
			'SELECT 1 FROM @CHAT WHERE @CHAT.GAME = %d AND @CHAT.TIME > %d',
			$gid, $time
		));
	}

	/**
	 * Returns all messages created after $time for $gid.
	 * 
	 * @param int $gid
	 * @param int $time
	 * @return \imperator\chat\ChatMessage[]
	 */
	public function getMessagesAfter($gid, $time) {
		$sql = 'SELECT @CHAT.GAME, @CHAT.TIME, @CHAT.USER, @CHAT.MESSAGE, @OUTSIDEUSERS.USERNAME';
		if($gid !== 0) {
			$sql .= ', @GAMESJOINED.COLOR';
		}
		$sql .= ' FROM @CHAT
			JOIN @OUTSIDEUSERS
			ON(@OUTSIDEUSERS.USER = @CHAT.USER)';
		if($gid !== 0) {
			$sql .= ' LEFT JOIN @GAMESJOINED
			ON(@GAMESJOINED.USER = @CHAT.USER AND @GAMESJOINED.GAME = @CHAT.GAME)';
		}
		$sql .= ' WHERE @CHAT.GAME = %d AND @CHAT.TIME > %d ORDER BY @CHAT.TIME ASC';
		$query = $this->getManager()->preparedStatement($sql, $gid, $time);
		$messages = array();
		$userClass = Imperator::getSettings()->getUserClass();
		$users = array();
		while($result = $query->fetchResult()) {
			if(!isset($users[$result->getInt(2)])) {
				$users[$result->getInt(2)] = new $userClass($result->getInt(2), $result->get(4));
			}
			$user = $users[$result->getInt(2)];
			if($gid !== 0 && $result->get(5) !== null) {
				$user = new \imperator\game\Player($user);
				$user->setColor($result->get(5));
			}
			$messages[] = new \imperator\chat\ChatMessage(
				$result->getInt(0),
				$result->getInt(1),
				$user,
				$result->get(3)
			);
		}
		$query->free();
		return $messages;
	}

	public function insertMessage(\imperator\chat\ChatMessage $message) {
		$this->getManager()->insert('@CHAT', array(
			'@CHAT.GAME' => $message->getGid(),
			'@CHAT.MESSAGE' => $message->getMessage(),
			'@CHAT.TIME' => $message->getTime(),
			'@CHAT.USER' => $message->getUser()->getId()
		))->free();
	}

	public function deleteMessage(\imperator\chat\ChatMessage $message) {
		$this->getManager()->preparedStatement(
			'DELETE FROM @CHAT WHERE @CHAT.GAME = %d AND @CHAT.TIME = %d AND @CHAT.USER = %d',
			$message->getGid(), $message->getTime(), $message->getUser()->getId()
		)->free();
	}

	/**
	 * Deletes messages older than $time preserving a minimum of $keep messages.
	 * 
	 * @param int $time
	 * @param int $keep
	 */
	public function deleteOldMessages($time, $keep) {
		$db = $this->getManager();
		$query = $db->preparedStatement('SELECT COUNT(1) FROM @CHAT WHERE @CHAT.GAME = 0');
		if($result = $query->fetchResult()) {
			$limit = max(0, $result->getInt(0) - $keep);
			if($limit > 0) {
				Imperator::getLogger()->d('Deleting '.$limit.' chat messages.');
				$db->preparedStatement('DELETE FROM @CHAT WHERE @CHAT.TIME < %d AND @CHAT.GAME = 0 LIMIT %d', $time, $limit)->free();
			}
		}
		$query->free();
		return $limit;
	}

	/**
	 * Deletes messages from the given games.
	 * 
	 * @param int[] $games
	 */
	public function deleteGames(array $games) {
		$db = $this->getManager();
		array_unshift($games, 'DELETE FROM @CHAT WHERE @CHAT.GAME '.$db->createIn(count($games), '%d'));
		call_user_func_array(array($db, 'preparedStatement'), $games)->free();
	}
}