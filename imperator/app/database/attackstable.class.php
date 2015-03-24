<?php
namespace imperator\database;

class AttacksTable extends Table {
	const NAME						= 'imperator_attacks';
	const COLUMN_ATTACKING_TERRITOY	= 'a_territory';
	const COLUMN_DEFENDING_TERRITOY	= 'd_territory';
	const COLUMN_ATTACKING_UID		= 'a_uid';
	const COLUMN_DEFENDING_UID		= 'd_uid';
	const COLUMN_DICE_ROLL			= 'a_roll';
	const COLUMN_TRANSFERING_UNITS	= 'transfer';
}