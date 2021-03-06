<?php
namespace imperator\outside\mybb\database;

class MyBBQuery implements \imperator\database\Query {
	private $query;

	public function __construct($query) {
		$this->query = $query;
	}

	public function fetchResult() {
		global $db;
		$const = MYSQLI_NUM;
		switch($db->engine) {
			case 'mysql':
				$const = MYSQL_NUM;
				break;
			case 'pgsql':
				$const = PGSQL_NUM;
				break;
			case 'pdo':
				$const = \PDO::FETCH_NUM;
				break;
		}
		$result = $db->fetch_array($this->query, $const);
		if($result) {
			return new \imperator\database\Result($result);
		}
		return null;
	}

	public function free() {
		if(is_resource($this->query) || is_object($this->query)) {
			global $db;
			$db->free_result($this->query);
		}
	}

	public function getInsertId() {
		global $db;
		return (int)$db->insert_id();
	}
}