<?php
namespace imperator\outside\mybb\database;
use imperator\Imperator;

class MyBBDatabaseManager extends \imperator\database\DatabaseManager {
	private static $instance = null;
	private $table = null;

	protected function __construct() {
		parent::__construct();
		Imperator::getSettings()->includeMyBB();
	}

	public function query($query) {
		global $db;
		Imperator::getLogger()->log(\imperator\Logger::LEVEL_INFO, $query);
		$q = $db->query($query, true);
		if($db->error_number()) {
			throw new \imperator\exceptions\DatabaseException($db->error_string(), $db->error_number());
		}
		return new MyBBQuery($q);
	}

	public function escape($value) {
		global $db;
		return $db->escape_string($value);
	}

	public function getOutsideUsersTable() {
		if(!isset($this->table)) {
			Imperator::getSettings()->includeMyBB();
			$this->table = new MyBBUserTable($this);
		}
		return $this->table;
	}

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}