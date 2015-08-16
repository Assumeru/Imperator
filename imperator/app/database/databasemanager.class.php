<?php
namespace imperator\database;
use imperator\Imperator;

abstract class DatabaseManager {
	private $tables = array();
	private $replacements = array();

	protected function __construct() {
		$this->getAttacksTable();
		$this->getChatTable();
		$this->getCombatLogTable();
		$this->getGamesJoinedTable();
		$this->getGamesTable();
		$this->getOutsideUsersTable();
		$this->getTerritoriesTable();
		$this->getUsersTable();
	}

	/**
	 * Performs a database query.
	 * 
	 * @param string $query The query
	 * @return Query A query object
	 */
	protected function query($query) {
		return null;
	}

	public function escape($value) {
		return $value;
	}

	/**
	 * @param string $table
	 * @param array $values
	 * @return \imperator\database\Query
	 */
	public function insert($table, array $values) {
		$format = array();
		foreach($values as $value) {
			$format[] = '%s';
		}
		$fields = implode(', ', array_keys($values));
		$args = array('INSERT INTO '.$table.' ('.$fields.') VALUES('.implode(', ', $format).')');
		return call_user_func_array(array($this, 'preparedStatement'), array_merge($args, $values));
	}

	/**
	 * @param string $table
	 * @param array $values
	 * @return \imperator\database\Query
	 */
	public function insertMultiple($table, array $values) {
		$fields = implode(', ', array_keys($values[0]));
		$insert = array();
		$args = array();
		foreach($values as $row) {
			$format = array();
			foreach($row as $column) {
				$format[] = '%s';
			}
			$insert[] = '('.implode(', ', $format).')';
			$args = array_merge($args, array_values($row));
		}
		array_unshift($args, 'INSERT INTO '.$table.' ('.$fields.') VALUES '.implode(', ', $insert));
		return call_user_func_array(array($this, 'preparedStatement'), $args);
	}

	/**
	 * @param string $table
	 * @param array $values
	 * @param string $where
	 * @return \imperator\database\Query
	 */
	public function update($table, array $values, $where) {
		foreach($values as $key => $value) {
			$values[$key] = '`'.$key.'` = '.$this->escape($value);
		}
		$sql = 'UPDATE '.$table.' SET '.implode(', ', $values).' WHERE '.$where;
		return $this->query($sql);
	}

	/**
	 * @return \imperator\database\AttacksTable
	 */
	public function getAttacksTable() {
		if(!isset($this->tables['attacks'])) {
			$this->tables['attacks'] = new AttacksTable($this);
		}
		return $this->tables['attacks'];
	}

	/**
	 * @return \imperator\database\ChatTable
	 */
	public function getChatTable() {
		if(!isset($this->tables['chat'])) {
			$this->tables['chat'] = new ChatTable($this);
		}
		return $this->tables['chat'];
	}

	/**
	 * @return \imperator\database\CombatLogTable
	 */
	public function getCombatLogTable() {
		if(!isset($this->tables['log'])) {
			$this->tables['log'] = new CombatLogTable($this);
		}
		return $this->tables['log'];
	}

	/**
	 * @return \imperator\database\GamesJoinedTable
	 */
	public function getGamesJoinedTable() {
		if(!isset($this->tables['gamesjoined'])) {
			$this->tables['gamesjoined'] = new GamesJoinedTable($this);
		}
		return $this->tables['gamesjoined'];
	}

	/**
	 * @return \imperator\database\GamesTable
	 */
	public function getGamesTable() {
		if(!isset($this->tables['games'])) {
			$this->tables['games'] = new GamesTable($this);
		}
		return $this->tables['games'];
	}

	/**
	 * @return \imperator\database\OutsideUsersTable
	 */
	public function getOutsideUsersTable() {
		if(!isset($this->tables['outside'])) {
			$this->tables['outside'] = new OutsideUsersTable($this);
		}
		return $this->tables['outside'];
	}

	/**
	 * @return \imperator\database\TerritoriesTable
	 */
	public function getTerritoriesTable() {
		if(!isset($this->tables['territories'])) {
			$this->tables['territories'] = new TerritoriesTable($this);
		}
		return $this->tables['territories'];
	}

	/**
	 * @return \imperator\database\UsersTable
	 */
	public function getUsersTable() {
		if(!isset($this->tables['users'])) {
			$this->tables['users'] = new UsersTable($this);
		}
		return $this->tables['users'];
	}

	/**
	 * Checks if the given query contains a row and closes it.
	 * 
	 * @param Query query
	 * @return bool True if a row exists
	 */
	public function exists(Query $query) {
		if($query->fetchResult()) {
			$query->free();
			return true;
		}
		$query->free();
		return false;
	}

	public function startTransaction() {
		$this->query('START TRANSACTION');
	}

	public function commitTransaction() {
		$this->query('COMMIT');
	}

	public function registerTable($namespace, $name, array $columns) {
		$this->replacements[$namespace] = $name;
		foreach($columns as $column => $value) {
			$this->replacements[$namespace.'.'.$column] = $name.'.'.$value;
			$this->replacements['-'.$namespace.'.'.$column] = $value;
		}
	}

	public function parseStatement($statement) {
		preg_match_all('(\@[-]?[A-Za-z._]+)', $statement, $matches, PREG_OFFSET_CAPTURE);
		if(!empty($matches[0])) {
			$offset = 0;
			foreach($matches[0] as $match) {
				$key = substr($match[0], 1);
				if(isset($this->replacements[$key])) {
					$len = strlen($match[0]);
					$statement = substr_replace($statement, $this->replacements[$key], $offset + $match[1], $len);
					$offset += strlen($this->replacements[$key]) - $len;
				}
			}
		}
		if(func_num_args() > 1) {
			$args = func_get_args();
			array_shift($args);
			$statement = str_replace('%s', '\'%s\'', $statement);
			$args = array_map(array($this, 'escape'), $args);
			$statement = vsprintf($statement, $args);
		}
		return $statement;
	}

	public function preparedStatement($statement) {
		return $this->query(call_user_func_array(array($this, 'parseStatement'), func_get_args()));
	}

	public function createTables() {
		$this->getGamesTable()->create();
		$this->getAttacksTable()->create();
		$this->getChatTable()->create();
		$this->getCombatLogTable()->create();
		$this->getGamesJoinedTable()->create();
		$this->getTerritoriesTable()->create();
		$this->getUsersTable()->create();
	}

	public function dropTables() {
		$this->getUsersTable()->drop();
		$this->getTerritoriesTable()->drop();
		$this->getGamesJoinedTable()->drop();
		$this->getCombatLogTable()->drop();
		$this->getChatTable()->drop();
		$this->getAttacksTable()->drop();
		$this->getGamesTable()->drop();
	}

	public function createIn($number, $type = '%s') {
		return 'IN('.implode(', ', array_fill(0, $number, $type)).')';
	}
}