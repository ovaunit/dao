<?php

namespace ova777\DAO;

/**
 * Class Command
 * @package ova777\DAO
 */
class Command {
	/**
	 * @var Connection
	 */
	public $connection;

	/**
	 * Current SQL string
	 * @var null|string
	 */
	private $_sql = null;

	/**
	 * Parameters to bindValues before execute statement
	 * @var array
	 */
	private $_params = array();

	/**
	 * Current PDO statement
	 * @var null|\PDOStatement
	 */
	private $_prepare = null;

	/**
	 * Creates and executes a DELETE SQL statement
	 * @param string $table
	 * @param string $condition the conditions that will be put in the WHERE part
	 * @param array $params the parameters to be bound to the query
	 * @return bool
	 */
	public function delete($table, $condition = '', $params = array()) {
		$this->_sql = 'DELETE';
		return $this->from($table)->where($condition, $params)->execute();
	}

	/**
	 * Creates and executes an UPDATE SQL statement
	 * @param string $table
	 * @param array $data data (name=>value) to be updated
	 * @param string $condition the conditions that will be put in the WHERE part
	 * @param array $params the parameters to be bound to the query
	 * @return bool
	 */
	public function update($table, $data, $condition = '', $params = array()) {
		$this->_sql = 'UPDATE '.$this->tablePrefix($table);
		return $this->set($data)->where($condition, $params)->execute();
	}

	/**
	 * Creates and executes an INSERT SQL statement
	 * @param string $table
	 * @param array $data data (name=>value) to be inserted into the table
	 * @return bool
	 */
	public function insert($table, $data) {
		$this->_sql = 'INSERT INTO '.$this->tablePrefix($table);
		return $this->set($data)->execute();
	}

	/**
	 * Sets the SET part of the query
	 * @param array $data data (name=>value) to be added in SET part
	 * @return $this
	 */
	public function set($data) {
		$set = array();
		foreach($data as $k=>$v) {
			$_param = ':daoset'.$k;
			$set[] = '`'.trim($k, '`').'`='.$_param;
			$this->_params[$_param] = $v;
		}
		$this->_sql .= ' SET '.implode(',', $set);
		return $this;
	}

	/**
	 * Sets the SELECT part of the query
	 * @param string $columns
	 * @return $this
	 */
	public function select($columns = '*') {
		$this->_sql .= ' SELECT '.$columns;
		return $this;
	}

	/**
	 * Sets the FROM part of the query
	 * @param string $table
	 * @return $this
	 */
	public function from($table) {
		$this->_sql .= ' FROM '.$this->tablePrefix($table);
		return $this;
	}

	/**
	 * Sets the WHERE part of the query
	 * @param string $condition
	 * @param array $params
	 * @return $this
	 */
	public function where($condition = '', $params = array()) {
		if('' === $condition) return $this;
		$this->_sql .= ' WHERE '.$condition;
		$this->addParams($params);
		return $this;
	}

	/**
	 * Sets the ORDER BY part of the query
	 * @param string $columns
	 * @return $this
	 */
	public function order($columns) {
		$this->_sql .= ' ORDER BY '.$columns;
		return $this;
	}

	/**
	 * Sets the GROUP BY part of the query
	 * @param string $columns
	 * @return $this
	 */
	public function group($columns) {
		$this->_sql .= ' GROUP BY '.$columns;
		return $this;
	}

	/**
	 * Sets the LIMIT part of the query
	 * @param int $limit
	 * @param null|int $offset
	 * @return $this
	 */
	public function limit($limit, $offset = null) {
		$this->_sql .= ' LIMIT '.(int)$limit;
		if(null !== $offset) $this->offset($offset);
		return $this;
	}

	/**
	 * Sets the OFFSET part of the query
	 * @param int $offset
	 * @return $this
	 */
	public function offset($offset) {
		$this->_sql .= ' OFFSET '.(int)$offset;
		return $this;
	}

	/**
	 * Appends a JOIN part to the query
	 * @param string $table
	 * @param string $condition
	 * @param array $params
	 * @return $this
	 */
	public function join($table, $condition = '', $params = array()) {
		return $this->joinInternal('JOIN', $table, $condition, $params);
	}

	/**
	 * Appends a LEFT JOIN part to the query
	 * @param string $table
	 * @param string $condition
	 * @param array $params
	 * @return $this
	 */
	public function leftJoin($table, $condition = '', $params = array()) {
		return $this->joinInternal('LEFT JOIN', $table, $condition, $params);
	}

	/**
	 * Appends a RIGHT JOIN part to the query
	 * @param string $table
	 * @param string $condition
	 * @param array $params
	 * @return $this
	 */
	public function rightJoin($table, $condition = '', $params = array()) {
		return $this->joinInternal('RIGHT JOIN', $table, $condition, $params);
	}

	/**
	 * Appends a CROSS JOIN part to the query
	 * @param string $table
	 * @param string $condition
	 * @param array $params
	 * @return $this
	 */
	public function crossJoin($table, $condition = '', $params = array()) {
		return $this->joinInternal('CROSS JOIN', $table, $condition, $params);
	}

	/**
	 * Appends a NATURAL JOIN part to the query
	 * @param string $table
	 * @param string $condition
	 * @param array $params
	 * @return $this
	 */
	public function naturalJoin($table, $condition = '', $params = array()) {
		return $this->joinInternal('NATURAL JOIN', $table, $condition, $params);
	}

	/**
	 * Appends a NATURAL LEFT JOIN part to the query
	 * @param string $table
	 * @param string $condition
	 * @param array $params
	 * @return $this
	 */
	public function naturalLeftJoin($table, $condition = '', $params = array()) {
		return $this->joinInternal('NATURAL LEFT JOIN', $table, $condition, $params);
	}

	/**
	 * Appends a NATURAL RIGHT JOIN part to the query
	 * @param string $table
	 * @param string $condition
	 * @param array $params
	 * @return $this
	 */
	public function naturalRightJoin($table, $condition = '', $params = array()) {
		return $this->joinInternal('NATURAL RIGHT JOIN', $table, $condition, $params);
	}

	/**
	 * Sets the HAVING part of the query
	 * @param string $condition
	 * @param array $params
	 * @return $this
	 */
	public function having($condition, $params = array()) {
		$this->_sql .= ' HAVING '.$condition;
		$this->addParams($params);
		return $this;
	}

	/**
	 * Appends a SQL statement using UNION operator
	 * @param string $sql
	 * @return $this
	 */
	public function union($sql = '') {
		$this->_sql .= ' UNION '.$this->tablePrefix($sql).' ';
		return $this;
	}

	/**
	 * Binds a parameter to the SQL statement to be executed
	 * @param string $param
	 * @param mixed $value
	 * @param int|null $type If null, the type is determined by the PHP type of the value
	 * @return $this
	 */
	public function bindParam($param, &$value, $type = null) {
		if(null === $type) $type = $this->connection->getPdoType($value);
		$this->prepare()->bindParam($param, $value, $type);
		return $this;
	}

	/**
	 * Binds a value to a parameter
	 * @param string $param
	 * @param mixed $value
	 * @param int|null $type If null, the type is determined by the PHP type of the value
	 * @return $this
	 */
	public function bindValue($param, $value, $type = null) {
		if(null === $type) $type = $this->connection->getPdoType($value);
		$this->prepare()->bindValue($param, $value, $type);
		return $this;
	}

	/**
	 * Binds a list of values to the corresponding parameters
	 * @param array $values
	 * @return $this
	 */
	public function bindValues($values = array()) {
		foreach($values as $param=>$value) {
			$this->bindValue($param, $value);
		}
		return $this;
	}

	/**
	 * Executes the SQL statement
	 * @param array $params
	 * @return bool
	 */
	public function execute($params = array()) {
		$this->addParams($params);
		$this->bindValues($this->_params);
		return $this->prepare()->execute();
	}

	/**
	 * Executes the SQL statement and returns query result
	 * @param array $params
	 * @return Reader the reader object for fetching the query result
	 */
	public function query($params = array()) {
		$this->execute($params);
		return new Reader($this->prepare());
	}

	/**
	 * Executes the SQL statement and returns all rows
	 * @param array $params
	 * @return array
	 */
	public function queryAll($params = array()) {
		$this->execute($params);
		return $this->prepare()->fetchAll();
	}

	/**
	 * Executes the SQL statement and returns the first row of the result
	 * @param array $params
	 * @return mixed
	 */
	public function queryRow($params = array()) {
		$this->execute($params);
		return $this->prepare()->fetch();
	}

	/**
	 * Executes the SQL statement and returns the first column of the result
	 * @param array $params
	 * @return array
	 */
	public function queryColumn($params = array()) {
		$this->execute($params);
		$column = array();
		while(false !== $item = $this->prepare()->fetchColumn()) $column[] = $item;
		return $column;
	}

	/**
	 * Executes the SQL statement and returns the value of the first column in the first row of data
	 * @param array $params
	 * @return mixed
	 */
	public function queryScalar($params = array()) {
		$this->execute($params);
		return $this->prepare()->fetchColumn();
	}

	/**
	 * Returns the current SQL string ($this->sql, $this->text)
	 * @param string $name
	 * @return null|string
	 */
	public function __get($name) {
		switch ($name) {
			case 'text':
			case 'sql': return trim($this->_sql);
		}
		return null;
	}

	/**
	 * Command constructor.
	 * @param Connection $connection
	 * @param null|string $sql
	 */
	public function __construct($connection, $sql = null) {
		$this->connection = $connection;
		$this->_sql = $this->tablePrefix($sql);
	}

	/**
	 * Create PDO Statement (if not exists)
	 * @return \PDOStatement
	 */
	private function prepare() {
		if(null === $this->_prepare) {
			$this->_prepare = $this->connection->dbh->prepare($this->sql);
		}
		return $this->_prepare;
	}

	/**
	 * Appends an JOIN part to the query
	 * @param string $type
	 * @param string $table
	 * @param string $condition
	 * @param array $params
	 * @return $this
	 */
	private function joinInternal($type, $table, $condition = '', $params = array()) {
		$this->_sql .= ' '.$type.' '.$this->tablePrefix($table);
		if('' !== $condition) $this->_sql .= ' ON '.$condition;
		$this->addParams($params);
		return $this;
	}

	/**
	 * Add parameters for bindValues before execute
	 * @param array $params
	 * @return $this
	 */
	private function addParams($params = array()) {
		$this->_params = array_merge($this->_params, $params);
		return $this;
	}

	/**
	 * Replace "{{table}}" to "tablePrefix_table"
	 * @param string $sql
	 * @return string
	 */
	private function tablePrefix($sql) {
		$prefix = $this->connection->tablePrefix;
		return strtr($sql, array('{{' => $prefix, '}}' => ''));
	}
}