<?php
namespace imperator\outside\mybb\database;

class MyBBQuery implements \imperator\database\Query {
	private $query;

	public function __construct($query) {
		$this->query = $query;
	}

	public function fetchResult() {
		global $db;
		$result = $db->fetch_array($this->query);
		if($result) {
			return new \imperator\database\Result($result);
		}
		return null;
	}

	public function free() {
		global $db;
		$db->free_result($this->query);
	}

	public function getInsertId() {
		global $db;
		return (int)$db->insert_id();
	}
}