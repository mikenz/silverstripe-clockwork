<?php
/**
 * Wraps the real database adapter, passing on most function calls
 * and logging queries for sending along to Clockwork
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 11.07.2014
 * @package clockwork
 */

namespace Clockwork\Support\Silverstripe;

use SS_Query;
use SS_Database;

class DatabaseProxy extends SS_Database
{
	/** @var SS_Database */
	protected $realConn;

	/** @var array */
	protected $queries;


	/**
	 * @param SS_Database $realConn
	 */
	public function __construct($realConn) {
		$this->realConn = $realConn;
		$this->queries = array();
	}

	/**
	 * @return array
	 */
	public function getQueries() {
		return $this->queries;
	}

	/**
	 * Returns the current schema manager
	 *
	 * @return DBSchemaManager
	 */
	public function getSchemaManager() {
		return $this->realConn->getSchemaManager();
	}

	/**
	 * Returns the current query builder
	 *
	 * @return DBQueryBuilder
	 */
	public function getQueryBuilder() {
		return $this->realConn->getQueryBuilder();
	}

	/**
	 * Execute the given SQL query.
	 *
	 * @param string $sql The SQL query to execute
	 * @param int $errorLevel The level of error reporting to enable for the query
	 * @return SS_Query
	 */
	public function query($sql, $errorLevel = E_USER_ERROR) {
		$starttime = microtime(true);
		$handle = $this->realConn->query($sql, $errorLevel);
		$endtime = microtime(true);
		$this->queries[] = array('query' => $sql, 'duration' => round(($endtime - $starttime) * 1000.0, 2));
		return $handle;
	}


	/**
	 * Execute the given SQL parameterised query with the specified arguments
	 *
	 * @param string $sql The SQL query to execute. The ? character will denote parameters.
	 * @param array $parameters An ordered list of arguments.
	 * @param int $errorLevel The level of error reporting to enable for the query
	 * @return SS_Query
	 */
	public function preparedQuery($sql, $parameters, $errorLevel = E_USER_ERROR) {
		$starttime = microtime(true);
		$handle = $this->realConn->preparedQuery($sql, $parameters, $errorLevel);
		$endtime = microtime(true);
		$this->queries[] = array('query' => $sql, 'duration' => round(($endtime - $starttime) * 1000.0, 2));
		return $handle;
	}

	/**
	 * Get the autogenerated ID from the previous INSERT query.
	 *
	 * @param string $table The name of the table to get the generated ID for
	 * @return integer the most recently generated ID for the specified table
	 */
	public function getGeneratedID($table) {
		return $this->realConn->getGeneratedID($table);
	}

	/**
	 * Determines if we are connected to a server AND have a valid database
	 * selected.
	 *
	 * @return boolean Flag indicating that a valid database is connected
	 */
	public function isActive() {
		return $this->realConn->isActive();
	}

	/**
	 * Returns an escaped string. This string won't be quoted, so would be suitable
	 * for appending to other quoted strings.
	 *
	 * @param mixed $value Value to be prepared for database query
	 * @return string Prepared string
	 */
	public function escapeString($value) {
		return $this->realConn->escapeString($value);
	}

	/**
	 * Wrap a string into DB-specific quotes.
	 *
	 * @param mixed $value Value to be prepared for database query
	 * @return string Prepared string
	 */
	public function quoteString($value) {
		return $this->realConn->quoteString($value);
	}

	/**
	 * Escapes an identifier (table / database name). Typically the value
	 * is simply double quoted. Don't pass in already escaped identifiers in,
	 * as this will double escape the value!
	 *
	 * @param string $value The identifier to escape
	 * @param string $separator optional identifier splitter
	 */
	public function escapeIdentifier($value, $separator = '.') {
		return $this->realConn->escapeIdentifier($value, $separator);
	}

	/**
	 * Generate a WHERE clause for text matching.
	 *
	 * @param String $field Quoted field name
	 * @param String $value Escaped search. Can include percentage wildcards.
	 * Ignored if $parameterised is true.
	 * @param boolean $exact Exact matches or wildcard support.
	 * @param boolean $negate Negate the clause.
	 * @param boolean $caseSensitive Enforce case sensitivity if TRUE or FALSE.
	 * Fallback to default collation if set to NULL.
	 * @param boolean $parameterised Insert the ? placeholder rather than the
	 * given value. If this is true then $value is ignored.
	 * @return String SQL
	 */
	public function comparisonClause($field, $value, $exact = false, $negate = false, $caseSensitive = null,
											$parameterised = false) {
		return $this->realConn->comparisonClause($field, $value, $exact, $negate, $caseSensitive, $parameterised);
	}

	/**
	 * function to return an SQL datetime expression that can be used with the adapter in use
	 * used for querying a datetime in a certain format
	 *
	 * @param string $date to be formated, can be either 'now', literal datetime like '1973-10-14 10:30:00' or
	 *                     field name, e.g. '"SiteTree"."Created"'
	 * @param string $format to be used, supported specifiers:
	 * %Y = Year (four digits)
	 * %m = Month (01..12)
	 * %d = Day (01..31)
	 * %H = Hour (00..23)
	 * %i = Minutes (00..59)
	 * %s = Seconds (00..59)
	 * %U = unix timestamp, can only be used on it's own
	 * @return string SQL datetime expression to query for a formatted datetime
	 */
	public function formattedDatetimeClause($date, $format) {
		return $this->realConn->formattedDatetimeClause($date, $format);
	}

	/**
	 * function to return an SQL datetime expression that can be used with the adapter in use
	 * used for querying a datetime addition
	 *
	 * @param string $date, can be either 'now', literal datetime like '1973-10-14 10:30:00' or field name,
	 *                      e.g. '"SiteTree"."Created"'
	 * @param string $interval to be added, use the format [sign][integer] [qualifier], e.g. -1 Day, +15 minutes,
	 *                         +1 YEAR
	 * supported qualifiers:
	 * - years
	 * - months
	 * - days
	 * - hours
	 * - minutes
	 * - seconds
	 * This includes the singular forms as well
	 * @return string SQL datetime expression to query for a datetime (YYYY-MM-DD hh:mm:ss) which is the result of
	 *                the addition
	 */
	public function datetimeIntervalClause($date, $interval) {
		return $this->realConn->datetimeIntervalClause($date, $interval);
	}

	/**
	 * function to return an SQL datetime expression that can be used with the adapter in use
	 * used for querying a datetime substraction
	 *
	 * @param string $date1, can be either 'now', literal datetime like '1973-10-14 10:30:00' or field name
	 *                       e.g. '"SiteTree"."Created"'
	 * @param string $date2 to be substracted of $date1, can be either 'now', literal datetime
	 *                      like '1973-10-14 10:30:00' or field name, e.g. '"SiteTree"."Created"'
	 * @return string SQL datetime expression to query for the interval between $date1 and $date2 in seconds which
	 *                is the result of the substraction
	 */
	public function datetimeDifferenceClause($date1, $date2) {
		return $this->realConn->datetimeDifferenceClause($date1, $date2);
	}

	/**
	 * Returns true if this database supports collations
	 *
	 * @return boolean
	 */
	public function supportsCollations() {
		return $this->realConn->supportsCollations();
	}

	/**
	 * Can the database override timezone as a connection setting,
	 * or does it use the system timezone exclusively?
	 *
	 * @return Boolean
	 */
	public function supportsTimezoneOverride() {
		return $this->realConn->supportsTimezoneOverride();
	}

	/**
	 * Query for the version of the currently connected database
	 * @return string Version of this database
	 */
	public function getVersion() {
		return $this->realConn->getVersion();
	}

	/**
	 * Get the database server type (e.g. mysql, postgresql).
	 * This value is passed to the connector as the 'driver' argument when
	 * initiating a database connection
	 *
	 * @return string
	 */
	public function getDatabaseServer() {
		return $this->realConn->getDatabaseServer();
	}

	/**
	 * The core search engine, used by this class and its subclasses to do fun stuff.
	 * Searches both SiteTree and File.
	 *
	 * @param array $classesToSearch List of classes to search
	 * @param string $keywords Keywords as a string.
	 * @param integer $start Item to start returning results from
	 * @param integer $pageLength Number of items per page
	 * @param string $sortBy Sort order expression
	 * @param string $extraFilter Additional filter
	 * @param boolean $booleanSearch Flag for boolean search mode
	 * @param string $alternativeFileFilter
	 * @param boolean $invertedMatch
	 * @return PaginatedList Search results
	 */
	public function searchEngine($classesToSearch, $keywords, $start, $pageLength, $sortBy = "Relevance DESC",
		$extraFilter = "", $booleanSearch = false, $alternativeFileFilter = "", $invertedMatch = false) {
		return $this->realConn->searchEngine($classesToSearch, $keywords, $start, $pageLength, $sortBy,
		$extraFilter, $booleanSearch, $alternativeFileFilter, $invertedMatch);
	}

	/**
	 * Determines if this database supports transactions
	 *
	 * @return boolean Flag indicating support for transactions
	 */
	public function supportsTransactions() {
		return $this->realConn->supportsTransactions();
	}

	/**
	 * Start a prepared transaction
	 * See http://developer.postgresql.org/pgdocs/postgres/sql-set-transaction.html for details on
	 * transaction isolation options
	 *
	 * @param string|boolean $transactionMode Transaction mode, or false to ignore
	 * @param string|boolean $sessionCharacteristics Session characteristics, or false to ignore
	 */
	public function transactionStart($transaction_mode = false, $session_characteristics = false) {
		return $this->realConn->transactionStart($transaction_mode, $session_characteristics);
	}

	/**
	 * Create a savepoint that you can jump back to if you encounter problems
	 *
	 * @param string $savepoint Name of savepoint
	 */
	public function transactionSavepoint($savepoint) {
		return $this->realConn->transactionSavepoint($savepoint);
	}

	/**
	 * Rollback or revert to a savepoint if your queries encounter problems
	 * If you encounter a problem at any point during a transaction, you may
	 * need to rollback that particular query, or return to a savepoint
	 *
	 * @param string|boolean $savepoint Name of savepoint, or leave empty to rollback
	 * to last savepoint
	 */
	public function transactionRollback($savepoint = false) {
		return $this->realConn->transactionRollback($savepoint);
	}

	/**
	 * Commit everything inside this transaction so far
	 *
	 * @param boolean $chain
	 */
	public function transactionEnd($chain = false) {
		return $this->realConn->transactionEnd($chain);
	}

	/**
	 * Returns the name of the currently selected database
	 *
	 * @return string|null Name of the selected database, or null if none selected
	 */
	public function getSelectedDatabase() {
		return $this->realConn->getSelectedDatabase();
	}

	/**
	 * Return SQL expression used to represent the current date/time
	 *
	 * @return string Expression for the current date/time
	 */
	public function now() {
		return $this->realConn->now();
	}

	/**
	 * Returns the database-specific version of the random() function
	 *
	 * @return string Expression for a random value
	 */
	public function random() {
		return $this->realConn->random();
	}

	/**
	 * @deprecated since version 4.0 Use selectDatabase('dbname', true) instead
	 */
	public function createDatabase() {
		return $this->realConn->createDatabase();
	}

	/**
	 * @deprecated since version 4.0 SS_Database::getConnect was never implemented and is obsolete
	 */
	public function getConnect($parameters) {
		return $this->realConn->getConnect($parameters);
	}

	/**
	 * @deprecated since version 4.0 Use DB::create_table instead
	 */
	 public function createTable($table, $fields = null, $indexes = null, $options = null, $advancedOptions = null) {
		return $this->realConn->createTable($table, $fields, $indexes, $options, $advancedOptions);
	}

	/**
	 * @deprecated since version 4.0 Use DB::get_schema()->alterTable() instead
	 */
	public function alterTable($table, $newFields = null, $newIndexes = null,
		$alteredFields = null, $alteredIndexes = null, $alteredOptions = null,
		$advancedOptions = null
	) {
		return $this->realConn->alterTable($table, $newFields, $newIndexes,
			$alteredFields, $alteredIndexes, $alteredOptions, $advancedOptions);
	}

	/**
	 * @deprecated since version 4.0 Use DB::get_schema()->renameTable() instead
	 */
	public function renameTable($oldTableName, $newTableName) {
		return $this->realConn->renameTable($oldTableName, $newTableName);
	}

	/**
	 * @deprecated since version 4.0 Use DB::create_field() instead
	 */
	public function createField($table, $field, $spec) {
		return $this->realConn->createField($table, $field, $spec);
	}

	/**
	 * @deprecated since version 4.0 Use DB::get_schema()->renameField() instead
	 */
	public function renameField($tableName, $oldName, $newName) {
		return $this->realConn->renameField($tableName, $oldName, $newName);
	}

	/**
	 * @deprecated since version 4.0 Use DB::field_list instead
	 */
	public function fieldList($table) {
		return $this->realConn->fieldList($table);
	}

	/**
	 * @deprecated since version 4.0 Use DB::table_list instead
	 */
	public function tableList() {
		return $this->realConn->tableList();
	}

	/**
	 * @deprecated since version 4.0 Use DB::get_schema()->hasTable() instead
	 */
	public function hasTable($tableName) {
		return $this->realConn->hasTable($tableName);
	}

	/**
	 * @deprecated since version 4.0 Use DB::get_schema()->enumValuesForField() instead
	 */
	public function enumValuesForField($tableName, $fieldName) {
		return $this->realConn->enumValuesForField($tableName, $fieldName);
	}

	/**
	 * @deprecated since version 4.0 Use Convert::raw2sql instead
	 */
	public function addslashes($value) {
		return $this->realConn->addslashes($val);
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		return call_user_func_array(array($this->realConn, $name), $arguments);
	}

}
