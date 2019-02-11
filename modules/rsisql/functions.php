<?php
/*

SQL Buddy - Web based MySQL administration
http://www.sqlbuddy.com/

functions.php
- gets the page setup with the variables it needs

MIT license

2008 Calvin Lough <http://calv.in>

*/

error_reporting(E_ALL);

if (function_exists('date_default_timezone_set'))
	date_default_timezone_set('Greenwich');

if (!session_id())
	session_start();

define("MAIN_DIR", dirname(__FILE__) . "/");
define("INCLUDES_DIR", MAIN_DIR . "includes/");

$sbconfig['DefaultAdapter'] = "mysql";
$sbconfig['DefaultHost'] = "localhost";
$sbconfig['DefaultUser'] = "root";
$sbconfig['EnableUpdateCheck'] = true;
$sbconfig['RowsPerPage'] = 100;
$sbconfig['EnableGzip'] = true;


$typeList[] = "varchar";
$typeList[] = "char";
$typeList[] = "text";
$typeList[] = "tinytext";
$typeList[] = "mediumtext";
$typeList[] = "longtext";
$typeList[] = "tinyint";
$typeList[] = "smallint";
$typeList[] = "mediumint";
$typeList[] = "int";
$typeList[] = "bigint";
$typeList[] = "real";
$typeList[] = "double";
$typeList[] = "float";
$typeList[] = "decimal";
$typeList[] = "numeric";
$typeList[] = "date";
$typeList[] = "time";
$typeList[] = "datetime";
$typeList[] = "timestamp";
$typeList[] = "tinyblob";
$typeList[] = "blob";
$typeList[] = "mediumblob";
$typeList[] = "longblob";
$typeList[] = "binary";
$typeList[] = "varbinary";
$typeList[] = "bit";
$typeList[] = "enum";
$typeList[] = "set";

$textDTs[] = "text";
$textDTs[] = "mediumtext";
$textDTs[] = "longtext";

$numericDTs[] = "tinyint";
$numericDTs[] = "smallint";
$numericDTs[] = "mediumint";
$numericDTs[] = "int";
$numericDTs[] = "bigint";
$numericDTs[] = "real";
$numericDTs[] = "double";
$numericDTs[] = "float";
$numericDTs[] = "decimal";
$numericDTs[] = "numeric";

$binaryDTs[] = "tinyblob";
$binaryDTs[] = "blob";
$binaryDTs[] = "mediumblob";
$binaryDTs[] = "longblob";
$binaryDTs[] = "binary";
$binaryDTs[] = "varbinary";

$sqliteTypeList[] = "varchar";
$sqliteTypeList[] = "integer";
$sqliteTypeList[] = "float";
$sqliteTypeList[] = "varchar";
$sqliteTypeList[] = "nvarchar";
$sqliteTypeList[] = "text";
$sqliteTypeList[] = "boolean";
$sqliteTypeList[] = "clob";
$sqliteTypeList[] = "blob";
$sqliteTypeList[] = "timestamp";
$sqliteTypeList[] = "numeric";


class GetTextReader {
	
	var $translationIndex = array();
	var $basePath = "";
	
	function GetTextReader($inputFile) {
		
		$msgId = "";
		$msgIdPlural = "";
		$msgStr = "";
		$msgStrPlural = "";
		
		$readFile = $this->basePath . $inputFile;
		
/*		if (file_exists($readFile)) {
			$handle = fopen($readFile, "r");
			if ($handle) {
				while (!feof($handle)) 
				{
				   $lines[] = trim(fgets($handle, 4096));
				}
				fclose($handle);
			}
*/
if (1) {
$lines = <<<EOD
Language: English
Language-Code: en_US
Build-Date: Wed, 18 Feb 2009 10:34:56 -0700

msgid: "Either the file could not be read or it was empty"
msgstr: ""

msgid: "Error performing operation"
msgstr: ""

msgid: "All"
msgstr: ""

msgid: "None"
msgstr: ""

msgid: "With selected"
msgstr: ""

msgid: "Empty"
msgstr: ""

msgid: "Drop"
msgstr: ""

msgid: "Optimize"
msgstr: ""

msgid: "Table"
msgstr: ""

msgid: "Rows"
msgstr: ""

msgid: "Size"
msgstr: ""

msgid: "Overhead"
msgstr: ""

msgid: "Options"
msgstr: ""

msgid: "Edit database"
msgstr: ""

msgid: "Charset"
msgstr: ""

msgid: "Submit"
msgstr: ""

msgid: "Create a new table"
msgstr: ""

msgid: "Name"
msgstr: ""

msgid: "Setup the fields for the table below"
msgstr: ""

msgid: "New field"
msgstr: ""

msgid: "Type"
msgstr: ""

msgid: "Values"
msgstr: ""

msgid: "Key"
msgstr: ""

msgid: "primary"
msgstr: ""

msgid: "unique"
msgstr: ""

msgid: "index"
msgstr: ""

msgid: "Default"
msgstr: ""

msgid: "Other"
msgstr: ""

msgid: "Unsigned"
msgstr: ""

msgid: "Binary"
msgstr: ""

msgid: "Not Null"
msgstr: ""

msgid: "Auto Increment"
msgstr: ""

msgid: "Add field"
msgstr: ""

msgid: "Oops"
msgstr: ""

msgid: "For some reason, the database parameter was not included with your request"
msgstr: ""

msgid: "Save changes to original"
msgstr: ""

msgid: "Insert as new row"
msgstr: ""

msgid: "Save"
msgstr: ""

msgid: "User"
msgstr: ""

msgid: "All privileges"
msgstr: ""

msgid: "Selected privileges"
msgstr: ""

msgid: "User privileges"
msgstr: ""

msgid: "Select"
msgstr: ""

msgid: "Insert"
msgstr: ""

msgid: "Update"
msgstr: ""

msgid: "Delete"
msgstr: ""

msgid: "Alter"
msgstr: ""

msgid: "Create"
msgstr: ""

msgid: "Temp tables"
msgstr: ""

msgid: "Administrator privileges"
msgstr: ""

msgid: "File"
msgstr: ""

msgid: "Process"
msgstr: ""

msgid: "Reload"
msgstr: ""

msgid: "Shutdown"
msgstr: ""

msgid: "Super"
msgstr: ""

msgid: "Grant option"
msgstr: ""

msgid: "User not found!"
msgstr: ""

msgid: "You must export either structure, data, or both"
msgstr: ""

msgid: "Please select the databases that you would like to export"
msgstr: ""

msgid: "Please select the tables that you would like to export"
msgstr: ""

msgid: "Table `%s` is empty"
msgstr: ""

msgid: "Results"
msgstr: ""

msgid: "Select all"
msgstr: ""

msgid: "The file could not be opened"
msgstr: ""

msgid: "Could not write to file"
msgstr: ""

msgid: "Successfully wrote content to file"
msgstr: ""

msgid: "Download"
msgstr: ""

msgid: "Note
"
msgstr: ""

msgid: "If this is a public server, you should delete this file from the server after you download it"
msgstr: ""

msgid: "Export"
msgstr: ""

msgid: "Tables"
msgstr: ""

msgid: "Databases"
msgstr: ""

msgid: "Format"
msgstr: ""

msgid: "SQL"
msgstr: ""

msgid: "CSV"
msgstr: ""

msgid: "Structure"
msgstr: ""

msgid: "Data"
msgstr: ""

msgid: "Compact inserts"
msgstr: ""

msgid: "Complete inserts"
msgstr: ""

msgid: "Delimiter"
msgstr: ""

msgid: "Comma"
msgstr: ""

msgid: "Tab"
msgstr: ""

msgid: "Semicolon"
msgstr: ""

msgid: "Space"
msgstr: ""

msgid: "Print field names on first line"
msgstr: ""

msgid: "If you are exporting a large number of rows, it is recommended that you output the results to a text file"
msgstr: ""

msgid: "Output to"
msgstr: ""

msgid: "Browser"
msgstr: ""

msgid: "Text file"
msgstr: ""

msgid: "Home"
msgstr: ""

msgid: "Users"
msgstr: ""

msgid: "Query"
msgstr: ""

msgid: "Import"
msgstr: ""

msgid: "Overview"
msgstr: ""

msgid: "Browse"
msgstr: ""

msgid: "Your changes were saved to the database"
msgstr: ""

msgid: "delete this row"
msgstr: ""

msgid: "delete these rows"
msgstr: ""

msgid: "empty this table"
msgstr: ""

msgid: "empty these tables"
msgstr: ""

msgid: "drop this table"
msgstr: ""

msgid: "drop these tables"
msgstr: ""

msgid: "delete this column"
msgstr: ""

msgid: "delete these columns"
msgstr: ""

msgid: "delete this index"
msgstr: ""

msgid: "delete this indexes"
msgstr: ""

msgid: "delete this user"
msgstr: ""

msgid: "delete this users"
msgstr: ""

msgid: "Are you sure you want to"
msgstr: ""

msgid: "The following query will be run:"
msgstr: ""

msgid: "The following queries will be run:"
msgstr: ""

msgid: "Confirm"
msgstr: ""

msgid: "Successfully saved changes"
msgstr: ""

msgid: "Full Text"
msgstr: ""

msgid: "Loading..."
msgstr: ""

msgid: "Error"
msgstr: ""

msgid: "There was an error receiving data from the server"
msgstr: ""

msgid: "Logout"
msgstr: ""

msgid: "For some reason, the table parameter was not included with your request"
msgstr: ""

msgid: "Welcome to SQL Buddy!"
msgstr: ""

msgid: "You are connected to MySQL %s with the user %s."
msgstr: ""

msgid: "The database server has been running since %s and has transferred approximately %s of data."
msgstr: ""

msgid: "A new version of SQL Buddy is available!"
msgstr: ""

msgid: "You have SQL Buddy %s installed and %s is now available."
msgstr: ""

msgid: "Getting started"
msgstr: ""

msgid: "Help"
msgstr: ""

msgid: "Translations"
msgstr: ""

msgid: "Release Notes"
msgstr: ""

msgid: "Contact"
msgstr: ""

msgid: "Create a new database"
msgstr: ""

msgid: "Did you know..."
msgstr: ""

msgid: "There is an easier way to select a large group of items when browsing table rows. Check the first row, hold the shift key, and check the final row. The checkboxes between the two rows will be automatically checked for you."
msgstr: ""

msgid: "The columns in the browse and query tabs are resizable. Adjust them to as wide or narrow as you like."
msgstr: ""

msgid: "The login page is based on a default user of root@localhost. By editing config.php, you can change the default user and host to whatever you want."
msgstr: ""

msgid: "Keyboard shortcuts"
msgstr: ""

msgid: "Press this key..."
msgstr: ""

msgid: "...and this will happen"
msgstr: ""

msgid: "select none"
msgstr: ""

msgid: "edit selected items"
msgstr: ""

msgid: "delete selected items"
msgstr: ""

msgid: "refresh page"
msgstr: ""

msgid: "load the query tab"
msgstr: ""

msgid: "browse tab - go to first page of results"
msgstr: ""

msgid: "browse tab - go to last page of results"
msgstr: ""

msgid: "browse tab - go to previous page of results"
msgstr: ""

msgid: "browse tab - go to next page of results"
msgstr: ""

msgid: "optimize selected tables"
msgstr: ""

msgid: "Choose a .sql file to import"
msgstr: ""

msgid: "File
"
msgstr: ""

msgid: "Ignore first line"
msgstr: ""

msgid: "Edit"
msgstr: ""

msgid: "Refresh"
msgstr: ""

msgid: "No primary key defined"
msgstr: ""

msgid: "First"
msgstr: ""

msgid: "Prev"
msgstr: ""

msgid: "Next"
msgstr: ""

msgid: "Last"
msgstr: ""

msgid: "Your query returned %d result"
msgid_plural: "Your query returned %d results"
msgstr[0]: ""
msgstr[1]: ""

msgid: "Note: To avoid crashing your browser, only the first %d results have been displayed"
msgstr: ""

msgid: "binary data"
msgstr: ""

msgid: "Your query affected %d rows"
msgstr: ""

msgid: "Your query did not return any results"
msgstr: ""

msgid: "Your data has been inserted into the database"
msgstr: ""

msgid: "There was a bit of trouble locating the \"%s\" table"
msgstr: ""

msgid: "There was a problem logging you in"
msgstr: ""

msgid: "Login"
msgstr: ""

msgid: "Unsupported browser"
msgstr: ""

msgid: "Help!"
msgstr: ""

msgid: "Your session has timed out. Please login again."
msgstr: ""

msgid: "Username"
msgstr: ""

msgid: "Password"
msgstr: ""

msgid: "Run a query on the %s database"
msgstr: ""

msgid: "Columns"
msgstr: ""

msgid: "Add a column"
msgstr: ""

msgid: "Insert this column"
msgstr: ""

msgid: "At end of table"
msgstr: ""

msgid: "At beginning of table"
msgstr: ""

msgid: "After"
msgstr: ""

msgid: "Edit table"
msgstr: ""

msgid: "Indexes"
msgstr: ""

msgid: "Add an index"
msgstr: ""

msgid: "Column(s)"
msgstr: ""

msgid: "Show %d more..."
msgstr: ""

msgid: "Empty table"
msgstr: ""

msgid: "Drop table"
msgstr: ""

msgid: "Optimize table"
msgstr: ""

msgid: "Table Information"
msgstr: ""

msgid: "Host"
msgstr: ""

msgid: "Add a new user"
msgstr: ""

msgid: "Enter in the format: ('1','2')"
msgstr: ""

msgid: "Are you sure you want to empty the '%s' table? This will delete all the data inside of it. The following query will be run:"
msgstr: ""

msgid: "Are you sure you want to drop the '%s' table? This will delete the table and all data inside of it. The following query will be run:"
msgstr: ""

msgid: "Are you sure you want to drop the database '%s'? This will delete the database, the tables inside the database, and all data inside of the tables. The following query will be run:"
msgstr: ""

msgid: "We're sorry, but currently only Internet Explorer 7 is supported. It is available as a free download on Microsoft's website. Other free browsers are also supported, including Firefox, Safari, and Opera."
msgstr: ""

msgid: "You don't appear to have cookies enabled. For sessions to work, most php installations require cookies."
msgstr: ""

msgid: "Importing..."
msgstr: ""

msgid: "Drop the '%s' database"
msgstr: ""

msgid: "Okay"
msgstr: ""

msgid: "Cancel"
msgstr: ""

msgid: "Storage engine"
msgstr: ""

msgid: "Language"
msgstr: ""

msgid: "%d statement was executed from the file"
msgid_plural: "%d statements were executed from the file"
msgstr[0]: ""
msgstr[1]: ""

msgid: "%d row was inserted into the database from the file"
msgid_plural: "%d rows were inserted into the database from the file"
msgstr[0]: ""
msgstr[1]: ""

msgid: "%d row had to be skipped because the number of values was incorrect"
msgid_plural: "%d rows had to be skipped because the number of values was incorrect"
msgstr[0]: ""
msgstr[1]: ""

msgid: "Theme"
msgstr: ""

msgid: "(%.4f seconds)"
msgstr: ""

msgid: "The following errors were reported"
msgstr: ""

msgid: "You do not have enough permissions to create new users."
msgstr: ""

msgid: "You do not have enough permissions to view or manage users."
msgstr: ""

msgid: "You are connected to %s."
msgstr: ""

msgid: "Allow access to"
msgstr: ""

msgid: "All databases"
msgstr: ""

msgid: "Selected databases"
msgstr: ""

msgid: "Give user"
msgstr: ""

msgid: "Updates"
msgstr: ""

msgid: "There are no updates available"
msgstr: ""

msgid: "Change password"
msgstr: ""


EOD;

$lines = explode("\n",$lines);
			foreach ($lines as $line) {
				if (substr($line, 0, 6) == "msgid:") {
					$msgId = substr($line, 8, -1);
					$msgStr = "";
				} else if (substr($line, 0, 13) == "msgid_plural:") {
					$msgIdPlural = substr($line, 15, -1);
				} else if (substr($line, 0, 7) == "msgstr:") {
					$msgStr = substr($line, 9, -1);
				} else if (substr($line, 0, 10) == "msgstr[0]:") {
					$msgStr = substr($line, 12, -1);
				} else if (substr($line, 0, 10) == "msgstr[1]:") {
					$msgStrPlural = substr($line, 12, -1);
				}
				
				if ($msgId && $msgStr) {
					$this->translationIndex[$msgId] = $msgStr;
					if ($msgIdPlural)
						$this->translationIndex[$msgIdPlural] = $msgStrPlural;
					
					$msgId = "";
					$msgIdPlural = "";
					$msgStr = "";
					$msgStrPlural = "";
				}
			}
		}
	}
	
	function getTranslation($lookup) {
		if (array_key_exists($lookup, $this->translationIndex)) {
			return $this->translationIndex[$lookup];
		} else {
			return $lookup;
		}
	}
	
}


if (version_compare(PHP_VERSION, "5.0.0", "<")) {
	class SQL {
	
	var $adapter = "";
	var $method = "";
	var $version = "";
	var $conn = "";
	var $options = "";
	var $errorMessage = "";
	var $db = "";
	
	function SQL($connString, $user = "", $pass = "") {
		list($this->adapter, $options) = explode(":", $connString, 2);
		
		if ($this->adapter != "sqlite") {
			$this->adapter = "mysql";
		}
		
		$optionsList = explode(";", $options);
		
		foreach ($optionsList as $option) {
			list($a, $b) = explode("=", $option);
			$opt[$a] = $b;
		}
		
		$this->options = $opt;
		$database = (array_key_exists("database", $opt)) ? $opt['database'] : "";
		
		if ($this->adapter == "sqlite") {
			$this->method = "sqlite";
			$this->conn = sqlite_open($database, 0666, $sqliteError);
		} else {
			$this->method = "mysql";
			$host = (array_key_exists("host", $opt)) ? $opt['host'] : "";
			$this->conn = @mysql_connect($host, $user, $pass);
		}
		
		if ($this->conn && $this->adapter == "mysql") {
			$this->query("SET NAMES 'utf8'");
		}
	}
	
	function isConnected() {
		return ($this->conn !== false);
	}
	
	function disconnect() {
		if ($this->conn) {
			if ($this->method == "mysql") {
				mysql_close($this->conn);
				$this->conn = null;
			} else if ($this->method == "sqlite") {
				sqlite_close($this->conn);
				$this->conn = null;
			}
		}
	}
	
	function getAdapter() {
		return $this->adapter;
	}
	
	function getMethod() {
		return $this->method;
	}
	
	function getOptionValue($optKey) {
		if (array_key_exists($optKey, $this->options)) {
			return $this->options[$optKey];
		} else {
			return false;
		}
	}
	
	function selectDB($db) {
		if ($this->conn) {
			
			$this->db = $db;
			
			if ($this->method == "mysql") {
				return (mysql_select_db($db));
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	function query($queryText) {
		if ($this->conn) {
			if ($this->method == "mysql") {
				$queryResult = @mysql_query($queryText, $this->conn);

				if (!$queryResult) {
					$this->errorMessage = mysql_error();
				}

				return $queryResult;
			} else if ($this->method == "sqlite") {
				$queryResult = sqlite_query($this->conn, $queryText);

				if (!$queryResult) {
					$this->errorMessage = sqlite_error_string(sqlite_last_error($this->conn));
				}

				return $queryResult;
			}
		} else {
			return false;
		}
	}

	function rowCount($resultSet) {
		if ($this->conn) {
			if ($this->method == "mysql") {
				return @mysql_num_rows($resultSet);
			} else if ($this->method == "sqlite") {
				return @sqlite_num_rows($resultSet);
			}
		}
	}
	
	function isResultSet($resultSet) {
		if ($this->conn) {
			return ($this->rowCount($resultSet) > 0);
		}
	}
	
	function fetchArray($resultSet) {
		if (!$resultSet)
			return false;
		
		if ($this->conn) {
			if ($this->method == "mysql") {
				return mysql_fetch_row($resultSet);
			} else if ($this->method == "sqlite") {
				return sqlite_fetch_array($resultSet, SQLITE_NUM);
			}
		}
	}

	function fetchAssoc($resultSet) {
		if (!$resultSet)
			return false;
		
		if ($this->conn) {
			if ($this->method == "mysql") {
				return mysql_fetch_assoc($resultSet);
			} else if ($this->method == "sqlite") {
				return sqlite_fetch_array($resultSet, SQLITE_ASSOC);
			}
		}
	}

	function affectedRows($resultSet) {
		if (!$resultSet)
			return false;
		
		if ($this->conn) {
			if ($this->method == "mysql") {
				return @mysql_affected_rows($resultSet);
			} else if ($this->method == "sqlite") {
				return sqlite_changes($resultSet);
			}
		}
	}
	
	function result($resultSet, $targetRow, $targetColumn = "") {
		if (!$resultSet)
			return false;
		
		if ($this->conn) {
			if ($this->method == "mysql") {
				return mysql_result($resultSet, $targetRow, $targetColumn);
			} else if ($this->method == "sqlite") {
				return sqlite_column($resultSet, $targetColumn);
			}
		}
	}
	
	function listDatabases() {
		if ($this->conn) {
			if ($this->adapter == "mysql") {
				return $this->query("SHOW DATABASES");
			} else if ($this->adapter == "sqlite") {
				$database = (array_key_exists("database", $this->options)) ? $this->options['database'] : "";
				return $database;
			}
		}
	}
	
	function listTables() {
		if ($this->conn) {
			if ($this->adapter == "mysql") {
				return $this->query("SHOW TABLES");
			} else if ($this->adapter == "sqlite") {
				return $this->query("SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name");
			}
		}
	}
	
	function hasCharsetSupport()
	{
		if ($this->conn) {
			if ($this->adapter == "mysql" && version_compare($this->getVersion(), "4.1", ">")) {
				return true;
			} else  {
				return false;
			}
		}
	}
	
	function listCharset() {
		if ($this->conn) {
			if ($this->adapter == "mysql") {	
				return $this->query("SHOW CHARACTER SET");
			} else if ($this->adapter == "sqlite") {
				return "";
			}
		}
	}
	
	function listCollation() {
		if ($this->conn) {
			if ($this->adapter == "mysql") {
				return $this->query("SHOW COLLATION");
			} else if ($this->adapter == "sqlite") {
				return "";
			}
		}
	}
	
	function insertId($resultSet = null) {
		if ($this->conn) {
			if ($this->method == "mysql") {
				return mysql_insert_id($resultSet);
			} else if ($this->method == "sqlite") {
				return sqlite_last_insert_rowid($resultSet);
			}
		}
	}

	function escapeString($toEscape) {
		if ($this->conn) {
			if ($this->adapter == "mysql") {
				return mysql_real_escape_string($toEscape);
			} else if ($this->adapter == "sqlite") {
				return sqlite_escape_string($toEscape);
			}
		}
	}
	
	function getVersion() {
		if ($this->conn) {
			// cache
			if ($this->version) {
				return $this->version;
			}
			
			if ($this->adapter == "mysql") {
				$verSql = mysql_get_server_info();
				$version = explode("-", $verSql);
				$this->version = $version[0];
				return $this->version;
			} else if ($this->adapter == "sqlite") {
				$this->version = sqlite_libversion();
				return $this->version;
			}
		}

	}
	
	// returns the number of rows in a table
	function tableRowCount($table) {
		if ($this->conn) {
			if ($this->adapter == "mysql") {
				$countSql = $this->query("SELECT COUNT(*) AS `RowCount` FROM `" . $table . "`");
				$count = (int)($this->result($countSql, 0, "RowCount"));
				return $count;
			} else if ($this->adapter == "sqlite") {
				$countSql = $this->query("SELECT COUNT(*) AS 'RowCount' FROM '" . $table . "'");
				$count = (int)($this->result($countSql, 0, "RowCount"));
				return $count;
			}
		}
	}
	
	// gets column info for a table
	function describeTable($table) {
		if ($this->conn) {
			if ($this->adapter == "mysql") {
				return $this->query("DESCRIBE `" . $table . "`");
			} else if ($this->adapter == "sqlite") {
				$columnSql = $this->query("SELECT sql FROM sqlite_master where tbl_name = '" . $table . "'");
				$columnInfo = $this->result($columnSql, 0, "sql");
				$columnStart = strpos($columnInfo, '(');
				$columns = substr($columnInfo, $columnStart+1, -1);
				$columns = split(',[^0-9]', $columns);
				
				$columnList = array();
				
				foreach ($columns as $column) {
					$column = trim($column);
					$columnSplit = explode(" ", $column, 2);
					$columnName = $columnSplit[0];
					$columnType = (sizeof($columnSplit) > 1) ? $columnSplit[1] : "";
					$columnList[] = array($columnName, $columnType);
				}
				
				return $columnList;
			}
		}
	}
	
	/*
		Return names, row counts etc for every database, table and view in a JSON string
	*/
	function getMetadata() {
		$output = '';
		if ($this->conn) {
			if ($this->adapter == "mysql" && version_compare($this->getVersion(), "5.0.0", ">=")) {
				$this->selectDB("information_schema");
				$schemaSql = $this->query("SELECT `SCHEMA_NAME` FROM `SCHEMATA` ORDER BY `SCHEMA_NAME`");
				if ($this->rowCount($schemaSql)) {
					while ($schema = $this->fetchAssoc($schemaSql)) {
						$output .= '{"name": "' . $schema['SCHEMA_NAME'] . '"';
						// other interesting columns: TABLE_TYPE, ENGINE, TABLE_COLUMN and many more
						$tableSql = $this->query("SELECT `TABLE_NAME`, `TABLE_ROWS` FROM `TABLES` WHERE `TABLE_SCHEMA`='" . $schema['SCHEMA_NAME'] . "' ORDER BY `TABLE_NAME`");
						if ($this->rowCount($tableSql)) {
							$output .= ',"items": [';
							while ($table = $this->fetchAssoc($tableSql)) {
								
								if ($schema['SCHEMA_NAME'] == "information_schema") {
									$countSql = $this->query("SELECT COUNT(*) AS `RowCount` FROM `" . $table['TABLE_NAME'] . "`");
									$rowCount = (int)($this->result($countSql, 0, "RowCount"));
								} else {
									$rowCount = (int)($table['TABLE_ROWS']);
								}
								
								$output .= '{"name":"' . $table['TABLE_NAME'] . '","rowcount":' . $rowCount . '},';
							}
							
							if (substr($output, -1) == ",")
								$output = substr($output, 0, -1);
							
							$output .= ']';
						}
						$output .= '},';
					}
					$output = substr($output, 0, -1);
				}
			} else if ($this->adapter == "mysql") {
				$schemaSql = $this->listDatabases();
				
				if ($this->rowCount($schemaSql)) {
					while ($schema = $this->fetchArray($schemaSql)) {
						$output .= '{"name": "' . $schema[0] . '"';
						
						$this->selectDB($schema[0]);
						$tableSql = $this->listTables();
						
						if ($this->rowCount($tableSql)) {
							$output .= ',"items": [';
							while ($table = $this->fetchArray($tableSql)) {
								$countSql = $this->query("SELECT COUNT(*) AS `RowCount` FROM `" . $table[0] . "`");
								$rowCount = (int)($this->result($countSql, 0, "RowCount"));
								$output .= '{"name":"' . $table[0] . '","rowcount":' . $rowCount . '},';
							}
							
							if (substr($output, -1) == ",")
								$output = substr($output, 0, -1);
							
							$output .= ']';
						}
						$output .= '},';
					}
					$output = substr($output, 0, -1);
				}
			} else if ($this->adapter == "sqlite") {
				$database = (array_key_exists("database", $this->options)) ? $this->options['database'] : "";
				
				$output .= '{"name": "' . $database . '"';
				
				$tableSql = $this->listTables();

				if ($this->rowCount($tableSql)) {
					$output .= ',"items": [';
					while ($tableRow = $this->fetchArray($tableSql)) {
						$countSql = $this->query("SELECT COUNT(*) AS 'RowCount' FROM '" . $tableRow[0] . "'");
						$rowCount = (int)($this->result($countSql, 0, "RowCount"));
						$output .= '{"name":"' . $tableRow[0] . '","rowcount":' . $rowCount . '},';
					}
					
					if (substr($output, -1) == ",")
						$output = substr($output, 0, -1);
					
					$output .= ']';
				}
				$output .= '}';
			}
		}
		return $output;
	}

	function error() {
		return $this->errorMessage;
	}

}
} else {


class SQL {
	
	var $adapter = "";
	var $method = "";
	var $version = "";
	var $conn = "";
	var $options = "";
	var $errorMessage = "";
	var $db = "";
	
	function SQL($connString, $user = "", $pass = "") {
		list($this->adapter, $options) = explode(":", $connString, 2);
		
		if ($this->adapter != "sqlite") {
			$this->adapter = "mysql";
		}
		
		$optionsList = explode(";", $options);
		
		foreach ($optionsList as $option) {
			list($a, $b) = explode("=", $option);
			$opt[$a] = $b;
		}
		
		$this->options = $opt;
		$database = (array_key_exists("database", $opt)) ? $opt['database'] : "";
		
		if ($this->adapter == "sqlite" && substr(sqlite_libversion(), 0, 1) == "3" && class_exists("PDO") && in_array("sqlite", PDO::getAvailableDrivers())) {
			$this->method = "pdo";
			
			try
			{
				$this->conn = new PDO("sqlite:" . $database, null, null, array(PDO::ATTR_PERSISTENT => true));
			}
			catch (PDOException $error) {
				$this->conn = false;
            	$this->errorMessage = $error->getMessage();
          	}
		} else if ($this->adapter == "sqlite" && substr(sqlite_libversion(), 0, 1) == "2" && class_exists("PDO") && in_array("sqlite2", PDO::getAvailableDrivers())) {
			$this->method = "pdo";
			
			try
			{
				$this->conn = new PDO("sqlite2:" . $database, null, null, array(PDO::ATTR_PERSISTENT => true));
			}
			catch (PDOException $error) {
				$this->conn = false;
            	$this->errorMessage = $error->getMessage();
          	}
		} else if ($this->adapter == "sqlite") {
			$this->method = "sqlite";
			$this->conn = sqlite_open($database, 0666, $sqliteError);
		} else {
			$this->method = "mysql";
			$host = (array_key_exists("host", $opt)) ? $opt['host'] : "";
			$this->conn = @mysql_connect($host, $user, $pass);
		}
		
		if ($this->conn && $this->method == "pdo") {
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		}
		
		if ($this->conn && $this->adapter == "mysql") {
			$this->query("SET NAMES 'utf8'");
		}
		
		if ($this->conn && $database) {
			$this->db = $database;
		}
	}
	
	function isConnected() {
		return ($this->conn !== false);
	}
	
	function disconnect() {
		if ($this->conn) {
			if ($this->method == "pdo") {
				$this->conn = null;
			} else if ($this->method == "mysql") {
				mysql_close($this->conn);
				$this->conn = null;
			} else if ($this->method == "sqlite") {
				sqlite_close($this->conn);
				$this->conn = null;
			}
		}
	}
	
	function getAdapter() {
		return $this->adapter;
	}
	
	function getMethod() {
		return $this->method;
	}
	
	function getOptionValue($optKey) {
		if (array_key_exists($optKey, $this->options)) {
			return $this->options[$optKey];
		} else {
			return false;
		}
	}
	
	function selectDB($db) {
		if ($this->conn) {
			if ($this->method == "mysql") {
				$this->db = $db;
				return (mysql_select_db($db));
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	function query($queryText) {
		if ($this->conn) {
			if ($this->method == "pdo") {
				$queryResult = $this->conn->prepare($queryText);
				
				if ($queryResult)
					$queryResult->execute();

				if (!$queryResult) {
					$errorInfo = $this->conn->errorInfo();
					$this->errorMessage = $errorInfo[2];
				}

				return $queryResult;
			} else if ($this->method == "mysql") {
				$queryResult = @mysql_query($queryText, $this->conn);

				if (!$queryResult) {
					$this->errorMessage = mysql_error();
				}

				return $queryResult;
			} else if ($this->method == "sqlite") {
				$queryResult = sqlite_query($this->conn, $queryText);

				if (!$queryResult) {
					$this->errorMessage = sqlite_error_string(sqlite_last_error($this->conn));
				}

				return $queryResult;
			}
		} else {
			return false;
		}
	}
	
	// Be careful using this function - when used with pdo, the pointer is moved
	// to the end of the result set and the query needs to be rerun. Unless you 
	// actually need a count of the rows, use the isResultSet() function instead
	function rowCount($resultSet) {
		if (!$resultSet)
			return false;
		
		if ($this->conn) {
			if ($this->method == "pdo") {
				return count($resultSet->fetchAll());
			} else if ($this->method == "mysql") {
				return @mysql_num_rows($resultSet);
			} else if ($this->method == "sqlite") {
				return @sqlite_num_rows($resultSet);
			}
		}
	}
	
	function isResultSet($resultSet) {
		if ($this->conn) {
			if ($this->method == "pdo") {
				return ($resultSet == true);
			} else {
				return ($this->rowCount($resultSet) > 0);
			}
		}
	}

	function fetchArray($resultSet) {
		if (!$resultSet)
			return false;
		
		if ($this->conn) {
			if ($this->method == "pdo") {
				return $resultSet->fetch(PDO::FETCH_NUM);
			} else if ($this->method == "mysql") {
				return mysql_fetch_row($resultSet);
			} else if ($this->method == "sqlite") {
				return sqlite_fetch_array($resultSet, SQLITE_NUM);
			}
		}
	}

	function fetchAssoc($resultSet) {
		if (!$resultSet)
			return false;
		
		if ($this->conn) {
			if ($this->method == "pdo") {
				return $resultSet->fetch(PDO::FETCH_ASSOC);
			} else if ($this->method == "mysql") {
				return mysql_fetch_assoc($resultSet);
			} else if ($this->method == "sqlite") {
				return sqlite_fetch_array($resultSet, SQLITE_ASSOC);
			}
		}
	}

	function affectedRows($resultSet) {
		if (!$resultSet)
			return false;
		
		if ($this->conn) {
			if ($this->method == "pdo") {
				return $resultSet->rowCount();
			} else if ($this->method == "mysql") {
				return @mysql_affected_rows($resultSet);
			} else if ($this->method == "sqlite") {
				return sqlite_changes($resultSet);
			}
		}
	}
	
	function result($resultSet, $targetRow, $targetColumn = "") {
		if (!$resultSet)
			return false;
		
		if ($this->conn) {
			if ($this->method == "pdo") {
				if ($targetColumn) {
					$resultRow = $resultSet->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $targetRow);
					return $resultRow[$targetColumn];
				} else {
					$resultRow = $resultSet->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_ABS, $targetRow);
					return $resultRow[0];
				}
			} else if ($this->method == "mysql") {
				return mysql_result($resultSet, $targetRow, $targetColumn);
			} else if ($this->method == "sqlite") {
				return sqlite_column($resultSet, $targetColumn);
			}
		}
	}
	
	function listDatabases() {
		if ($this->conn) {
			if ($this->adapter == "mysql") {
				return $this->query("SHOW DATABASES");
			} else if ($this->adapter == "sqlite") {
				return $this->db;
			}
		}
	}
	
	function listTables() {
		if ($this->conn) {
			if ($this->adapter == "mysql") {
				return $this->query("SHOW TABLES");
			} else if ($this->adapter == "sqlite") {
				return $this->query("SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name");
			}
		}
	}
	
	function hasCharsetSupport()
	{
		if ($this->conn) {
			if ($this->adapter == "mysql" && version_compare($this->getVersion(), "4.1", ">")) {
				return true;
			} else  {
				return false;
			}
		}
	}
	
	function listCharset() {
		if ($this->conn) {
			if ($this->adapter == "mysql") {	
				return $this->query("SHOW CHARACTER SET");
			} else if ($this->adapter == "sqlite") {
				return "";
			}
		}
	}
	
	function listCollation() {
		if ($this->conn) {
			if ($this->adapter == "mysql") {
				return $this->query("SHOW COLLATION");
			} else if ($this->adapter == "sqlite") {
				return "";
			}
		}
	}
	
	function insertId() {
		if ($this->conn) {
			if ($this->method == "pdo") {
				return $this->conn->lastInsertId();
			} else if ($this->method == "mysql") {
				return @mysql_insert_id($this->conn);
			} else if ($this->method == "sqlite") {
				return sqlite_last_insert_rowid($this-conn);
			}
		}
	}

	function escapeString($toEscape) {
		if ($this->conn) {
			if ($this->method == "pdo") {
				$toEscape = $this->conn->quote($toEscape);
				$toEscape = substr($toEscape, 1, -1);
				return $toEscape;
			} else if ($this->adapter == "mysql") {
				return mysql_real_escape_string($toEscape);
			} else if ($this->adapter == "sqlite") {
				return sqlite_escape_string($toEscape);
			}
		}
	}
	
	function getVersion() {
		if ($this->conn) {
			// cache
			if ($this->version) {
				return $this->version;
			}
			
			if ($this->adapter == "mysql") {
				$verSql = mysql_get_server_info();
				$version = explode("-", $verSql);
				$this->version = $version[0];
				return $this->version;
			} else if ($this->adapter == "sqlite") {
				$this->version = sqlite_libversion();
				return $this->version;
			}
		}

	}
	
	// returns the number of rows in a table
	function tableRowCount($table) {
		if ($this->conn) {
			if ($this->adapter == "mysql") {
				$countSql = $this->query("SELECT COUNT(*) AS `RowCount` FROM `" . $table . "`");
				$count = (int)($this->result($countSql, 0, "RowCount"));
				return $count;
			} else if ($this->adapter == "sqlite") {
				$countSql = $this->query("SELECT COUNT(*) AS 'RowCount' FROM '" . $table . "'");
				$count = (int)($this->result($countSql, 0, "RowCount"));
				return $count;
			}
		}
	}
	
	// gets column info for a table
	function describeTable($table) {
		if ($this->conn) {
			if ($this->adapter == "mysql") {
				return $this->query("DESCRIBE `" . $table . "`");
			} else if ($this->adapter == "sqlite") {
				$columnSql = $this->query("SELECT sql FROM sqlite_master where tbl_name = '" . $table . "'");
				$columnInfo = $this->result($columnSql, 0, "sql");
				$columnStart = strpos($columnInfo, '(');
				$columns = substr($columnInfo, $columnStart+1, -1);
				$columns = split(',[^0-9]', $columns);
				
				$columnList = array();
				
				foreach ($columns as $column) {
					$column = trim($column);
					$columnSplit = explode(" ", $column, 2);
					$columnName = $columnSplit[0];
					$columnType = (sizeof($columnSplit) > 1) ? $columnSplit[1] : "";
					$columnList[] = array($columnName, $columnType);
				}
				
				return $columnList;
			}
		}
	}
	
	/*
		Return names, row counts etc for every database, table and view in a JSON string
	*/
	function getMetadata() {
		$output = '';
		if ($this->conn) {
			if ($this->adapter == "mysql" && version_compare($this->getVersion(), "5.0.0", ">=")) {
				$this->selectDB("information_schema");
				$schemaSql = $this->query("SELECT `SCHEMA_NAME` FROM `SCHEMATA` ORDER BY `SCHEMA_NAME`");
				if ($this->rowCount($schemaSql)) {
					while ($schema = $this->fetchAssoc($schemaSql)) {
						$output .= '{"name": "' . $schema['SCHEMA_NAME'] . '"';
						// other interesting columns: TABLE_TYPE, ENGINE, TABLE_COLUMN and many more
						$tableSql = $this->query("SELECT `TABLE_NAME`, `TABLE_ROWS` FROM `TABLES` WHERE `TABLE_SCHEMA`='" . $schema['SCHEMA_NAME'] . "' ORDER BY `TABLE_NAME`");
						if ($this->rowCount($tableSql)) {
							$output .= ',"items": [';
							while ($table = $this->fetchAssoc($tableSql)) {
								
								if ($schema['SCHEMA_NAME'] == "information_schema") {
									$countSql = $this->query("SELECT COUNT(*) AS `RowCount` FROM `" . $table['TABLE_NAME'] . "`");
									$rowCount = (int)($this->result($countSql, 0, "RowCount"));
								} else {
									$rowCount = (int)($table['TABLE_ROWS']);
								}
								
								$output .= '{"name":"' . $table['TABLE_NAME'] . '","rowcount":' . $rowCount . '},';
							}
							
							if (substr($output, -1) == ",")
								$output = substr($output, 0, -1);
							
							$output .= ']';
						}
						$output .= '},';
					}
					$output = substr($output, 0, -1);
				}
			} else if ($this->adapter == "mysql") {
				$schemaSql = $this->listDatabases();
				
				if ($this->rowCount($schemaSql)) {
					while ($schema = $this->fetchArray($schemaSql)) {
						$output .= '{"name": "' . $schema[0] . '"';
						
						$this->selectDB($schema[0]);
						$tableSql = $this->listTables();
						
						if ($this->rowCount($tableSql)) {
							$output .= ',"items": [';
							while ($table = $this->fetchArray($tableSql)) {
								$countSql = $this->query("SELECT COUNT(*) AS `RowCount` FROM `" . $table[0] . "`");
								$rowCount = (int)($this->result($countSql, 0, "RowCount"));
								$output .= '{"name":"' . $table[0] . '","rowcount":' . $rowCount . '},';
							}
							
							if (substr($output, -1) == ",")
								$output = substr($output, 0, -1);
							
							$output .= ']';
						}
						$output .= '},';
					}
					$output = substr($output, 0, -1);
				}
			} else if ($this->adapter == "sqlite") {
				$output .= '{"name": "' . $this->db . '"';
				
				$tableSql = $this->listTables();
				
				if ($tableSql) {
					$output .= ',"items": [';
					while ($tableRow = $this->fetchArray($tableSql)) {
						$countSql = $this->query("SELECT COUNT(*) AS 'RowCount' FROM '" . $tableRow[0] . "'");
						$rowCount = (int)($this->result($countSql, 0, "RowCount"));
						$output .= '{"name":"' . $tableRow[0] . '","rowcount":' . $rowCount . '},';
					}
					
					if (substr($output, -1) == ",")
						$output = substr($output, 0, -1);
					
					$output .= ']';
				}
				$output .= '}';
			}
		}
		return $output;
	}

	function error() {
		return $this->errorMessage;
	}

}


}
define("VERSION_NUMBER", "1.3.1");
define("PREVIEW_CHAR_SIZE", 65);

$adapterList[] = "mysql";

if (function_exists("sqlite_open") || (class_exists("PDO") && in_array("sqlite", PDO::getAvailableDrivers()))) {
	$adapterList[] = "sqlite";
}

$cookieLength = time() + (60*24*60*60);

$langList['en_US'] = "English";
$lang = "en_US";


if ($lang != "en_US") {
	// extend the cookie length
	setcookie("sb_lang", $lang, $cookieLength);
} else if (isset($_COOKIE['sb_lang'])) {
	// cookie not needed for en_US
	setcookie("sb_lang", "", time() - 10000);
}

$themeList["bittersweet"] = "Bittersweet";
$theme = "bittersweet";

$gt = new GetTextReader($lang . ".pot");

if (isset($_SESSION['SB_LOGIN_STRING'])) {
	$user = (isset($_SESSION['SB_LOGIN_USER'])) ? $_SESSION['SB_LOGIN_USER'] : "";
	$pass = (isset($_SESSION['SB_LOGIN_PASS'])) ? $_SESSION['SB_LOGIN_PASS'] : "";
	$conn = new SQL($_SESSION['SB_LOGIN_STRING'], $user, $pass);
}

// unique identifer for this session, to validate ajax requests.
// document root is included because it is likely a difficult value
// for potential attackers to guess
$requestKey = substr(md5(session_id() . $_SERVER["DOCUMENT_ROOT"]), 0, 16);

if (isset($conn) && $conn->isConnected()) {
	if (isset($_GET['db']))
		$db = $conn->escapeString($_GET['db']);

	if (isset($_GET['table']))
		$table = $conn->escapeString($_GET['table']);
	
	if ($conn->hasCharsetSupport()) {
		
		$charsetSql = $conn->listCharset();
		if ($conn->isResultSet($charsetSql)) {
			while ($charsetRow = $conn->fetchAssoc($charsetSql)) {
				$charsetList[] = $charsetRow['Charset'];
			}
		}
	
		$collationSql = $conn->listCollation();
		if ($conn->isResultSet($collationSql)) {
			while ($collationRow = $conn->fetchAssoc($collationSql)) {
				$collationList[$collationRow['Collation']] = $collationRow['Charset'];
			}
		}
	}
}

// undo magic quotes, if necessary
if (get_magic_quotes_gpc()) {
	$_GET = stripslashesFromArray($_GET);
	$_POST = stripslashesFromArray($_POST);
	$_COOKIE = stripslashesFromArray($_COOKIE);
	$_REQUEST = stripslashesFromArray($_REQUEST);
}

function stripslashesFromArray($value) {
    $value = is_array($value) ?
                array_map('stripslashesFromArray', $value) :
                stripslashes($value);

    return $value;
}

function loginCheck($validateReq = true) {
	if (!isset($_SESSION['SB_LOGIN'])){
		if (isset($_GET['ajaxRequest']))
			redirect("login.php?timeout=1");
		else
			redirect("login.php");
		exit;
	}
	if ($validateReq) {
		if (!validateRequest()) {
			exit;
		}
	}

	startOutput();
}

function redirect($url) {
	if (isset($_GET['ajaxRequest']) || headers_sent()) {
		global $requestKey;
		?>
		<script type="text/javascript" authkey="<?php echo $_GET['requestKey']; ?>">

		document.location = "<?php echo $url; ?>" + window.location.hash;

		</script>
		<?php
	} else {
		header("Location: $url");
	}
	exit;
}

function validateRequest() {
	global $requestKey;
	if (isset($_GET['requestKey']) && $_GET['requestKey'] != $requestKey) {
		return false;
	}
	return true;
}

function startOutput() {
	global $sbconfig;
	
	if (!headers_sent()) {
		/*
		if (extension_loaded("zlib") && ((isset($sbconfig['EnableGzip']) && $sbconfig['EnableGzip'] == true) || !isset($sbconfig['EnableGzip'])) && !ini_get("zlib.output_compression") && ini_get("output_handler") != "ob_gzhandler") {
		ob_start("ob_gzhandler");
			header("Content-Encoding: gzip");
			ob_implicit_flush();
		} else {
			ob_start();
		}
		*/

		ob_start();

		header("Cache-Control: no-cache, must-revalidate");
		header("Content-Type: text/html; charset=UTF-8");
		
		register_shutdown_function("finishOutput");
	}
}

function finishOutput() {	
	global $conn;
	
	if (ob_get_length() > 0)
		ob_end_flush();
	
	if (isset($conn) && $conn->isConnected()) {
		$conn->disconnect();
		unset($conn);
	}
}

function outputPage() {

global $requestKey;
global $sbconfig;
global $conn;
global $lang;

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/REC-html40/strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" version="-//W3C//DTD XHTML 1.1//EN" xml:lang="en">
	<head>
		<title>SQL Buddy</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<link type="text/css" rel="stylesheet" href="core.css" media="all" />

		<script type="text/javascript" src="core.js"></script>
	</head>
	<body>
	<div id="container">
	<div id="header">
		<div id="headerlogo">
		<a href="#page=home" onclick="sideMainClick('home.php', 0); return false;"><img src="../rsisql/logo.png" width="32" /></a>
		</div>
		<div id="toptabs"><ul></ul></div>
		<div id="headerinfo">
		<span id="load" style="display: none"><?php echo __("Loading..."); ?></span>
		<?php

		// if set to auto login, providing a link to logout wouldnt be much good
		if (!((isset($sbconfig['DefaultPass']) && $conn->getAdapter() == "mysql") || (isset($sbconfig['DefaultDatabase']) && $conn->getAdapter() == "sqlite")))
			echo '<a href="logout.php">' . __("Logout") . '</a>';

		?>
		</div>
		<div class="clearer"></div>
	</div>

	<div id="bottom">

	<div id="leftside">
		<div id="sidemenu">
		<div class="dblist"><ul>
		<?php
		
		if ($conn->getAdapter() != "sqlite") {
		
		?>
			<li id="sidehome"><a href="#page=home" onclick="sideMainClick('home.php', 0); return false;"><div class="menuicon">&gt;</div><div class="menutext"><?php echo __("Home"); ?></div></a></li>
			<li id="sideusers"><a href="#page=users&topTab=1" onclick="sideMainClick('users.php', 1); return false;"><div class="menuicon">&gt;</div><div class="menutext"><?php echo __("Users"); ?></div></a></li>
			<li id="sidequery"><a href="#page=query&topTab=2" onclick="sideMainClick('query.php', 2); return false;"><div class="menuicon">&gt;</div><div class="menutext"><?php echo __("Query"); ?></div></a></li>
			<li id="sideimport"><a href="#page=import&topTab=3" onclick="sideMainClick('import.php', 3); return false;"><div class="menuicon">&gt;</div><div class="menutext"><?php echo __("Import"); ?></div></a></li>
			<li id="sideexport"><a href="#page=export&topTab=4" onclick="sideMainClick('export.php', 4); return false;"><div class="menuicon">&gt;</div><div class="menutext"><?php echo __("Export"); ?></div></a></li>
		<?php
		
		} else {
		
		?>
			<li id="sidehome"><a href="#page=home" onclick="sideMainClick('home.php', 0); return false;"><div class="menuicon">&gt;</div><div class="menutext"><?php echo __("Home"); ?></div></a></li>
			<li id="sidequery"><a href="#page=query&topTab=1" onclick="sideMainClick('query.php', 1); return false;"><div class="menuicon">&gt;</div><div class="menutext"><?php echo __("Query"); ?></div></a></li>
			<li id="sideimport"><a href="#page=import&topTab=2" onclick="sideMainClick('import.php', 2); return false;"><div class="menuicon">&gt;</div><div class="menutext"><?php echo __("Import"); ?></div></a></li>
			<li id="sideexport"><a href="#page=export&topTab=3" onclick="sideMainClick('export.php', 3); return false;"><div class="menuicon">&gt;</div><div class="menutext"><?php echo __("Export"); ?></div></a></li>
		<?php
		
		}
		
		?>
		</ul></div>
		
		<div class="dblistheader"><?php echo __("Databases"); ?></div>
		<div class="dblist" id="databaselist"><ul></ul></div>
		</div>
	</div>
	<div id="rightside">

		<div id="content">
			<div class="corners"><div class="tl"></div><div class="tr"></div></div>
			<div id="innercontent"></div>
			<div class="corners"><div class="bl"></div><div class="br"></div></div>
		</div>

		</div>

	</div>
	</div>

	</body>
	<script type="text/javascript">
	<!--
	
	<?php
	
	if ($conn->getAdapter() == "sqlite") {
		echo "var showUsersMenu = false;\n";
	} else {
		echo "var showUsersMenu = true;\n";
	}
	
	echo "var adapter = \"" . $conn->getAdapter() . "\";\n";
	
	if (isset($requestKey)) {
		echo 'var requestKey = "' . $requestKey . '";';
		echo "\n";
	}
	
	// javascript translation strings
	echo "\t\tvar getTextArr = {";
	
	if ($lang != "en_US") {
		
		echo '"Home":"' . __("Home") . '", ';
		echo '"Users":"' . __("Users") . '", ';
		echo '"Query":"' . __("Query") . '", ';
		echo '"Import":"' . __("Import") . '", ';
		echo '"Export":"' . __("Export") . '", ';
	
		echo '"Overview":"' . __("Overview") . '", ';
	
		echo '"Browse":"' . __("Browse") . '", ';
		echo '"Structure":"' . __("Structure") . '", ';
		echo '"Insert":"' . __("Insert") . '", ';
	
		echo '"Your changes were saved to the database.":"' . __("Your changes were saved to the database.") . '", ';
	
		echo '"delete this row":"' . __("delete this row") . '", ';
		echo '"delete these rows":"' . __("delete these rows") . '", ';
		echo '"empty this table":"' . __("empty this table") . '", ';
		echo '"empty these tables":"' . __("empty these tables") . '", ';
		echo '"drop this table":"' . __("drop this table") . '", ';
		echo '"drop these tables":"' . __("drop these tables") . '", ';
		echo '"delete this column":"' . __("delete this column") . '", ';
		echo '"delete these columns":"' . __("delete these columns") . '", ';
		echo '"delete this index":"' . __("delete this index") . '", ';
		echo '"delete these indexes":"' . __("delete this indexes") . '", ';
		echo '"delete this user":"' . __("delete this user") . '", ';
		echo '"delete these users":"' . __("delete this users") . '", ';
		echo '"Are you sure you want to":"' . __("Are you sure you want to") . '", ';
	
		echo '"The following query will be run:":"' . __("The following query will be run:") . '", ';
		echo '"The following queries will be run:":"' . __("The following queries will be run:") . '", ';
	
		echo '"Confirm":"' . __("Confirm") . '", ';
		echo '"Are you sure you want to empty the \'%s\' table? This will delete all the data inside of it. The following query will be run:":"' . __("Are you sure you want to empty the '%s' table? This will delete all the data inside of it. The following query will be run:") . '", ';
		echo '"Are you sure you want to drop the \'%s\' table? This will delete the table and all data inside of it. The following query will be run:":"' . __("Are you sure you want to drop the '%s' table? This will delete the table and all data inside of it. The following query will be run:") . '", ';
		echo '"Are you sure you want to drop the database \'%s\'? This will delete the database, the tables inside the database, and all data inside of the tables. The following query will be run:":"' . __("Are you sure you want to drop the database '%s'? This will delete the database, the tables inside the database, and all data inside of the tables. The following query will be run:") . '", ';
	
		echo '"Successfully saved changes.":"' . __("Successfully saved changes.") . '", ';
	
		echo '"New field":"' . __("New field") . '", ';
	
		echo '"Full Text":"' . __("Full Text") . '", ';
	
		echo '"Loading...":"' . __("Loading...") . '", ';
		echo '"Redirecting...":"' . __("Redirecting...") . '", ';
	
		echo '"Okay":"' . __("Okay") . '", ';
		echo '"Cancel":"' . __("Cancel") . '", ';
	
		echo '"Error":"' . __("Error") . '", ';
		echo '"There was an error receiving data from the server.":"' . __("There was an error receiving data from the server.") . '"';
		
	}
	
	echo '};';

	echo "\n";


	echo 'var menujson = {"menu": [';
	echo $conn->getMetadata();
	echo ']};';
	
	?>
	//-->
	</script>
</html>
<?php
}

function requireDatabaseAndTableBeDefined() {
	global $db, $table;

	if (!isset($db)) {
		?>

		<div class="errorpage">
		<h4><?php echo __("Oops"); ?></h4>
		<p><?php echo __("For some reason, the database parameter was not included with your request."); ?></p>
		</div>

		<?php
		exit;
	}

	if (!isset($table)) {
		?>

		<div class="errorpage">
		<h4><?php echo __("Oops"); ?></h4>
		<p><?php echo __("For some reason, the table parameter was not included with your request."); ?></p>
		</div>

		<?php
		exit;
	}

}

function formatForOutput($text) {
	$text = nl2br(htmlentities($text, ENT_QUOTES, 'UTF-8'));
	if (utf8_strlen($text) > PREVIEW_CHAR_SIZE) {
		$text = utf8_substr($text, 0, PREVIEW_CHAR_SIZE) . " <span class=\"toBeContinued\">[...]</span>";
	}
	return $text;
}

function formatDataForCSV($text) {
	$text = str_replace('"', '""', $text);
	return $text;
}

function splitQueryText($query) {
	// the regex needs a trailing semicolon
	$query = trim($query);

	if (substr($query, -1) != ";")
		$query .= ";";

	// i spent 3 days figuring out this line
	preg_match_all("/(?>[^;']|(''|(?>'([^']|\\')*[^\\\]')))+;/ixU", $query, $matches, PREG_SET_ORDER);

	$querySplit = "";

	foreach ($matches as $match) {
		// get rid of the trailing semicolon
		$querySplit[] = substr($match[0], 0, -1);
	}

	return $querySplit;
}

function memoryFormat($bytes) {
	if ($bytes < 1024)
		$dataString = $bytes . " B";
	else if ($bytes < (1024 * 1024))
		$dataString = round($bytes / 1024) . " KB";
	else if ($bytes < (1024 * 1024 * 1024))
		$dataString = round($bytes / (1024 * 1024)) . " MB";
	else
		$dataString = round($bytes / (1024 * 1024 * 1024)) . " GB";

	return $dataString;
}

function themeFile($filename) {
	global $theme;
	return smartCaching("themes/" . $theme . "/" . $filename);
}

function smartCaching($filename) {
	return "serve.php?file=" . $filename . "&ver=" . str_replace(".", "_", VERSION_NUMBER);
}

function __($t) {
	global $gt;
	return $gt->getTranslation($t);
}

function __p($singular, $plural, $count) {
	global $gt;
	if ($count == 1) {
		return $gt->getTranslation($singular);
	} else {
		return $gt->getTranslation($plural);
	}
}

function utf8_substr($str, $from, $len) {
# utf8 substr
# www.yeap.lv
  return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.
                       '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s',
                       '$1',$str);
}

function utf8_strlen($str) {
    $i = 0;
    $count = 0;
    $len = strlen ($str);
    while ($i < $len) {
    $chr = ord ($str[$i]);
    $count++;
    $i++;
    if ($i >= $len)
        break;

    if ($chr & 0x80) {
        $chr <<= 1;
        while ($chr & 0x80) {
        $i++;
        $chr <<= 1;
        }
    }
    }
    return $count;
}

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

?>