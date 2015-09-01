<?php
namespace imperator\outside\wordpress\database;
use imperator\Imperator;

class WordPressDatabaseManager extends \imperator\database\DatabaseManager {
	private static $instance = null;
	private $table = null;

	protected function __construct() {
		parent::__construct();
		Imperator::getSettings()->includeWordPress();
	}

	public function query($query) {
		return new WordPressQuery($query);
	}

	public function escape($value) {
		return esc_sql($value);
	}

	public function getOutsideUsersTable() {
		if(!isset($this->table)) {
			$this->table = new WordPressUserTable($this);
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