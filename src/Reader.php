<?php

namespace ova777\DAO;

/**
 * Class Reader
 * @package ova777\DAO
 */
class Reader implements \Iterator{
	/**
	 * @var \PDOStatement
	 */
	private $query;

	/**
	 * Current read row
	 * @var bool|array
	 */
	private $_row;

	/**
	 * Current row index
	 * @var int
	 */
	private $_index = -1;

	/**
	 * Reader constructor.
	 * @param \PDOStatement $query
	 */
	public function __construct($query) {
		$this->query = $query;
	}

	/**
	 * Resets the iterator to the initial state
	 * @return bool
	 */
	public function rewind() {
		if($this->_index > -1) return false;
		$this->_row = $this->read();
		$this->_index = 0;
	}

	/**
	 * Returns the current row
	 * @return array|bool
	 */
	public function current() {
		return $this->_row;
	}

	/**
	 * Returns the index of the current row
	 * @return int
	 */
	public function key() {
		return $this->_index;
	}

	/**
	 * Moves the internal pointer to the next row
	 */
	public function next() {
		$this->_row = $this->read();
		$this->_index++;
	}

	/**
	 * Returns whether there is a row of data at current position
	 * @return bool
	 */
	public function valid() {
		return $this->_row !== false;
	}

	/**
	 * Advances the reader to the next row in a result set
	 * @return array|false
	 */
	public function read() {
		return $this->query->fetch();
	}

	/**
	 * Reads the whole result set into an array
	 * @return array
	 */
	public function readAll() {
		return $this->query->fetchAll();
	}
}