<?php

namespace ova777\DAO;

/**
 * Class Connection
 * @package ova777\DAO
 */
class Connection {

	/**
	 * Table prefix
	 * @var string
	 */
	public $tablePrefix = '';

	/**
	 * PDO Connection
	 * @var \PDO
	 */
	public $dbh;

	/**
	 * Default PDO Connection options
	 * @var array
	 */
	private static $options = array(
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
	);

	/**
	 * Connection constructor.
	 * @param string $dsn
	 * @param string $user
	 * @param string $pass
	 * @param array $opts
	 */
	public function __construct($dsn, $user, $pass, $opts = array()) {
		$options = self::$options;
		foreach($opts as $k=>$v) $options[$k] = $v;

		$this->dbh = new \PDO($dsn, $user, $pass, $options);
	}

	/**
	 * Begin transaction
	 */
	public function beginTransaction(){
		$this->dbh->beginTransaction();
	}

	/**
	 * Commit transaction
	 */
	public function commit(){
		$this->dbh->commit();
	}

	/**
	 * Cancel transaction
	 */
	public function rollback(){
		$this->dbh->rollBack();
	}

	/**
	 * Create new SQL command
	 * @param null|string $sql
	 * @return Command
	 */
	public function createCommand($sql = null) {
		return new Command($this, $sql);
	}

	/**
	 * Determines the PDO type for the specified PHP type.
	 * @param string $type The PHP type (obtained by gettype() call).
	 * @return integer the corresponding PDO type
	 */
	public function getPdoType($type) {
		static $map = array(
			'boolean'=>\PDO::PARAM_BOOL,
			'integer'=>\PDO::PARAM_INT,
			'string'=>\PDO::PARAM_STR,
			'resource'=>\PDO::PARAM_LOB,
			'NULL'=>\PDO::PARAM_NULL,
		);
		return isset($map[$type]) ? $map[$type] : \PDO::PARAM_STR;
	}
}