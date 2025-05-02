<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * sql.pdo.php
 * Here happens the DB magic.
 * Usage:
 * $DB = Database::getInstance(); to instantiate the database object and
 * $DB->connect($servername, $database, $user, $password, $persistent=true/false, $dbType);
 * to connect to a database.
 * Then call e.g. $result = $DB->Select("SELECT * FROM `table` WHERE `id` > '2'"); to execute a normal SQL statement, or
 * $result = $DB->PreparedSelect("SELECT * FROM `table` WHERE `id` = :id", array("id" => $id)); to execute a prepared statement.
 * See method descriptions for further explanations of the parameters.
 */

/**
 * Original descriptions:
 * Class Singleton is a generic implementation of the singleton design pattern.
 *
 * Extending this class allows to make a single instance easily accessible by
 * many other objects.
 *
 * @author Quentin Berlemont <quentinberlemont@gmail.com>
 * @source http://www.php.net/manual/en/language.oop5.patterns.php#95196
 */
class Database {
	static protected $dbh;
	static protected $lastError;
	static protected $debug;
	
	/**
	 * Prevents direct creation of object.
	 * @param  void
	 * @return void
	 */
	protected function __construct() {}
	
	
	/**
	 * Prevents to clone the instance.
	 * @param  void
	 * @return void
	 */
	final private function __clone() {}
	
	
	/**
	 * Gets a single instance of the class the static method is called in.
	 * See the {@link http://php.net/lsb Late Static Bindings} feature for more
	 * information.
	 * @param  void
	 * @return object Returns a single instance of the class.
	 */
	final static public function getInstance() {
		static $instance = null;
		return $instance ? $instance : new self;
	}
	
	
	/**
	 * Connect to a database
	 * @param  string $dbHost  Host of the database (e.g. "localhost")
	 * @param  string $dbName  Name of the database
	 * @param  string $dbUser  User for the database
	 * @param  string $dbPass  Password for the user
	 * @param  bool   $persistent  (optional) Use a persistant connection?
	 * @param  string $dbType      (optional) Type of the database (e.g. "mysql")
	 * @param  string $encoding    (optional) Which encoding does the database use
	 * @return void
	 */
	public static function connect($dbHost, $dbName, $dbUser, $dbPass, $debug=false, $persistent=true, $dbType="mysql", $encoding="utf8") {
		self::$debug = $debug;
		
		try {
			self::$dbh = new PDO($dbType.':host='.$dbHost.';dbname='.$dbName, $dbUser, $dbPass, array(PDO::ATTR_PERSISTENT => $persistent));
			self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$dbh->query("SET CHARACTER SET '".$encoding."'");
			self::$dbh->query("SET NAMES '".$encoding."'");
		}
		catch (PDOException $e) {
			die("Cannot connect to database.");
		}
	}
	
	
	/**
	 * Closes the connection
	 * @param  void
	 * @return void
	 */
	public static function close() {
		self::$dbh = null;
	}
	
	
	/**
	 * Perform a SELECT statement
	 * @param  string $sql The SQL string
	 * @return array  The resulting rows in the database
	 */
	public static function Select($sql) {
		try {
			if(!self::$dbh) self::connect();
			$result = self::$dbh->query($sql);
			$rows   = $result->fetchAll(PDO::FETCH_ASSOC); 
		}
		catch (PDOException $e) {
			self::_fatalError($e->getMessage(), $sql);
		}
		
		return $rows;
	}
	
	
	/**	
	 * Perform a prepared SELECT statement
	 * @param  $sql      string  Der SQL-String
	 * @param  $params   array   The parameters array
	 * @param  $numeric  bool    Are parameters associative or numeric? (array("id" => $id) for `id` = :id or array($id) for `id` = ?)
	 * @param  $debug    bool    Display debug output?
	 * @return $result   mixed   The result
	 */
	public static function PreparedSelect($sql, $params=array(), $numeric=false, $debug=false) {
		try {
			if(!self::$dbh) self::connect();
			$dbSel = self::$dbh->prepare($sql);

			// Associative, named parameters
			if ($numeric === false) {
				foreach ($params AS $id => $val) {
					$dbSel->bindValue(':'.$id, $val);
				}
			}
			
			// Numeric, parameters are given by "?"
			else {
				$count = count($params);
				for ($x=1; $x<=$count; $x++) {
					$dbSel->bindValue($x, $params[($x-1)]);
				}
			}
			
			$result = $dbSel->execute();
			$rows   = $dbSel->fetchAll(PDO::FETCH_ASSOC);
			
			if ($debug == true) {
				self::_showDebug($sql, $params, $rows, $result);
			}
		}
		catch (PDOException $e) {
			self::_fatalError($e->getMessage(), $sql, $params);
		}
		
		return $rows;
	}
	


	/**
	 * Prepare a statemant
	 * @param  $sql     string  The SQL string to prepare
	 * @return $sqlPrep object  The prepared PDO statement
	 */
	public static function PrepareStatement($sql) {
		try {
			if(!self::$dbh) self::connect();
			$sqlPrep = self::$dbh->prepare($sql);
		}
		catch (PDOException $e) {
			if ($debug == true) {
				self::_fatalError($e->getMessage(), $sql, $params);
			}
			self::$lastError = $e->getMessage();
			return false;
		}

		return $sqlPrep;
	}

	

	/**	
	 * Perform a prepared statement (INSERT, UPDATE or DELETE - no SELECT)
	 * @param  $sql      mixed   The SQL string *OR* the already prepared statement
	 * @param  $params   array   The parameters array
	 * @param  $numeric  bool    Are parameters associative or numeric? (array("id" => $id) for `id` = :id or array($id) for `id` = ?)
	 * @param  $debug    bool    Display debug output?
	 * @param  $onlyShow bool    Only show the debug output, no real DB insert?
	 * @return $result   mixed   Number of inserted/updated/deleted rows or FALSE
	 */
	public static function PreparedStatement($sql, $params=array(), $numeric=false, $debug=false, $onlyShow=false) {
		try {
			$result = null;

			if(!self::$dbh) self::connect();

			// Execute a string or an already prepared statement?
			if (is_string($sql) === true) {
				$dbSel = self::$dbh->prepare($sql);
			}
			else {
				$dbSel = $sql;
			}

			
			
			// Associative, named parameters
			if ($numeric === false) {
				foreach ($params AS $id => $val) {
					$dbSel->bindValue(':'.$id, $val);
				}
			}
			
			// Numeric, parameters are given by "?"
			else {
				$paraNum = count($params);
				for ($x=1; $x<=$paraNum; $x++) {
					$dbSel->bindValue($x, $params[($x-1)]);
				}
			}
			
			if ($onlyShow === false) {
				$result = $dbSel->execute();
				$count  = $dbSel->rowCount();
			}
			
			if ($debug == true) {
				self::_showDebug($sql, $params, $count, $result);
			}
		}
		catch (PDOException $e) {
			if ($debug == true) {
				self::_fatalError($e->getMessage(), $sql, $params);
			}
			self::$lastError = $e->getMessage();
			return false;
		}
		
		if ($result !== true) {
			return $result;
		}
		return $count;
	}
	
	

	/**
	 * Output the last inserted ID
	 * Equivalent to mysql_insert_id()
	 * @param  void
	 * @return int  The last inserted ID in the database
	 */
	public static function lastInsertId() {
		return self::$dbh->lastInsertId();
	}
	
	
	/**
	 * Returns the amount of total rows found for the last query disregarding any LIMIT statements
	 * @param  void
	 * @return int  The total amount of rows found, without any LIMIT
	 */
	public static function foundRows() {
		$rows = self::$dbh->prepare('SELECT found_rows() AS `rows`', array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => TRUE));
		$rows->execute();
		$rowsCount = $rows->fetch(PDO::FETCH_OBJ)->rows;
		$rows->closeCursor();
		return $rowsCount;
	}
	
	
	/**
	 * Output an error message
	 * @param  string $msg The error message
	 * @param  string $sql The SQL string
	 * @param  array  $params Array with the parameters for the SQL string
	 * @param  $rows
	 * @param  $result
	 * @return void
	 */
	protected static function _fatalError($msg, $sql, $params, $rows=null, $result=null) {
		echo "<pre>Error!: ".$msg."\n";
		$bt = debug_backtrace();
		foreach($bt as $line) {
			$args = var_export($line['args'], true);
			echo "{$line['function']}($args) at {$line['file']}:{$line['line']}\n";
		}
		echo "</pre>";
		
		self::_showDebug($sql, $params, $rows, $result);
		#die();
	}
	
	
	/**
	 * Return the last error message
	 * return string The error message
	 */
	public static function getError() {
		//return self::$dbh->errorInfo();
		return self::$lastError;
	}
	
	
	/**
	 * Output the SQL query for debug purposes
	 * @param  string  $sql  The SQL query
	 * @param  array   $params  The parameter array
	 * @param  $rows
	 * @param  $result
	 * @return void
	 */
	public static function _showDebug($sql, $params, $rows=null, $result=null) {
		echo "<pre>";
		echo "SQL:\n";
		var_dump($sql);
		echo "\n";
		echo "Params:\n";
		var_dump($params);
		echo "\n";
		echo "Result:\n";
		var_dump($result);
		echo "\n";
		echo "Rows:\n";
		var_dump($rows);
		echo "\n";
		#echo "PDO DebugDump:\n";
		#$dbSel->debugDumpParams();
		echo "InterpolatedQuery:\n";
		var_dump(self::_interpolateQuery($sql, $params));
		echo "</pre>";				
	}
	
	
	/**
	 * Replaces any parameter placeholders in a query with the value of that
	 * parameter. Useful for debugging. Assumes anonymous parameters from $params
	 * are are in the same order as specified in $query
	 * @param  string  $query   The sql query with parameter placeholders
	 * @param  array   $params  The array of substitution parameters
	 * @return string  The interpolated query
	 * @source http://stackoverflow.com/questions/210564/pdo-prepared-statements
	 */
	public static function _interpolateQuery($query, $params=array()) {
		$keys = array();
		
		// Build a regular expression for each parameter
		foreach ($params as $key => $value) {
			if (is_string($key)) {
				$keys[] = '/:'.$key.'/';
			} else {
				$keys[] = '/[?]/';
			}
		}
		
		// Insert the parameters into the query string
		$query = preg_replace($keys, $params, $query, 1, $count);
		
		return $query;
	}

}
?>