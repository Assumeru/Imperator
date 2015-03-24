<?php
namespace imperator\database;

interface Query {
	/**
	 * @return array An associative array representing a row.
	 */
	public function fetchResult();

	/**
	 * Frees the memory associated with a result.
	 */
	public function free();

	/**
	 * @return int The ID of the last inserted row.
	 */
	public function getInsertId();
}