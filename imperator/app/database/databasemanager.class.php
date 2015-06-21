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
	 * @param string $name
	 * @return Table
	 */
	public function getTable($name) {
		if(!isset($this->tables[$name])) {
			$class = '\\imperator\\database\\'.$name.'Table';
			if(class_exists($class)) {
				$this->tables[$name] = new $class($this);
			} else {
				Imperator::getLogger()->log(\imperator\Logger::LEVEL_WARNING, 'Class "'.$class.'" not found.');
				$this->tables[$name] = null;
			}
		}
		if($this->tables[$name] == null) {
			throw new \imperator\exceptions\DatabaseException('Table \''.$name.'\' does not exist.');
		}
		return $this->tables[$name];
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
}