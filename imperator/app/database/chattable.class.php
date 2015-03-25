<?php
namespace imperator\database;

class ChatTable extends Table {
	const NAME				= 'imperator_chat';
	const COLUMN_GID		= 'gid';
	const COLUMN_UID		= 'uid';
	const COLUMN_TIME		= 'time';
	const COLUMN_MESSAGE	= 'message';

	public function removeMessagesFromGame(\imperator\Game $game) {
		$this->getManager()->delete(static::NAME, static::COLUMN_GID.' = '.$game->getId());
	}
}