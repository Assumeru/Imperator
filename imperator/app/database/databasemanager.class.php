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

	public function delete($table, $where = null) {
		$sql = 'DELETE FROM '.$table;
		if(!empty($where)) {
			$sql .= 'WHERE '.$where;
		}
		return $this->query($sql);
	}

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
	 * 
	 * @param string $name
	 * @return Table:
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
		return $this->tables[$name];
	}
}