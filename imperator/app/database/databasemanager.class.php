<?php
namespace imperator\database;
use imperator\Imperator;

abstract class DatabaseManager {
	private $tables = array();

	/**
	 * Performs a database query.
	 * 
	 * @param string $query The query
	 * @return Query A query object
	 */
	public function query($query) {
		return null;
	}

	public function escape($value) {
		return $value;
	}

	/**
	 * @param string $table
	 * @param string $where
	 * @return \imperator\database\Query
	 */
	public function delete($table, $where = null) {
		$sql = 'DELETE FROM '.$table;
		if(!empty($where)) {
			$sql .= ' WHERE '.$where;
		}
		return $this->query($sql);
	}

	/**
	 * @param string $table
	 * @param array $values
	 * @return \imperator\database\Query
	 */
	public function insert($table, array $values) {
		foreach($values as $key => $value) {
			$values[$key] = $this->escape($value);
		}
		$fields = '`'.implode('`, `', array_keys($values)).'`';
		$values = '\''.implode('\', \'', $values).'\'';
		$sql = 'INSERT INTO '.$table.' ('.$fields.') VALUES('.$values.')';
		return $this->query($sql);
	}

	/**
	 * @param string $table
	 * @param array $values
	 * @return \imperator\database\Query
	 */
	public function insertMultiple($table, array $values) {
		$fields = '`'.implode('`, `', array_keys($values[0])).'`';
		foreach($values as $key => $row) {
			foreach($row as $index => $column) {
				$row[$index] = $this->escape($column);
			}
			$values[$key] = '(\''.implode('\', \'', $row).'\')';
		}
		$sql = 'INSERT INTO '.$table.' ('.$fields.') VALUES '.implode(', ',$values);
		return $this->query($sql);
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
	 * Checks if a row exists in the given table matching the where clause.
	 * 
	 * @param string $table
	 * @param string $where
	 * @return bool True if a row exists
	 */
	public function rowExists($table, $where) {
		$query = $this->query('SELECT 1 FROM '.$table.' WHERE '.$where);
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
}