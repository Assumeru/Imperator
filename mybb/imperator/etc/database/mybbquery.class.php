<?php
namespace imperator\outside\mybb\database;

class MyBBQuery implements \imperator\database\Query {
	private $query;

	public function __construct($query) {
		$this->query = $query;
	}

	public function fetchResult() {
		global $db;
		return new imperator\database\Result($db->fetch_array($this->query));
	}

	public function free() {
		global $db;
		$db->free_result($this->query);
	}

	public function getInsertId() {
		global $db;
		return $db->insert_id();
	}
}