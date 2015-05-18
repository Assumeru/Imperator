<?php
namespace imperator\database;
use imperator\Imperator;

class ChatTable extends Table {
	const NAME				= 'imperator_chat';
	const COLUMN_GID		= 'gid';
	const COLUMN_UID		= 'uid';
	const COLUMN_TIME		= 'time';
	const COLUMN_MESSAGE	= 'message';

	public function removeMessagesFromGame(\imperator\Game $game) {
		$this->getManager()->delete(static::NAME, static::COLUMN_GID.' = '.$game->getId())->free();
	}

	public function hasMessagesAfter($gid, $time) {
		return $this->getManager()->rowExists(static::NAME,
			static::COLUMN_GID.' = '.$gid.' AND '.
			static::COLUMN_TIME.' > '.$time);
	}

	/**
	 * Returns all messages created after $time for $gid.
	 * 
	 * @param int $gid
	 * @param int $time
	 * @return \imperator\chat\ChatMessage[]
	 */
	public function getMessagesAfter($gid, $time) {
		$u = $this->getManager()->getTable('OutsideUsers');
		if($gid !== 0) {
			$gj = $this->getManager()->getTable('GamesJoined');
		}
		$sql = '
			SELECT c.*, u.'.$u::COLUMN_USERNAME;
		if($gid !== 0) {
			$sql .= ', gj.'.$gj::COLUMN_COLOR;
		}
		$sql .= '
			FROM '.static::NAME.' AS c
			JOIN '.$u::NAME.' AS u
			ON(u.'.$u::COLUMN_UID.' = c.'.static::COLUMN_UID.')';
		if($gid !== 0) {
			$sql .= '
			LEFT JOIN '.$gj::NAME.' AS gj
			ON(gj.'.$gj::COLUMN_UID.' = c.'.static::COLUMN_UID.' AND gj.'.$gj::COLUMN_GID.' = c.'.static::COLUMN_GID.')';
		}
		$sql .= '
			WHERE c.'.static::COLUMN_GID.' = '.$gid.'
			AND c.'.static::COLUMN_TIME.' > '.$time;
		$query = $this->getManager()->query($sql);
		$messages = array();
		$userClass = Imperator::getSettings()->getUserClass();
		$users = array();
		while($result = $query->fetchResult()) {
			if(!isset($users[$result->getInt(static::COLUMN_UID)])) {
				$users[$result->getInt(static::COLUMN_UID)] = new $userClass($result->getInt(static::COLUMN_UID), $result->get($u::COLUMN_USERNAME));
			}
			$user = $users[$result->getInt(static::COLUMN_UID)];
			if($gid !== 0 && isset($result->get($gj::COLUMN_COLOR))) {
				$user->setColor($result->get($gj::COLUMN_COLOR));
			}
			$messages[] = new \imperator\chat\ChatMessage(
				$result->getInt(static::COLUMN_GID),
				$result->getInt(static::COLUMN_TIME),
				$user,
				$result->get(static::COLUMN_MESSAGE)
			);
		}
		$query->free();
		return $messages;
	}

	public function insertMessage(\imperator\chat\ChatMessage $message) {
		$this->getManager()->insert(static::NAME, array(
			static::COLUMN_GID => $message->getGid(),
			static::COLUMN_MESSAGE => $message->getMessage(),
			static::COLUMN_TIME => $message->getTime(),
			static::COLUMN_UID => $message->getUser()->getId()
		))->free();
	}

	public function deleteMessage(\imperator\chat\ChatMessage $message) {
		$this->getManager()->delete(static::NAME,
			static::COLUMN_GID.' = '.$message->getGid().'
			AND '.static::COLUMN_TIME.' = '.$message->getTime().'
			AND '.static::COLUMN_UID.' = '.$message->getUser()->getId()
		)->free();
	}
}