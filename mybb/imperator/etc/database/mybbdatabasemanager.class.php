<?php
namespace imperator\outside\mybb\database;
use imperator\Imperator;

class MyBBDatabaseManager extends \imperator\database\DatabaseManager {
	private static $instance = null;
	private $table = null;

	private function __construct() {
		Imperator::getSettings()->includeMyBB();
	}

	public function query($query) {
		global $db;
		return new MyBBQuery($db->query($query));
	}

	public function escape($value) {
		global $db;
		return $db->escape_string($value);
	}

	public function getTable($name) {
		if($name == 'OutsideUsers') {
			if(!$this->table) {
				Imperator::getSettings()->includeMyBB();
				$this->table = new MyBBUserTable($this);
			}
			return $this->table;
		} else {
			return parent::getTable($name);
		}
	}

	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}