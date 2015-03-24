<?php
namespace imperator\outside\mybb\database;

//Because class constants don't allow string concatenation <PHP5.6
define('IMPERATOR_MYBB_USER_TABLE_NAME', TABLE_PREFIX.'users');

class MyBBUserTable extends \imperator\database\OutsideUsersTable {
	const NAME = IMPERATOR_MYBB_USER_TABLE_NAME;
}