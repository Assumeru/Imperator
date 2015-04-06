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
			ON(gj.'.$gj::COLUMN_UID.' = c.'.static::COLUMN_UID.')';
		}
		$sql .= '
			WHERE c.'.static::COLUMN_GID.' = '.$gid.'
			AND c.'.static::COLUMN_TIME.' > '.$time;
		$query = $this->getManager()->query($sql);
		$messages = array();
		$userClass = Imperator::getSettings()->getUserClass();
		while($result = $query->fetchResult()) {
			$user = new $userClass($result[static::COLUMN_UID], $result[$u::COLUMN_USERNAME]);
			if($gid !== 0 && isset($result[$gj::COLUMN_COLOR])) {
				$user->setColor($result[$gj::COLUMN_COLOR]);
			}
			$messages[] = new \imperator\chat\ChatMessage(
					$result[static::COLUMN_GID],
					$result[static::COLUMN_TIME],
					$user,
					$result[static::COLUMN_MESSAGE]);
		}
		$query->free();
		return $messages;
	}
}