<?php
namespace imperator\outside\wordpress\database;

class WordPressQuery implements \imperator\database\Query {
	private $results;
	private $fetch;

	public function __construct($query) {
		global $wpdb;
		$this->results = $wpdb->get_results($query, ARRAY_N);
		$this->fetch = 0;
	}

	public function fetchResult() {
		if(isset($this->results[$this->fetch])) {
			$result = new \imperator\database\Result($this->results[$this->fetch]);
			$this->fetch++;
			return $result;
		}
		return null;
	}

	public function free() {
		global $wpdb;
		$wpdb->flush();
	}

	public function getInsertId() {
		global $wpdb;
		return $wpdb->insert_id;
	}
}