<?php
namespace imperator\database;

class Result {
	private $data;

	public function __construct(array $data) {
		$this->data = $data;
	}

	/**
	 * @param string $column
	 * @return int
	 */
	public function getInt($column) {
		if(isset($this->data[$column])) {
			return (int)$this->data[$column];
		}
		return null;
	}

	/**
	 * @param string $column
	 * @return float
	 */
	public function getFloat($column) {
		if(isset($this->data[$column])) {
			return (float)$this->data[$column];
		}
		return null;
	}

	/**
	 * @param string $column
	 * @return string
	 */
	public function get($column) {
		if(isset($this->data[$column])) {
			return $this->data[$column];
		}
		return null;
	}

	/**
	 * @param string $column
	 * @return bool
	 */
	public function getBool($column) {
		if(isset($this->data[$column])) {
			return (bool)$this->data[$column];
		}
		return null;
	}
}