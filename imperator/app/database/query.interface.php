<?php
namespace imperator\database;

interface Query {
	/**
	 * @return Result A row.
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