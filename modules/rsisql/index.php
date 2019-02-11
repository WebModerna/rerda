<?php
/*

SQL Buddy - Web based MySQL administration
http://www.sqlbuddy.com/

index.php
- output page structure, content loaded through ajax

MIT license

2008 Calvin Lough <http://calv.in>

*/

error_reporting(E_ALL);
ini_set('display_errors','On');

$file = '';
if (!empty($_SERVER['REDIRECT_URL'])) {
	$file =  end(explode('/',$_SERVER['REDIRECT_URL']));
}

if (!empty($file)) {

if ($file == 'ajaxcreatetable.php') {

	include "functions.php";


	loginCheck();

	if (isset($db))
		$conn->selectDB($db);

	if (isset($_POST['query'])) {
		
		$queryList = splitQueryText($_POST['query']);
		
		foreach ($queryList as $query) {
			$sql = $conn->query($query) or ($dbError = $conn->error());
		}
		
		if (isset($dbError)) {
			echo $dbError;
		}
		
	}

} else if ($file == 'ajaxfulltext.php') {

	include "functions.php";

	loginCheck();

	if (isset($db))
		$conn->selectDB($db);

	if (isset($_POST['query'])) {
		
		$queryList = splitQueryText($_POST['query']);
		
		foreach ($queryList as $query) {
			$sql = $conn->query($query);
		}
	}

	if ($conn->getAdapter() == "mysql") {
		$structureSql = $conn->describeTable($table);
		
		while ($structureRow = $conn->fetchAssoc($structureSql)) {
			$types[$structureRow['Field']] = $structureRow['Type'];
		}
	}

	if ($conn->isResultSet($sql)) {
		
		$row = $conn->fetchAssoc($sql);
		
		foreach ($row as $key => $value) {
			echo "<div class=\"fulltexttitle\">" . $key . "</div>";
			echo "<div class=\"fulltextbody\">";
			
			$curtype = $types[$key];
			
			if (strpos(" ", $curtype) > 0) {
				$curtype = substr($curtype, 0, strpos(" ", $curtype));
			}
			
			if ($value && isset($binaryDTs) && in_array($curtype, $binaryDTs)) {
				echo '<span class="binary">(' . __("binary data") . ')</span>';
			} else {
				echo nl2br(htmlentities($value, ENT_QUOTES, 'UTF-8'));
			}
			
			echo "</div>";
		}
	}

} else if ($file == 'ajaximportfile.php') {

	include "functions.php";

	loginCheck();

	if (isset($db))
		$conn->selectDB($db);

	function stripCommentLines($in) {
		if (substr($in, 0, 2) == "--")
			$in = '';
		
		return $in;
	}

	if (isset($_POST) || isset($_FILES)) {
		
		if (isset($_FILES['INPUTFILE']['tmp_name']))
			$file = $_FILES['INPUTFILE']['tmp_name'];
		
		if (isset($_POST['FORMAT']))
			$format = $_POST['FORMAT'];
		
		if (!(isset($format) && $format == "CSV"))
			$format = "SQL";
		
		if (isset($_POST['IGNOREFIRST']))
			$ignoreFirst = $_POST['IGNOREFIRST'];
		
		$first = true;
		
		// for csv
		if (isset($format) && $format == "CSV" && isset($table)) {
			$columnCount = 0;
			
			$structureSQL = $conn->describeTable($table);
			
			if ($conn->isResultSet($structureSQL)) {
				while ($structureRow = $conn->fetchAssoc($structureSQL)) {
					$columnCount++;
				}
			}
		}
		
		$insertCount = 0;
		$skipCount = 0;
		
		if (isset($file) && is_uploaded_file($file)) {
			if (isset($format) && $format == "SQL") {
				$lines = file($file);
				
				// the file() function doesn't handle mac line endings correctly
				if (sizeof($lines) == 1 && strpos($lines[0], "\r") > 0) {
					$lines = explode("\r", $lines[0]);
				}
				
				$commentFree = array_map("stripCommentLines", $lines);
				
				$contents = trim(implode('', $commentFree));
				
				$statements = splitQueryText($contents);
			} else {
				$statements = file($file);
				
				// see previous comment
				if (sizeof($statements) == 1 && strpos($statements[0], "\r") > 0) {
					$statements = explode("\r", $statements[0]);
				}
			}
			
			foreach ($statements as $statement) {
				$statement = trim($statement);
				
				if ($statement) {
					if (isset($format) && $format == "SQL") {
						$importQuery = $conn->query($statement) or ($dbErrors[] = $conn->error());
						
						$affected = (int)($conn->affectedRows($importQuery));
						$insertCount += $affected;
					} else if (isset($format) && $format == "CSV" && isset($table)) {
						if (!(isset($ignoreFirst) && $first)) {
							preg_match_all('/"(([^"]|"")*)"/i', $statement, $matches);
							
							$rawValues = $matches[1];
							
							for ($i=0; $i<sizeof($rawValues); $i++) {
								$rawValues[$i] = str_replace('""', '"', $rawValues[$i]);
								$rawValues[$i] = $conn->escapeString($rawValues[$i]);
							}
							
							$values = implode("','", $rawValues);
							
							// make sure that the counts match up
							if (sizeof($rawValues) == $columnCount) {
								
								if ($conn->getAdapter() == "sqlite")
									$importQuery = $conn->query("INSERT INTO '$table' VALUES ('$values')") or ($dbErrors[] = $conn->error());
								else
									$importQuery = $conn->query("INSERT INTO `$table` VALUES ('$values')") or ($dbErrors[] = $conn->error());
								
								$affected = (int)($conn->affectedRows($importQuery));
								
								$insertCount += $affected;
							} else {
								$skipCount++;
							}
						}
						$first = false;
					}
				}
			}
		}
		
		$message = "";
		
		if (!isset($statements)) {
			$message .= __("Either the file could not be read or it was empty") . "<br />";
		} else if ($format == "SQL") {	
			$message .= sprintf(__p("%d statement was executed from the file", "%d statements were executed from the file", $insertCount), $insertCount) . ".<br />";
		} else if ($format == "CSV") {
			if (isset($insertCount) && $insertCount > 0) {
				$message .= sprintf(__p("%d row was inserted into the database from the file", "%d rows were inserted into the database from the file", $insertCount), $insertCount) . ".<br />";
			}
			if (isset($skipCount) && $skipCount > 0) {
				$message .= sprintf(__p("%d row had to be skipped because the number of values was incorrect", "%d rows had to be skipped because the number of values was incorrect", $skipCount), $skipCount) . ".<br />";
			}
		}
		
		if (isset($dbErrors)) {
			$message .= __("The following errors were reported") . ":<br />";
			foreach ($dbErrors as $merr) {
				$message .= " - " . $merr . "<br />";
			}
		}
		
		?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/REC-html40/strict.dtd">

	<html xmlns="http://www.w3.org/1999/xhtml" version="-//W3C//DTD XHTML 1.1//EN" xml:lang="en">
	<head>
	</head>
	<body>	
		<script type="text/javascript">
		
		parent.updateAfterImport("<?php echo trim(addslashes(nl2br($message))); ?>");
		parent.refreshRowCount();
		
		</script>
	</body>
	</html>
		
		<?php
		
	}

} else if ($file == 'ajaxquery.php') {

	include "functions.php";

	loginCheck();

	if (isset($db))
		$conn->selectDB($db);

	if (isset($_POST['query'])) {
		$queryList = splitQueryText($_POST['query']);
		
		foreach ($queryList as $query) {
			$sql = $conn->query($query);
		}
	}

	//return the first field from the first row
	if (!isset($_POST['silent']) && $conn->isResultSet($sql)) {
		$row = $conn->fetchArray($sql);
		echo nl2br(htmlentities($row[0], ENT_QUOTES, 'UTF-8'));
	}

} else if ($file == 'ajaxsavecolumnedit.php') {
	
	include "functions.php";

	loginCheck();

	if (isset($db))
		$conn->selectDB($db);

	if (isset($_POST['runQuery'])) {
		$query = $_POST['runQuery'];
		
		$conn->query($query) or ($dbError = $conn->error());
		
		echo "{\n";
		echo "    \"formupdate\": \"" . $_GET['form'] . "\",\n";
		echo "    \"errormess\": \"";
		if (isset($dbError))
			echo $dbError;
		echo "\"\n";
		echo '}';
		
	}

} else if ($file == 'ajaxsaveedit.php') {

	include "functions.php";

	loginCheck();

	if (isset($db))
		$conn->selectDB($db);

	if ($_POST && isset($table)) {
		
		$insertChoice = "";
		
		if (isset($_POST['SB_INSERT_CHOICE'])) {
			$insertChoice = $_POST['SB_INSERT_CHOICE'];
		}
		
		$structureSql = $conn->describeTable($table);
		
		if ($conn->getAdapter() == "mysql") {
			while ($structureRow = $conn->fetchAssoc($structureSql)) {
				$pairs[$structureRow['Field']] = '';
				$types[$structureRow['Field']] = $structureRow['Type'];
				$nulls[$structureRow['Field']] = (isset($structureRow['Null'])) ? $structureRow['Null'] : "YES";
			}
		} else if ($conn->getAdapter() == "sqlite") {
			foreach ($structureRow as $column) {
				$pairs[$column[0]] = '';
			}
		}
		
		foreach ($_POST as $key=>$value) {
			if ($key != "SB_INSERT_CHOICE") {	
				if (is_array($value)) {
					$value = implode(",", $value);
				}
				
				$pairs[$key] = $conn->escapeString($value);
			}
		}
		
		if (isset($pairs)) {
			
			if ($insertChoice != "INSERT") {
				$updates = "";
				
				foreach ($pairs as $keyname=>$value) {
					if ($conn->getAdapter() == "mysql") {
						if (isset($types) && substr($value, 0, 2) == "0x" && isset($binaryDTs) && in_array($types[$keyname], $binaryDTs)) {
							$updates .= "`" . $keyname . "`=" . $value . ",";
						} else if (!$value && $nulls[$keyname] == "YES") {
							$updates .= "`" . $keyname . "`=NULL,";
						} else {
							$updates .= "`" . $keyname . "`='" . $value . "',";
						}
					} else if ($conn->getAdapter() == "sqlite") {
						$updates .= "'" . $keyname . "'='" . $value . "',";
					}
				}
				
				$updates = substr($updates, 0, -1);
				
				if (isset($_GET['queryPart']))
					$queryPart = $_GET['queryPart'];
				else
					$queryPart = "";
				
				if ($conn->getAdapter() == "mysql") {
					$query = "UPDATE `$table` SET " . $updates . " " . $queryPart;
				} else if ($conn->getAdapter() == "sqlite") {
					$query = "UPDATE '$table' SET " . $updates . " " . $queryPart;
				}
				
			} else {
				$columns = "";
				$values = "";
				
				foreach ($pairs as $keyname=>$value) {
					
					if ($conn->getAdapter() == "mysql") {
						$columns .= "`" . $keyname . "`,";
					} else if ($conn->getAdapter() == "sqlite") {
						$columns .= "'" . $keyname . "',";
					}
					
					if (isset($types) && substr($value, 0, 2) == "0x" && isset($binaryDTs) && in_array($types[$keyname], $binaryDTs)) {
						$values .= $value . ",";
					} else {
						$values .= "'" . $value . "',";
					}
					
				}
				
				$columns = substr($columns, 0, -1);
				$values = substr($values, 0, -1);
				
				if ($conn->getAdapter() == "mysql") {
					$query = "INSERT INTO `$table` ($columns) VALUES ($values)";
				} else if ($conn->getAdapter() == "sqlite") {
					$query = "INSERT INTO '$table' ($columns) VALUES ($values)";
				}
			}
			
			$conn->query($query) or ($dbError = $conn->error());
			
			echo "{\n";
			echo "    \"formupdate\": \"" . $_GET['form'] . "\",\n";
			echo "    \"errormess\": \"";
			if (isset($dbError))
				echo $dbError;
			echo "\"\n";
			echo '}';
			
		}
	}

} else if ($file == 'ajaxsaveuseredit.php') {

	include "functions.php";

	loginCheck();

	$conn->selectDB("mysql");

	function removeAdminPrivs($priv) {
		if ($priv == "FILE" || $priv == "PROCESS" ||  $priv == "RELOAD" ||  $priv == "SHUTDOWN" ||  $priv == "SUPER")
			return false;
		else
			return true;
	}

	if (isset($_GET['user']))
		$user = $_GET['user'];

	if (isset($_POST['NEWPASS']))
		$newPass = $_POST['NEWPASS'];

	if (isset($_POST['CHOICE']))
		$choice = $_POST['CHOICE'];

	if (isset($_POST['ACCESSLEVEL']))
		$accessLevel = $_POST['ACCESSLEVEL'];
	else
		$accessLevel = "GLOBAL";

	if ($accessLevel != "LIMITED")
		$accessLevel = "GLOBAL";

	if (isset($_POST['DBLIST']))
		$dbList = $_POST['DBLIST'];
	else
		$dbList = array();

	if (isset($_POST['PRIVILEGES']))
		$privileges = $_POST['PRIVILEGES'];
	else
		$privileges = array();

	if (isset($_POST['GRANTOPTION']))
		$grantOption = $_POST['GRANTOPTION'];

	if (isset($user) && ($accessLevel == "GLOBAL" || ($accessLevel == "LIMITED" && sizeof($dbList) > 0))) {
		
		if ($choice == "ALL") {
			$privList = "ALL";
		} else {
			if (isset($privileges) && count($privileges) > 0)
				$privList = implode(", ", $privileges);
			else
				$privList = "USAGE";
				
			if (sizeof($privileges) > 0) {
				if ($accessLevel == "LIMITED") {
					$privileges = array_filter($privileges, "removeAdminPrivs");
				}
				
				$privList = implode(", ", $privileges);
			} else {
				$privList = "USAGE";
			}
			
		}
		
		$split = explode("@", $user);
		
		if (isset($split[0]))
			$name = $split[0];
		
		if (isset($split[1]))
			$host = $split[1];
		
		if (isset($name) && isset($host)) {
			$user = "'" . $name . "'@'" . $host . "'";
			
			if ($accessLevel == "LIMITED") {
				$conn->query("DELETE FROM `db` WHERE `User`='$name' AND `Host`='$host'");
				
				foreach ($dbList as $theDb) {	
					$query = "GRANT " . $privList . " ON `$theDb`.* TO " . $user;
					
					if (isset($grantOption))
						$query .= " WITH GRANT OPTION";
					
					$conn->query($query) or ($dbError = $conn->error());
				}
			} else {
				$conn->query("REVOKE ALL PRIVILEGES ON *.* FROM " . $user);
				$conn->query("REVOKE GRANT OPTION ON *.* FROM " . $user);
				
				$query = "GRANT " . $privList . " ON *.* TO " . $user;
			
				if (isset($grantOption))
					$query .= " WITH GRANT OPTION";
				
				$conn->query($query) or ($dbError = $conn->error());
			}
			
			if (isset($newPass))
				$conn->query("SET PASSWORD FOR '$name'@'$host' = PASSWORD('$newPass')") or ($dbError = $conn->error());
			
			$conn->query("FLUSH PRIVILEGES") or ($dbError = $conn->error());
			
			echo "{\n";
			echo "    \"formupdate\": \"" . $_GET['form'] . "\",\n";
			echo "    \"errormess\": \"";
			if (isset($dbError))
				echo $dbError;
			echo "\"\n";
			echo '}';
		}
	}

} else if ($file == 'browse.php') {

	include "functions.php";

	loginCheck();

	requireDatabaseAndTableBeDefined();

	if (isset($db))
		$conn->selectDB($db);

	//run delete queries

	if (isset($_POST['runQuery'])) {
		$runQuery = $_POST['runQuery'];
		
		$queryList = splitQueryText($runQuery);
		foreach ($queryList as $query) {
			$conn->query($query);
		}
	}

	if ($conn->getAdapter() == "sqlite") {
		$query = "SELECT * FROM '$table'";
	} else {
		$query = "SELECT * FROM `$table`";
	}

	$queryTable = $table;

	if (isset($_POST['s']))
		$start = (int)($_POST['s']);
	else
		$start = 0;

	if (isset($_POST['sortKey']))
		$sortKey = $_POST['sortKey'];

	if (isset($_POST['sortDir']))
		$sortDir = $_POST['sortDir'];
	else if (isset($sortKey))
		$sortDir = "ASC";

	if (isset($_POST['view']) && $_POST['view'] == "1")
		$view = 1;
	else
		$view = 0;

	if (isset($sortKey) && $sortKey != "" && isset($sortDir) && $sortDir != "") {
		$sort = "ORDER BY `" . $sortKey . "` " . $sortDir;
	} else {
		$sort = "";
	}

	$totalRows = 0;
	$insertCount = 0;
	$queryTime = 0;

	$perPage = (isset($sbconfig) && array_key_exists("RowsPerPage", $sbconfig)) ? $sbconfig['RowsPerPage'] : 100;

	$displayLimit = 1000;

	$query = trim($query);

	if ($query) {

		if (!isset($queryTable)) {
			$querySplit = splitQueryText($query);
		} else {
			$querySplit[] = $query;
		}
		
		foreach ($querySplit as $q) {
			$q = trim($q, "\n");
			if ($q != "") {
				if (isset($queryTable)) {
					$totalRows = $conn->tableRowCount($queryTable);
		
					if ($start > $totalRows) {
						$start = 0;
					}
					
					$q = "$q $sort LIMIT $start, $perPage";
				}
				
				$queryStartTime = microtime_float();
				$dataSql = $conn->query($q) or ($dbError[] = $conn->error());
				$queryFinishTime = microtime_float();
				$queryTime += round($queryFinishTime - $queryStartTime, 4);
				
				if ($conn->affectedRows($dataSql)) {
					$insertCount += (int)($conn->affectedRows($dataSql));
				}
			}
		}
			
		if (!isset($queryTable)) {
			$totalRows = (int)($conn->rowCount($dataSql));
			
			// running rowCount on PDO resets the result set
			// so we need to run the query again
			if ($conn->getMethod() == "pdo") {
				$dataSql = $conn->query($q);
			}
		}
		
	}

	//for the browse tab
	if (isset($queryTable) && $conn->getAdapter() == "sqlite") {
		$structure = $conn->describeTable($queryTable);
		
		if (sizeof($structure) > 0) {
			foreach ($structure as $column) {	
				if (strpos($column[1], "primary key") > 0) {
					$primaryKeys[] = $column[0];
				}
			}
		}
	} else if (isset($queryTable) && $conn->getAdapter() == "mysql") {
		$structureSql = $conn->describeTable($queryTable);
		
		if ($conn->isResultSet($structureSql)) {
			while ($structureRow = $conn->fetchAssoc($structureSql)) {	
				$explosion = explode("(", $structureRow['Type'], 2);
				
				$tableTypes[] = $explosion[0];
				
				if ($structureRow['Key'] == "PRI") {
					$primaryKeys[] = $structureRow['Field'];
				}
			}
		}
	}

	echo '<div class="browsetab">';

	if (isset($dbError)) {
		echo '<div class="errormessage" style="margin-left: 5px; width: 536px"><strong>' . __("The following errors were reported") . ':</strong>';
		foreach ($dbError as $error) {
			echo $error . "<br />";
		}
		echo '</div>';
	} else {
		
		if (isset($totalRows) && $totalRows > 0) {
			
			if (isset($queryTable)) {
				
				echo '<table class="browsenav">';
				echo '<tr>';
				echo '<td class="options">';
				
				if (isset($primaryKeys) && count($primaryKeys)) {
					
					echo __("Select") . ':&nbsp;&nbsp;<a onclick="checkAll()">' . __("All") . '</a>&nbsp;&nbsp;<a onclick="checkNone()">' . __("None") . '</a>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . __("With selected") . ':&nbsp;&nbsp;<a onclick="editSelectedRows()">' . __("Edit") . '</a>&nbsp;&nbsp;<a onclick="deleteSelectedRows()">' . __("Delete") . '</a>';
					
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a onclick="sb.loadPage()">' . __("Refresh") . '</a>';
					
				} else {
					echo '<span style="color: rgb(150, 150, 150)">[' . __("No primary key defined") . ']</span>';
				}
				
				echo '</td>';
				
				echo '<td class="right">';
				
				$totalPages = ceil($totalRows / $perPage);
				$currentPage = floor($start / $perPage) + 1;
				
				if ($currentPage > 1) {
					echo '<a id="firstNav" onclick="browseNav(0,' . $view . ')">' . __("First") . '</a>';
					echo '<a id="prevNav" onclick="browseNav(' . (($currentPage - 2) * $perPage) . ',' . $view . ')">' . __("Prev") . '</a>';
				}
				
				echo '<span class="paginator">';
				
				if ($currentPage == 1) {
					$startPage = 1;
					$finishPage = 3;
					
					if ($finishPage > $totalPages)
						$finishPage = $totalPages;
					
				} else if ($currentPage == $totalPages) {
					$startPage = $totalPages - 2;
					$finishPage = $totalPages;
					
					if ($startPage < 1)
						$startPage = 1;
				} else {
					$startPage = $currentPage - 1;
					$finishPage = $currentPage + 1;
				}
				
				if ($startPage != $finishPage) {
					for ($bnav=$startPage; $bnav<=$finishPage; $bnav++) {
						echo '<a';
						
						if ($bnav == $currentPage)
							echo ' class="selected"';
						
						echo ' onclick="browseNav(' . (($bnav - 1) * $perPage) . ',' . $view . ')">' . number_format($bnav) . '</a>';
					}
				}
				
				echo '</span>';
				
				if ($currentPage < $totalPages) {
					echo '<a id="nextNav" onclick="browseNav(' . ($currentPage * $perPage) . ',' . $view . ')">' . __("Next") . '</a>';
					echo '<a id="lastNav" onclick="browseNav(' . (($totalPages - 1) * $perPage) . ',' . $view . ')">' . __("Last") . '</a>';
				}
				
				echo '</td>';
				echo '</tr>';
				echo '</table>';
				
			} else {
				echo '<table class="browsenav">';
				echo '<tr>';
				echo '<td class="options">';
				
				printf(__p("Your query returned %d result.", "Your query returned %d results.", $totalRows), $totalRows);
				echo " " . sprintf(__("(%.4f seconds)"), $queryTime);
				
				if ($totalRows > $displayLimit)
					echo ' (' . sprintf(__("Note: To avoid crashing your browser, only the first %d results have been displayed"), $displayLimit) . '.)';
				echo '</td>';
				echo '</tr>';
				echo '</table>';
			}
			
			echo '<div class="grid">';
			
			if (isset($primaryKeys) && count($primaryKeys)) {
				echo '<div class="emptyvoid" style="width: 30px">&nbsp;</div>';
			}
			
			echo '<div class="gridheader';
			
			if (!isset($queryTable))
				echo ' nosort';
			
			echo '">';
			
			echo '<div class="gridheaderinner">';
			echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
				
			if ($conn->isResultSet($dataSql)) {
				$dataRow = $conn->fetchAssoc($dataSql);
				$g = 0;
				$numFields = 0;
				
				foreach ($dataRow as $key=>$value) {
					
					if ((isset($sortKey) && $sortKey == $key) && (isset($sortDir) && $sortDir == "ASC")) {
						$outputDir = "DESC";
					} elseif (isset($sortKey) && $sortKey == $key) {
						$outputDir = "ASC";
					} elseif (isset($sortDir) && $sortDir) {
						$outputDir = $sortDir;
					} else {
						$outputDir = "ASC";
					}
					echo '<td><div column="' . ++$g . '" class="headertitle column' . $g;
					if (isset($sortKey) && $sortKey == $key) {
						echo ' sort';
					}
					
					if (isset($tableTypes) && in_array($tableTypes[$g - 1], $textDTs)) {
						echo ' longtext';
					}
					
					if (isset($tableTypes) && in_array($tableTypes[$g - 1], $numericDTs)) {
						echo ' numeric';
					}
					
					echo '"';
					
					if (isset($queryTable))
						echo ' onclick="loadNewSort(\'' . $key . '\', \'' . $outputDir . '\')"';
					
					echo '>';
					
					if ((isset($sortKey) && $sortKey == $key) && (isset($sortDir) && $sortDir == "DESC")) {
						echo '<div class="sortdesc">' . $key . '</div>';
					} elseif ((isset($sortKey) && $sortKey == $key) && (isset($sortDir) && $sortDir == "ASC")) {
						echo '<div class="sortasc">' . $key . '</div>';
					} else {
						echo $key;
					}
					
					$fieldList[] = $key;
					
					echo '</div>';
					echo '</td>';
					echo '<td><div class="columnresizer"></div></td>';
					$numFields++;
				}
				echo '<td><div class="emptyvoid" style="width: 30px; border-right: 0">&nbsp;</div></td>';
				echo '</tr>';
				echo '</table>';
							
			}
			
			echo '</div>';
			echo '</div>';
			
			$dataSql = $conn->query($q);
			
			$queryBuilder = "";
			
			if (isset($primaryKeys) && count($primaryKeys) > 0) {
				
				echo '<div class="leftchecks">';
				
				$m = 0;
				
				while (($dataRow = $conn->fetchAssoc($dataSql)) && ($m < $displayLimit)) {
					
					$queryBuilder = "WHERE ";
					foreach ($primaryKeys as $primary) {
						if ($conn->getAdapter() == "sqlite") {
							$queryBuilder .= "" . $primary . "='" . $dataRow[$primary] . "' AND ";
						} else {
							$queryBuilder .= "`" . $primary . "`='" . $dataRow[$primary] . "' AND ";
						}
					}
					$queryBuilder = substr($queryBuilder, 0, -5);
					
					if ($conn->getAdapter() == "mysql") {
						$queryBuilder .= " LIMIT 1";
					}
					
					echo '<dl class="manip';
					
					if ($m % 2 == 1)
						echo ' alternator';
					else 
						echo ' alternator2';
					
					echo '">';
					echo '<dt><input type="checkbox" class="check' . $m . '" onclick="rowClicked(' . $m . ')" querybuilder="' . $queryBuilder . '" /></dt>';
					echo '<dd><a onclick="fullTextWindow(' . $m . ')"></a></dd>';
					echo '</dl>';
					
					$m++;
				}
				
				echo '</div>';
				
				$dataSql = $conn->query($q);
				
			}
			
			if (isset($primaryKeys) && count($primaryKeys))
				echo '<div class="gridscroll withinfo">';
			else
				echo '<div class="gridscroll">';
			
			$m = 0;
			
			while (($dataRow = $conn->fetchArray($dataSql)) && ($m < $displayLimit)) {
				
				echo '<table cellpadding="0" cellspacing="0" class="row' .($m). ' browse';
				
				if ($m % 2 == 1)
					echo ' alternator';
				else 
					echo ' alternator2';
				
				echo '">';
				echo '<tr>';
				echo '<td>';
				
				echo '<table cellpadding="0" cellspacing="0">';
				echo '<tr>';
				
				for ($i=0; $i<$numFields; $i++) {
					echo '<td><div class="item column' . ($i + 1);
					if (isset($tableTypes) && in_array($tableTypes[$i], $textDTs)) {
						echo ' longtext';
					}
					if (isset($tableTypes) && in_array($tableTypes[$i], $numericDTs)) {
						echo ' numeric';
					}
					echo '" fieldname="' . $fieldList[$i] . '">';
					
					if (isset($tableTypes) && in_array($tableTypes[$i], $binaryDTs)) {
						echo '<span class="binary">(' . __("binary data") . ')</span>';
					} else if (is_numeric($dataRow[$i]) && stristr($fieldList[$i], "Date") !== false && strlen($dataRow[$i]) > 7 && strlen($dataRow[$i]) < 14) {
						echo '<span title="' . date("F j, Y g:ia", $dataRow[$i]) . '">' . formatForOutput($dataRow[$i]) . '</span>';
					} else {
						echo formatForOutput($dataRow[$i]);
					}
					
					echo '</div></td>';
				}
				echo '</tr>';
				echo '</table>';
				echo '</td>';
				echo '</tr>';
				echo '</table>';
				
				$m++;
			}
			echo '</div>';
			echo '</div>';
			
			?>
			
			<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
			setTimeout(function(){ startGrid(); }, 1);
			</script>
			
			<?php
		} else {
			if ($insertCount)
				echo '<div class="statusmessage" style="margin: 0 5px 10px">' . sprintf(__("Your query affected %d rows."), $insertCount) . '</div>';
			
			if (isset($queryTable) && $queryTable) {
				?>
				
				<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
				
				topTabLoad(1);
				
				</script>
				
				<?php
			} else {
				echo '<div class="statusmessage" style="margin-left: 5px">' . __("Your query did not return any results.") . " " . sprintf(__("(%.4f seconds)"), $queryTime) . '</div>';
			}
		}
	}

	echo '</div>';
} else if ($file == 'dboverview.php') {

	include "functions.php";

	loginCheck();

	if (isset($db)) {

	$conn->selectDB($db);

	//run delete queries

	if (isset($_POST['runQuery'])) {
		
		$runQuery = $_POST['runQuery'];
		
		$queryList = splitQueryText($runQuery);
		
		foreach ($queryList as $query) {
			$query = trim($query);
			
			if ($query != "") {
				$conn->query($query) or ($dbError = $conn->error());
				
				// make a list of the tables that were dropped/emptied
				if (substr($query, 0, 10) == "DROP TABLE")
					$droppedList[] = substr($query, 12, -1);
				
				if (substr($query, 0, 10) == "TRUNCATE `")
					$emptiedList[] = substr($query, 10, -1);
				
				if (substr($query, 0, 13) == "DELETE FROM '")
					$emptiedList[] = substr($query, 13, -1);
				
			}
		}
	}

	// if tables were dropped, remove them from the side menu
	if (isset($droppedList) && isset($db)) {
		?>
		
		<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
		
		var targ = $(getSubMenuId('<?php echo $db . "','" . $droppedList[0]; ?>'));
		while (!targ.hasClass("sublist")) {
			targ = targ.parentNode;
		}
		var toRecalculate = targ.id;
		
		<?php
		for ($mn=0; $mn<count($droppedList); $mn++) {
		?>
			$(getSubMenuId('<?php echo $db . "','" . $droppedList[$mn]; ?>')).dispose();
		<?php
		}
		?>
		
		recalculateSubmenuHeight(toRecalculate);
		
		</script>
		
		<?php
	}

	// if tables were emptied, reset their counts in js
	if (isset($emptiedList) && isset($db)) {
		?>
		
		<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
		<?php
		
		for ($mn=0; $mn<count($emptiedList); $mn++) {
			echo "sb.tableRowCounts[\"" . $db . "_" . $emptiedList[$mn] . "\"] = \"0\";\n";
			echo "var sideA = $(getSubMenuId('" . $db . "', '" . $emptiedList[$mn] . "'));\n";
			echo 'var subc = $E(".subcount", sideA);';
			echo "\nsubc.set(\"text\", \"(0)\");\n";
		}
		
		?>
		</script>
		
		<?php
	}


	if (isset($dbError)) {
		echo '<div class="errormessage" style="margin: 6px 12px 10px 7px; width: 550px"><strong>';
		echo __("Error performing operation");
		echo '</strong><p>' . $dbError . '</p></div>';
	}

	?>

	<table cellpadding="0" class="dboverview" width="750" style="margin: 5px 7px 0">
	<tr>
	<td>

	<?php

	$tableSql = $conn->listTables();

	if ($conn->isResultSet($tableSql)) {
		
		echo '<div style="margin-bottom: 15px">';
		
		echo '<table class="browsenav">';
		echo '<tr>';
		echo '<td class="options">';
		
		echo __("Select") . ':&nbsp;&nbsp;<a onclick="checkAll()">' . __("All") . '</a>&nbsp;&nbsp;<a onclick="checkNone()">' . __("None") . '</a>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . __("With selected") . ':&nbsp;&nbsp;<a onclick="emptySelectedTables()">' . __("Empty") . '</a>&nbsp;&nbsp;<a onclick="dropSelectedTables()">' . __("Drop") . '</a>';
		
		if ($conn->getAdapter() == "mysql") {
			echo '&nbsp;&nbsp;<a onclick="optimizeSelectedTables()">' . __("Optimize") . '</a>';
		}
		
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		
		echo '<div class="grid">';
		
		echo '<div class="emptyvoid">&nbsp;</div>';
		
		echo '<div class="gridheader impotent">';
			echo '<div class="gridheaderinner">';
			echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
			echo '<td><div column="1" class="headertitle column1">' . __("Table") . '</div></td>';
			echo '<td><div class="columnresizer"></div></td>';
			echo '<td><div column="2" class="headertitle column2">' . __("Rows") . '</div></td>';
			echo '<td><div class="columnresizer"></div></td>';
			
			if (isset($charsetList) && isset($collationList)) {
				echo '<td><div column="3" class="headertitle column3">' . __("Charset") . '</div></td>';
				echo '<td><div class="columnresizer"></div></td>';
				echo '<td><div column="4" class="headertitle column4">' . __("Overhead") . '</div></td>';
				echo '<td><div class="columnresizer"></div></td>';
			} else if ($conn->getAdapter() == "mysql") {
				echo '<td><div column="3" class="headertitle column3">' . __("Overhead") . '</div></td>';
				echo '<td><div class="columnresizer"></div></td>';
			}
			
			echo '<td><div class="emptyvoid" style="border-right: 0">&nbsp;</div></td>';
			echo '</tr>';
			echo '</table>';
			echo '</div>';
		echo '</div>';
		
		echo '<div class="leftchecks" style="max-height: 400px">';
		
		$m = 0;
		
		while ($tableRow = $conn->fetchArray($tableSql)) {
			echo '<dl class="manip';
			
			if ($m % 2 == 1)
				echo ' alternator';
			else 
				echo ' alternator2';
			
			echo '"><dt><input type="checkbox" class="check' . $m . '" onclick="rowClicked(' . $m++ . ')" querybuilder="' . $tableRow[0] . '" /></dt></dl>';
		}
		
		echo '</div>';
		
		$tableSql = $conn->listTables();
		
		echo '<div class="gridscroll withchecks" style="overflow-x: hidden; max-height: 400px">';
		
		$m = 0;
		
		while ($tableRow = $conn->fetchArray($tableSql)) {
			
			$rowCount = $conn->tableRowCount($tableRow[0]);
			
			if ($conn->getAdapter() == "mysql") {
				$infoSql = $conn->query("SHOW TABLE STATUS LIKE '" . $tableRow[0] . "'");
				$infoRow = $conn->fetchAssoc($infoSql);
				
				$overhead = $infoRow["Data_free"];
				
				$formattedOverhead = "";
				
				if ($overhead > 0)
					$formattedOverhead = memoryFormat($overhead);
			}
			
			echo '<div class="row' . $m . ' browse';
			
			if ($m % 2 == 1) { echo ' alternator'; }
			else 
			{ echo ' alternator2'; }
			
			echo '">';
			echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
			echo '<td><div class="item column1"><div style="float: left; overflow: hidden; width: 185px">' . $tableRow[0] . '</div><img src="http://sqlbuddylite.googlecode.com/svn/tags/r/goto.png" class="goto" onclick="subTabLoad(\'' . $db . '\', \'' . $tableRow[0] . '\')" /></div></td>';
			echo '<td><div class="item column2">' . number_format($rowCount) . '</div></td>';
			
			if (isset($collationList) && array_key_exists("Collation", $infoRow)) {
				echo '<td><div class="item column3">' . $collationList[$infoRow['Collation']] . '</div></td>';
				echo '<td><div class="item column4">' . $formattedOverhead . '</div></td>';
			} else if ($conn->getAdapter() == "mysql") {
				echo '<td><div class="item column4">' . $formattedOverhead . '</div></td>';
			}
			
			echo '</tr>';
			echo '</table>';
			echo '</div>';
			
			$m++;
		}
		
		echo '</div>';
		echo '</div>';
		
		echo '<br />';
		
	}

	if ($conn->getAdapter() != "sqlite") {

	?>

	<div class="inputbox" style="width: 275px; margin-bottom: 15px">
	<h4><?php echo __("Options"); ?></h4>

	<a onclick="confirmDropDatabase()"><?php printf(__("Drop the '%s' database"), $db); ?></a>
	</div>

	<?php

	}


	if (isset($charsetList)) {

	$currentChar = "";
	$currentCharSql = $conn->query("SHOW VARIABLES LIKE 'character_set_database'");

	if ($conn->isResultSet($currentCharSql)) {
		$currentChar = $conn->result($currentCharSql, 0, "Value");
	}

	?>

	<div class="inputbox" style="width: 325px; margin-bottom: 15px">
	<h4><?php echo __("Edit database"); ?></h4>

	<div id="editDatabaseMessage"></div>
	<form onsubmit="editDatabase(); return false">
	<table cellpadding="4">

	<?php

		echo "<tr>";
		echo "<td class=\"secondaryheader\">";
		echo __("Charset") . ":";
		echo "</td>";
		echo "<td class=\"inputarea\">";
		echo "<select id=\"DBRECHARSET\" style=\"width: 145px\">";
		echo "<option></option>";
		foreach ($charsetList as $charset) {
			echo "<option value=\"" . $charset . "\"";
			
			if (isset($currentChar) && $charset == $currentChar)
				echo " selected=\"selected\"";
			
			echo ">" . $charset . "</option>";
		}
		echo "</select>";
		echo "</td>";
		echo '<td align="left" style="padding-left: 10px">';
		echo '<input type="submit" class="inputbutton" value="' . __("Submit") . '" />';
		echo '</td>';
		echo "</tr>";

	?>

	</table>
	</form>
	</div>

	<?php

	}

	?>

	<div id="reporterror" class="errormessage" style="display: none; margin-bottom: 15px"></div>

	<div class="inputbox">
		<h4><?php echo __("Create a new table"); ?></h4>
		
		<form onsubmit="createTable(); return false">
		<table cellpadding="0" style="width: 300px">
		<tr>
			<td class="secondaryheader" style="width: 80px">
			<?php echo __("Name") ?>:
			</td>
			<td>
			<input type="text" class="text" id="TABLENAME" style="width: 150px" />
			</td>
		</tr>
		<?php
		
		if (isset($charsetList)) {
			echo "<tr>";
			echo "<td class=\"secondaryheader\" style=\"width: 60px\">";
			echo __("Charset") . ":";
			echo "</td>";
			echo "<td>";
			echo "<select id=\"TABLECHARSET\" style=\"width: 155px\">";
			echo "<option></option>";
			foreach ($charsetList as $charset) {
				echo "<option value=\"" . $charset . "\"";
				
				if (isset($currentChar) && $charset == $currentChar)
					echo " selected=\"selected\"";
				
				echo ">" . $charset . "</option>";
			}
			echo "</select>";
			echo "</td>";
			echo "</tr>";
		}
		
		?>
		<tr>
			<td style="padding-top: 5px; color: gray" colspan="2">
			<?php echo __("Setup the fields for the table below"); ?>:
			</td>
		</tr>
		</table>
		<div id="fieldlist">
			
			<div class="fieldbox">
			<table cellpadding="0" class="overview">
			<tr>
			<td colspan="4" class="fieldheader">
			<span class="fieldheadertitle">&lt;<?php echo __("New field"); ?>&gt;</span>
			<a class="fieldclose" onclick="removeField(this)"></a>
			</td>
			</tr>
			<?php
			
			if ($conn->getAdapter() == "mysql") {
				
				?>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Name"); ?>:
				</td>
				<td>
				<input type="text" class="text" name="NAME" onkeyup="updateFieldName(this)" />
				</td>
				<td class="secondaryheader" style="padding-left: 5px">
				<?php echo __("Type"); ?>:
				</td>
				<td>
				<select name="TYPE" onchange="updateFieldName(this); toggleValuesLine(this)">
				<?php
				
				foreach ($typeList as $type) {
					echo '<option value="' . $type . '">' . $type . '</option>';
				}
				
				?>
				</select>
				</td>
				</tr>
				<tr class="valueline" style="display: none">
				<td class="secondaryheader">
				<?php echo __("Values"); ?>:
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="VALUES" onkeyup="updateFieldName(this)" />
				</td>
				<td colspan="2" style="color: gray">
				<?php echo __("Enter in the format: ('1','2')"); ?>
				</td>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Size") ?>:
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="SIZE" onkeyup="updateFieldName(this)" />
				</td>
				<td class="secondaryheader" style="padding-left: 5px">
				<?php echo __("Key"); ?>:
				</td>
				<td class="inputarea">
				<select name="KEY" onchange="updateFieldName(this)">
				<option value=""></option>
				<option value="primary"><?php echo __("primary"); ?></option>
				<option value="unique"><?php echo __("unique"); ?></option>
				<option value="index"><?php echo __("index"); ?></option>
				</select>
				</td>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Default") ?>:
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="DEFAULT" onkeyup="updateFieldName(this)" />
				</td>
				<?php
				
				if (isset($charsetList)) {
					echo "<td class=\"secondaryheader\" style=\"padding-left: 5px\">";
					echo __("Charset") . ":";
					echo "</td>";
					echo "<td class=\"inputarea\">";
					echo "<select name=\"CHARSET\" onchange=\"updateFieldName(this)\">";
					echo "<option></option>";
					foreach ($charsetList as $charset) {
						echo "<option value=\"" . $charset . "\">" . $charset . "</option>";
					}
					echo "</select>";
					echo "</td>";
				} else {
					echo "<td></td>";
					echo "<td></td>";
				}
				
				?>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Other"); ?>:
				</td>
				<td colspan="3">
				<label><input type="checkbox" name="UNSIGN" onchange="updateFieldName(this)"><?php echo __("Unsigned"); ?></label>
				<label><input type="checkbox" name="BINARY" onchange="updateFieldName(this)"><?php echo __("Binary"); ?></label>
				<label><input type="checkbox" name="NOTNULL" onchange="updateFieldName(this)"><?php echo __("Not Null"); ?></label>
				<label><input type="checkbox" name="AUTO" onchange="updateFieldName(this)"><?php echo __("Auto Increment"); ?></label>
				</td>
				</tr>
				<?php
				
			} else if ($conn->getAdapter() == "sqlite") {
				
				?>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Name"); ?>:
				</td>
				<td>
				<input type="text" class="text" name="NAME" onkeyup="updateFieldName(this)" />
				</td>
				<td class="secondaryheader" style="padding-left: 5px">
				<?php echo __("Type"); ?>:
				</td>
				<td>
				<select name="TYPE" onchange="updateFieldName(this)">
				<option value="">typeless</option>
				<?php
				
				foreach ($sqliteTypeList as $type) {
					echo '<option value="' . $type . '">' . $type . '</option>';
				}
				
				?>
				</select>
				</td>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Size") ?>:
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="SIZE" onkeyup="updateFieldName(this)" />
				</td>
				<td class="secondaryheader" style="padding-left: 5px">
				<?php echo __("Key"); ?>:
				</td>
				<td class="inputarea">
				<select name="KEY" onchange="updateFieldName(this)">
				<option value=""></option>
				<option value="primary"><?php echo __("primary"); ?></option>
				</select>
				</td>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Default") ?>:
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="DEFAULT" onkeyup="updateFieldName(this)" />
				</td>
				<td></td>
				<td></td>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Other"); ?>:
				</td>
				<td colspan="3">
				<label><input type="checkbox" name="NOTNULL" onchange="updateFieldName(this)"><?php echo __("Not Null"); ?></label>
				<label><input type="checkbox" name="UNIQUE" onchange="updateFieldName(this)"><?php echo __("Unique"); ?></label>
				<?php
				
				// autoincrement supported in SQLite 3+
				if (version_compare($conn->getVersion(), "3.0.0", ">=")) {
				?>
					<label><input type="checkbox" name="AUTO" onchange="updateFieldName(this)"><?php echo __("Auto Increment"); ?></label>
				<?php
				}
				
				?>
				</td>
				</tr>
				<?php
				
			}
			
			?>
			</table>
			</div>
			
		</div>
			
		<table cellpadding="0" width="370" id="fieldcontrols">
		<tr>
		<td style="padding: 5px 0 4px">
		<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />
		</td>
		<td style="padding: 0px 4px 0" align="right" valign="top">
		<a onclick="addTableField()" style="font-size: 11px !important"><?php echo __("Add field"); ?></a><div style="visibility: hidden; height: 0"><input type="submit" /></div>
		</td>
		</tr>
		</table>
		</form>
		
	</div>

	</td>
	</table>

	<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
	setTimeout("startGrid()", 1);
	</script>

	<?php

	}else{
		
		?>
		
		<div class="errorpage">
		<h4><?php echo __("Oops"); ?></h4>
		<p><?php echo __("For some reason, the database parameter was not included with your request."); ?></p>
		</div>
		
		<?php
		exit;
		
	}

} else if ($file == 'edit.php') {

	include "functions.php";

	loginCheck();

	requireDatabaseAndTableBeDefined();

	if (isset($db))
		$conn->selectDB($db);

	if (isset($table))
		$structureSql = $conn->describeTable($table);

	if (isset($_POST['editParts'])) {
		$editParts = $_POST['editParts'];
		$editParts = explode("; ", $editParts);
		
		$totalParts = count($editParts);
		$counter = 0;
		
		$firstField = true;
		
		?>
		<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
		
		if ($('EDITFIRSTFIELD')) {
			$('EDITFIRSTFIELD').focus();
		}
		
		</script>
		<?php
		
		foreach ($editParts as $part) {
			
			$part = trim($part);
			
			if ($part != "" && $part != ";") {
			
			?>
			
			<form id="editform<?php echo $counter; ?>" querypart="<?php echo $part; ?>" onsubmit="saveEdit('editform<?php echo $counter; ?>'); return false;">
			<div class="errormessage" style="margin: 6px 12px 10px; width: 338px; display: none"></div>
			<table class="insert edit" cellspacing="0" cellpadding="0">
			<?php
			
			if ($conn->isResultSet($structureSql) && $conn->getAdapter() == "mysql") {
				
				$dataSql = $conn->query("SELECT * FROM `" . $table . "` " . $part);
				$dataRow = $conn->fetchAssoc($dataSql);
				
				while ($structureRow = $conn->fetchAssoc($structureSql)) {
					
					preg_match("/^([a-z]+)(.([0-9]+).)?(.*)?$/", $structureRow['Type'], $matches);
					
					$curtype = $matches[1];
					$cursizeQuotes = $matches[2];
					$cursize = $matches[3];
					$curextra = $matches[4];
					
					echo '<tr>';
					echo '<td class="fieldheader"><span style="color: steelblue">';
					if ($structureRow['Key'] == 'PRI') echo '<u>';
					echo $structureRow['Field'];
					if ($structureRow['Key'] == 'PRI') echo '</u>';
					echo "</span> " . $curtype . $cursizeQuotes . ' ' . $structureRow['Extra'] . '</td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td class="inputarea">';
					
					$showLargeEditor[] = "text";
					$showLargeEditor[] = "mediumtext";
					$showLargeEditor[] = "longtext";
					
					if (in_array($curtype, $showLargeEditor)) {
						echo '<textarea name="' . $structureRow['Field'] . '">' . $dataRow[$structureRow['Field']] . '</textarea>';
					}
					elseif ($curtype == "enum") {
						$trimmed = substr($structureRow['Type'], 6, -2);
						$listOptions = explode("','", $trimmed);
						echo '<select name="' . $structureRow['Field'] . '">';
						echo '<option> - - - - - </option>';
						foreach ($listOptions as $option) {
							echo '<option value="' . $option . '"';
							if ($option == $dataRow[$structureRow['Field']]) {
								echo ' selected="selected"';
							}
							echo '>' . $option . '</option>';
						}
						echo '</select>';
					}
					elseif ($curtype == "set") {
						$trimmed = substr($structureRow['Type'], 5, -2);
						$listOptions = explode("','", $trimmed);
						foreach ($listOptions as $option) {
							$id = $option . rand(1, 1000);
							echo '<label for="' . $id . '"><input name="' . $structureRow['Field'] . '[]" value="' . $option . '" id="' . $id . '" type="checkbox"';
							
							if (strpos($dataRow[$structureRow['Field']], $option) > -1)
								echo ' checked="checked"';
							
							echo '>' . $option . '</label><br />';
						}
					} else {
						echo '<input type="text"';
						if ($firstField)
							echo ' id="EDITFIRSTFIELD"';
						echo ' name="' . $structureRow['Field'] . '" class="text" value="';
						
						if ($dataRow[$structureRow['Field']] && isset($binaryDTs) && in_array($curtype, $binaryDTs)) {
							echo "0x" . bin2hex($dataRow[$structureRow['Field']]);
						} else {
							echo htmlentities($dataRow[$structureRow['Field']], ENT_QUOTES, 'UTF-8');
						}
						
						echo '" />';
					}
					
					$firstField = false;
					
					?>
					
					</td>
					</tr>
					
					<?php
				}
				
				$structureSql = $conn->describeTable($table);
				
			} else if (sizeof($structureSql) > 0 && $conn->getAdapter() == "sqlite") {
				
				$dataSql = $conn->query("SELECT * FROM '" . $table . "' " . $part);
				$dataRow = $conn->fetchAssoc($dataSql);
				
				foreach ($structureSql as $column) {
									
					echo '<tr>';
					echo '<td class="fieldheader"><span style="color: steelblue">';
					if (strpos($column[1], "primary key") > 0) echo '<u>';
					echo $column[0];
					if (strpos($column[1], "primary key") > 0) echo '</u>';
					echo "</span> " . $column[1] . '</td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td class="inputarea">';
					
					if (strpos($column[1], "text") !== false) {
						echo '<textarea name="' . $column[0] . '">' . $dataRow[$column[0]] . '</textarea>';
					} else {
						echo '<input type="text"';
						if ($firstField)
							echo ' id="EDITFIRSTFIELD"';
						echo ' name="' . $column[0] . '" class="text" value="' . htmlentities($dataRow[$column[0]], ENT_QUOTES, 'UTF-8') . '" />';
					}
					
					$firstField = false;
					
					?>
					
					</td>
					</tr>
					
					<?php
				}
				
				$structureSql = $conn->describeTable($table);
				
			}
			
			?>
			<tr>
			<td>
			<label><input type="radio" name="SB_INSERT_CHOICE" value="SAVE" checked="checked" /><?php echo __("Save changes to original"); ?></label><br />
			<label><input type="radio" name="SB_INSERT_CHOICE" value="INSERT" /><?php echo __("Insert as new row"); ?></label>
			</td>
			</tr>
			<tr>
			<td style="padding-top: 10px; padding-bottom: 25px">
			<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />&nbsp;&nbsp;<a onclick="cancelEdit('editform<?php echo $counter; ?>')"><?php echo __("Cancel"); ?></a>
			</td>
			</tr>
			</table>
			</form>
			
			
			<?php
			
			$counter++;
			
			}
			
		}
		
	}


} else if ($file == 'editcolumn.php') {

	include "functions.php";

	loginCheck();

	requireDatabaseAndTableBeDefined();

	if (isset($db))
		$conn->selectDB($db);

	if (isset($db))
		$structureSql = $conn->query("SHOW FULL FIELDS FROM `$table`");

	if (isset($_POST['editParts']) && $conn->isResultSet($structureSql)) {
		
		$editParts = $_POST['editParts'];
		
		$editParts = explode("; ", $editParts);
		
		$totalParts = count($editParts);
		$counter = 0;
		
		$firstField = true;
		
		?>
		<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
		
		if ($('EDITCOLUMNFIRSTFIELD')) {
			$('EDITCOLUMNFIRSTFIELD').focus();
		}
		
		</script>
		<?php
		
		while ($structureRow = $conn->fetchAssoc($structureSql)) {
			if (in_array($structureRow['Field'], $editParts)) {
				echo '<form id="editform' . $counter . '" querypart="' . $structureRow['Field'] . '" onsubmit="saveColumnEdit(\'editform' . $counter . '\'); return false;">';
				echo '<div class="editcolumn">';
				echo '<div class="errormessage" style="margin: 0 7px 13px; display: none"></div>';
				echo '<table class="edit" cellspacing="0" cellpadding="0">';
				
				preg_match("/^([a-z]+)(.([0-9]+).)?(.*)?$/", $structureRow['Type'], $matches);
				
				$curtype = $matches[1];
				$cursizeQuotes = $matches[2];
				$cursize = $matches[3];
				$curextra = $matches[4];
				
				?>
				
				<tr>
				<td class="secondaryheader">
				<?php echo __("Name:"); ?>
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="NAME"<?php if ($firstField) echo ' id="EDITCOLUMNFIRSTFIELD"'; ?> value="<?php echo $structureRow['Field']; ?>" style="width: 125px" />
				</td>
				<td class="secondaryheader">
				<?php echo __("Type:"); ?>
				</td>
				<td class="inputarea">
				<select name="TYPE" onchange="toggleValuesLine(this, 'editform<?php echo $counter; ?>')" style="width: 125px">
				<?php
				
				foreach ($typeList as $type) {
					echo '<option value="' . $type . '"';
					
					if ($type == $curtype)
						echo ' selected';
					
					echo '>' . $type . '</option>';
				}
				
				?>
				</select>
				</td>
				</tr>
				<?php
				
				echo '<tr class="valueline inputarea"';
				
				if (!($curtype == "enum" || $curtype == "set"))
					echo ' style="display: none"';
				
				echo '>';
				
				?>
				<td class="secondaryheader">
				<?php echo __("Values:"); ?>
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="VALUES" value="<?php if (substr($curextra, 0, 1) == "(" && substr($curextra, -1) == ")") echo $curextra; ?>" style="width: 125px" />
				</td>
				<td colspan="2">
				</td>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Size:"); ?>
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="SIZE" value="<?php echo $cursize; ?>" style="width: 125px" />
				</td>
				<td class="secondaryheader">
				<?php echo __("Default:"); ?>
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="DEFAULT" value="<?php echo $structureRow['Default']; ?>" style="width: 125px" />
				</td>
				</tr>
				<?php
				
				if (isset($charsetList) && isset($collationList)) {
					echo "<tr>";
					echo "<td class=\"secondaryheader\">";
					echo __("Charset:");
					echo "</td>";
					echo "<td class=\"inputarea\" colspan=\"3\">";
					echo "<select name=\"CHARSET\" style=\"width: 125px\">";
					echo "<option></option>";
					
					if ($structureRow['Collation'] != "NULL") {
						$currentCharset = $collationList[$structureRow['Collation']];
					}
					
					foreach ($charsetList as $charset) {
						echo "<option value=\"" . $charset . "\"";
						
						if (isset($currentCharset) && $charset == $currentCharset) {
							echo ' selected="selected"';
						}
						
						echo ">" . $charset . "</option>";
					}
					echo "</select>";
					echo "</td>";
					echo "</tr>";
				}
				
				?>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Other:"); ?>
				</td>
				<td colspan="3" class="inputarea">
				<label><input type="checkbox" name="UNSIGN"<?php if ($curextra == " unsigned") echo " checked"; ?>><?php echo __("Unsigned"); ?></label>
				<label><input type="checkbox" name="BINARY"<?php if ($curextra == " binary") echo " checked"; ?>><?php echo __("Binary"); ?></label>
				<label><input type="checkbox" name="NOTNULL"<?php if ($structureRow['Null'] != "YES") echo " checked"; ?>><?php echo __("Not Null"); ?></label>
				</td>
				</tr>
				
				<?php
				
				$firstField = false;
				
				?>
				
				<tr>
				<td style="padding: 5px 0 15px" colspan="4">
				<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />&nbsp;&nbsp;<a onclick="cancelEdit('editform<?php echo $counter; ?>')"><?php echo __("Cancel"); ?></a>
				</td>
				</tr>
				</table>
				</div>
				</form>
				
				<?php
				
				$counter++;
			}
		}
		
	}

} else if ($file == 'edituser.php') {

	include "functions.php";

	loginCheck();

	$conn->selectDB("mysql");

	if (isset($_POST['editParts'])) {
		$editParts = $_POST['editParts'];
		
		$editParts = explode("; ", $editParts);
		
		$totalParts = count($editParts);
		$counter = 0;
		
		$firstField = true;
		
		foreach ($editParts as $part) {
			$part = trim($part);
			
			if ($part != "" && $part != ";") {
				
				list($user, $host) = explode("@", $part);
				
				$userSQL = $conn->query("SELECT * FROM `user` WHERE `User`='" . $user . "' AND `Host`='" . $host . "'");
				$dbuserSQL = $conn->query("SELECT * FROM `db` WHERE `User`='" . $user . "' AND `Host`='" . $host . "'");
				
				if ($conn->isResultSet($userSQL)) {
					
					$allPrivs = true;
					
					$dbShowList = array();
					
					if ($conn->isResultSet($dbuserSQL)) {
						
						$accessLevel = "LIMITED";
						
						while ($dbuserRow = $conn->fetchAssoc($dbuserSQL)) {
							$selectedPrivs = array();
							
							$dbShowList[] = $dbuserRow['Db'];
										
							foreach ($dbuserRow as $key=>$value) {
								if (substr($key, -5) == "_priv" && $key != "Grant_priv" && $value == "N") {
									$allPrivs = false;
								}
								
								if ($value == "N")
									$selectedPrivs[$key] = $value;
							}
							
							if (isset($thePrivList)) {
								$thePrivList = array_merge($thePrivList, $selectedPrivs);
							} else {
								$thePrivList = $dbuserRow;
							}
						}
					} else {
						$accessLevel = "GLOBAL";
						
						$userRow = $conn->fetchAssoc($userSQL);
						
						foreach ($userRow as $key=>$value) {
							if (substr($key, -5) == "_priv" && $key != "Grant_priv" && $value == "N") {
								$allPrivs = false;
							}
						}
						
						$thePrivList = $userRow;
					}
					
					echo '<form id="editform' . $counter . '" querypart="' . $part . '" onsubmit="saveUserEdit(\'editform' . $counter . '\'); return false;">';
					echo '<div class="edituser inputbox">';
					echo '<div class="errormessage" style="margin: 0 7px 13px; display: none"></div>';
					echo '<table class="edit" cellspacing="0" cellpadding="0">';
					
					?>
					
					<tr>
						<td class="secondaryheader"><?php echo __("User"); ?>:</td>
						<td><strong><?php echo $part; ?></strong></td>
					</tr>
					<tr>
						<td class="secondaryheader"><?php echo __("Change password"); ?>:</td>
						<td><input type="password" class="text" name="NEWPASS" /></td>
					</tr>
					<?php
					
					$dbList = $conn->listDatabases();
					
					if ($conn->isResultSet($dbList)) {
					
					?>
					<tr>
						<td class="secondaryheader"><?php echo __("Allow access to"); ?>:</td>
						<td>
						<label><input type="radio" name="ACCESSLEVEL" value="GLOBAL" id="ACCESSGLOBAL<?php echo $counter; ?>" onchange="updatePane('ACCESSSELECTED<?php echo $counter; ?>', 'dbaccesspane<?php echo $counter; ?>'); updatePane('ACCESSGLOBAL<?php echo $counter; ?>', 'adminprivlist<?php echo $counter; ?>')" onclick="updatePane('ACCESSSELECTED<?php echo $counter; ?>', 'dbaccesspane<?php echo $counter; ?>'); updatePane('ACCESSGLOBAL<?php echo $counter; ?>', 'adminprivlist<?php echo $counter; ?>')"<?php if ($accessLevel == "GLOBAL") echo ' checked="checked"'; ?> /><?php echo __("All databases"); ?></label><br />
						<label><input type="radio" name="ACCESSLEVEL" value="LIMITED" id="ACCESSSELECTED<?php echo $counter; ?>" onchange="updatePane('ACCESSSELECTED<?php echo $counter; ?>', 'dbaccesspane<?php echo $counter; ?>'); updatePane('ACCESSGLOBA<?php echo $counter; ?>L', 'adminprivlist<?php echo $counter; ?>')" onclick="updatePane('ACCESSSELECTED<?php echo $counter; ?>', 'dbaccesspane<?php echo $counter; ?>'); updatePane('ACCESSGLOBAL<?php echo $counter; ?>', 'adminprivlist<?php echo $counter; ?>')"<?php if ($accessLevel == "LIMITED") echo ' checked="checked"'; ?> /><?php echo __("Selected databases"); ?></label>
						
						<div id="dbaccesspane<?php echo $counter; ?>"<?php if ($accessLevel == "GLOBAL") echo ' style="display: none"'; ?> class="privpane">
							<table cellpadding="0">
							<?php
							
							while ($dbRow = $conn->fetchArray($dbList)) {
								echo '<tr>';
								echo '<td>';
								echo '<label><input type="checkbox" name="DBLIST[]" value="' . $dbRow[0] . '"';
								
								if (in_array($dbRow[0], $dbShowList))
									echo ' checked="checked"';
								
								echo ' />' . $dbRow[0] . '</label>';
								echo '</td>';
								echo '</tr>';
							}
							
							?>
							</table>
						</div>
						
						</td>
					</tr>
					<?php
					
					}
					
					?>
					<tr>
						<td class="secondaryheader"><?php echo __("Give user"); ?>:</td>
						<td>
						<label><input type="radio" name="CHOICE" value="ALL" onchange="updatePane('EDITPRIVSELECTED<?php echo $counter; ?>', 'editprivilegepane<?php echo $counter; ?>')" onclick="updatePane('EDITPRIVSELECTED<?php echo $counter; ?>', 'editprivilegepane<?php echo $counter; ?>')" <?php if ($allPrivs) echo 'checked="checked"'; ?> /><?php echo __("All privileges"); ?></label><br />
						<label><input type="radio" name="CHOICE" value="SELECTED" id="EDITPRIVSELECTED<?php echo $counter; ?>" onchange="updatePane('EDITPRIVSELECTED<?php echo $counter; ?>', 'editprivilegepane<?php echo $counter; ?>')" onclick="updatePane('EDITPRIVSELECTED<?php echo $counter; ?>', 'editprivilegepane<?php echo $counter; ?>')" <?php if (!$allPrivs) echo 'checked="checked"'; ?> /><?php echo __("Selected privileges"); ?></label>
						
						<div id="editprivilegepane<?php echo $counter; ?>" class="privpane" <?php if ($allPrivs) echo 'style="display: none"'; ?>>
						<div class="paneheader">
						<?php echo __("User privileges"); ?>
						</div>
						<table cellpadding="0" id="edituserprivs<?php echo $counter; ?>">
						<tr>
							<td width="50%">
							<label><input type="checkbox" name="PRIVILEGES[]" value="SELECT" <?php if (isset($thePrivList['Select_priv']) && $thePrivList['Select_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Select"); ?></label>
							</td>
							<td width="50%">
							<label><input type="checkbox" name="PRIVILEGES[]" value="INSERT" <?php if (isset($thePrivList['Insert_priv']) && $thePrivList['Insert_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Insert"); ?></label>
							</td>
						</tr>
						<tr>
							<td>
							<label><input type="checkbox" name="PRIVILEGES[]" value="UPDATE" <?php if (isset($thePrivList['Update_priv']) && $thePrivList['Update_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Update"); ?></label>
							</td>
							<td>
							<label><input type="checkbox" name="PRIVILEGES[]" value="DELETE" <?php if (isset($thePrivList['Delete_priv']) && $thePrivList['Delete_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Delete"); ?></label>
							</td>
						</tr>
						<tr>
							<td>
							<label><input type="checkbox" name="PRIVILEGES[]" value="INDEX" <?php if (isset($thePrivList['Index_priv']) && $thePrivList['Index_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Index"); ?></label>
							</td>
							<td>
							<label><input type="checkbox" name="PRIVILEGES[]" value="ALTER" <?php if (isset($thePrivList['Alter_priv']) && $thePrivList['Alter_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Alter"); ?></label>
							</td>
						</tr>
						<tr>
							<td>
							<label><input type="checkbox" name="PRIVILEGES[]" value="CREATE" <?php if (isset($thePrivList['Create_priv']) && $thePrivList['Create_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Create"); ?></label>
							</td>
							<td>
							<label><input type="checkbox" name="PRIVILEGES[]" value="DROP" <?php if (isset($thePrivList['Drop_priv']) && $thePrivList['Drop_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Drop"); ?></label>
							</td>
						</tr>
						<tr>
							<td colspan="2">
							<label><input type="checkbox" name="PRIVILEGES[]" value="CREATE TEMPORARY TABLES" <?php if (isset($thePrivList['Create_tmp_table_priv']) && $thePrivList['Create_tmp_table_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Temp tables"); ?></label>
							</td>
						</tr>
						</table>
						<div id="adminprivlist<?php echo $counter; ?>">
						<div class="paneheader">
						<?php echo __("Administrator privileges"); ?>
						</div>
						<table cellpadding="0" id="editadminprivs<?php echo $counter; ?>">
						<tr>
							<td width="50%">
							<label><input type="checkbox" name="PRIVILEGES[]" value="FILE" <?php if (isset($thePrivList['File_priv']) && $thePrivList['File_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("File"); ?></label>
							</td>
							<td width="50%">
							<label><input type="checkbox" name="PRIVILEGES[]" value="PROCESS" <?php if (isset($thePrivList['Process_priv']) && $thePrivList['Process_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Process"); ?></label>
							</td>
						</tr>
						<tr>
							<td>
							<label><input type="checkbox" name="PRIVILEGES[]" value="RELOAD" <?php if (isset($thePrivList['Reload_priv']) && $thePrivList['Reload_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Reload"); ?></label>
							</td>
							<td>
							<label><input type="checkbox" name="PRIVILEGES[]" value="SHUTDOWN" <?php if (isset($thePrivList['Shutdown_priv']) && $thePrivList['Shutdown_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Shutdown"); ?></label>
							</td>
						</tr>
						<tr>
							<td>
							<label><input type="checkbox" name="PRIVILEGES[]" value="SUPER" <?php if (isset($thePrivList['Super_priv']) && $thePrivList['Super_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Super"); ?></label>
							</td>
							<td>
							</td>
						</tr>
						</table>
						</div>
						</div>
						
						</td>
					</tr>
					</table>
					
					<table cellpadding="0">
					<tr>
						<td class="secondaryheader"><?php echo __("Options"); ?>:</td>
						<td>
						<label><input type="checkbox" name="GRANTOPTION" value="true" <?php if ($thePrivList['Grant_priv'] == "Y") echo 'checked="checked"'; ?> /><?php echo __("Grant option"); ?></label>
						</td>
					</tr>
					</table>
					
					<div style="margin-top: 10px; height: 22px; padding: 4px 0">
						<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />&nbsp;&nbsp;<a onclick="cancelEdit('editform<?php echo $counter; ?>')"><?php echo __("Cancel"); ?></a>
					</div>
					</div>
					</form>
				
				<?php
				
				} else {
					echo __("User not found!");
				}
				
				$counter++;
			}
		}
		
	}

} else if ($file == 'export.php') {

	include "functions.php";

	loginCheck();

	if ($_POST) {
		
		$outputBuffer = "";
		
		if (isset($db)) {
			$dbs[] = $db;
			
			if (isset($table))
				$tables[] = $table;
			else if (isset($_POST['EXPORTTABLE']))
				$tables = $_POST['EXPORTTABLE'];
		} else {
			if (isset($_POST['EXPORTDB']))
				$dbs = $_POST['EXPORTDB'];
			$exportDb = true;
		}
		
		if (isset($_POST['FORMAT']))
			$format = strtoupper($_POST['FORMAT']);
		
		if (isset($_POST['STRUCTURE']))
			$exportStructure = $_POST['STRUCTURE'];
		
		if (isset($_POST['DATA']))
			$exportData = $_POST['DATA'];
		
		if (isset($_POST['DELIMITER']))
			$delimiter = $_POST['DELIMITER'];
		
		if (isset($_POST['FIELDNAMES']))
			$printFieldnames = $_POST['FIELDNAMES'];
		
		if (isset($_POST['INSERTTYPE']))
			$insertType = $_POST['INSERTTYPE'];
		
		if (isset($_POST['OUTPUT']))
			$output = $_POST['OUTPUT'];
		
		if (isset($_POST['OUTPUTFILETEXT'])) {
			$outputFile = "exports/" . basename($_POST['OUTPUTFILETEXT']);
		}
		
		if (!isset($delimiter) || $delimiter == "TAB")
			$delimiter = "\t";
		else if ($delimiter == "SEMICOLON")
			$delimiter = ";";
		else if ($delimiter == "SPACE")
			$delimiter = " ";
		else
			$delimiter = ",";
		
		// for the next three - it has to be one or the other
		// this way, if we get fed garbage, just go with a default
		if (!isset($format) || $format != "CSV")
			$format = "SQL";
		
		if (!isset($output) || $output != "FILE" || !isset($outputFile))
			$output = "BROWSER";
		
		if (!isset($insertType) || $insertType != "COMPLETE")
			$insertType = "COMPACT";
		
		if (isset($format) && $format == "SQL" && !isset($exportStructure) && !isset($exportData)) {
			$error = __("You must export either structure, data, or both") . ".";
		} else if (!isset($dbs)) {
			$error = __("Please select the databases that you would like to export") . ".";
		} else if (isset($db) && !isset($tables)) {
			$error = __("Please select the tables that you would like to export") . ".";
		} else {
		
			if ($format == "SQL") {
				
				$version = $conn->getVersion();
				
				$outputBuffer .= "--\r\n";
				
				if ($conn->getAdapter() == "mysql")
					$outputBuffer .= "-- MySQL " . $version . "\r\n";
				else if ($conn->getAdapter() == "sqlite")
					$outputBuffer .= "-- SQLite " . $version . "\r\n";
				
				$outputBuffer .= "-- " . date("r") . "\r\n";
				$outputBuffer .= "--\r\n\r\n";
			}
			
			foreach ($dbs as $d) {
				
				$conn->selectDB($d);
				
				// this checks to see if we are exporting an entire db with all tables
				if (isset($exportDb) && $exportDb == true) {
					
					if ($format == "SQL") {
						
						$outputBuffer .= "CREATE DATABASE `$d`";
						
						if ($conn->hasCharsetSupport())
						{
							$currentChar = "";
							$currentCharSql = $conn->query("SHOW VARIABLES LIKE 'character_set_database'");
							
							if ($conn->isResultSet($currentCharSql)) {
								$currentChar = $conn->result($currentCharSql, 0, "Value");
								
								$outputBuffer .= " DEFAULT CHARSET " . $currentChar;
							}
						}
						
						$outputBuffer .= ";\r\n\r\n";
						
						$outputBuffer .= "USE `$d`;\r\n\r\n";
						
					}
					
					$tableSql = $conn->listTables();
					
					$tables = "";
					
					if ($conn->isResultSet($tableSql)) {
						while ($tableRow = $conn->fetchArray($tableSql)) {
							$tables[] = $tableRow[0];
						}
					}
				}
				
				foreach ($tables as $t) {
					
					if ($format == "SQL") {
						
						if ($conn->getAdapter() == "mysql")
							$structureSQL = $conn->query("SHOW FULL FIELDS FROM `$t`");
						else
							$structureSQL = $conn->describeTable($t);
						
						$tableEngine = "";
						$tableCharset = "";
						
						if (isset($exportStructure)) {
							
							if ($conn->isResultSet($structureSQL)) {
								
								if ($conn->getAdapter() == "mysql") {
									
									$outputBuffer .= "CREATE TABLE `$t` (";
									
									$infoSql = $conn->query("SHOW TABLE STATUS LIKE '$t'");
									
									if ($conn->isResultSet($infoSql) == 1) {
										
										$infoRow = $conn->fetchAssoc($infoSql);
										
										$tableEngine = (array_key_exists("Type", $infoRow)) ? $infoRow['Type'] : $infoRow['Engine'];
										
										if (array_key_exists('Collation', $infoRow) && isset($collationList)) {
											$tableCharset = $collationList[$infoRow['Collation']];
										}
									
									}
									
								} else if ($conn->getAdapter() == "sqlite") {
									
									$outputBuffer .= "CREATE TABLE '$t' (";
								}
								
								$first = true;
								
								if ($conn->getAdapter() == "mysql") {
								
									while ($structureRow = $conn->fetchArray($structureSQL)) {
										
										if (!$first)
											$outputBuffer .= ",";
										
										$outputBuffer .= "\r\n   `" . $structureRow[0] . "` " . $structureRow[1];
										
										if (isset($collationList) && isset($structureRow['Collation']) && $structureRow['Collation'] != "NULL" && !is_null($structureRow['Collation'])) {
											if ($collationList[$structureRow['Collation']] != $tableCharset) {
												$outputBuffer .= " CHARSET " . $collationList[$structureRow['Collation']];
											}
										}
										
										if (isset($structureRow['Null']) && $structureRow['Null'] != "YES")
											$outputBuffer .= " NOT NULL";
										
										if (isset($structureRow['Default']) && $structureRow['Default'] == "CURRENT_TIMESTAMP") {
											$outputBuffer .= " DEFAULT CURRENT_TIMESTAMP";
										} else if (isset($structureRow['Default'])) {
											$outputBuffer .= " DEFAULT '" . $structureRow['Default'] . "'";
										}
										
										if (isset($structureRow['Extra']) && $structureRow['Extra'] != "")
											$outputBuffer .= " " . $structureRow['Extra'];
										
										$first = false;
									}
								
								} else if ($conn->getAdapter() == "sqlite") {
									
									foreach ($structureSQL as $structureRow) {
										
										if (!$first)
											$outputBuffer .= ",";
										
										$outputBuffer .= "\r\n   " . $structureRow[0] . " " . $structureRow[1];
										
										$first = false;
									}
									
								}
								
								// dont forget about the keys
								if ($conn->getAdapter() == "mysql") {
									$keySQL = $conn->query("SHOW INDEX FROM `$t`");
									
									if ($conn->isResultSet($keySQL)) {
										$currentKey = "";
										while ($keyRow = $conn->fetchAssoc($keySQL)) {
											// if this is the start of a key
											if ($keyRow['Key_name'] != $currentKey) {	
												// finish off the last key first, if necessary
												if ($currentKey != "")
													$outputBuffer .= ")";
												
												if ($keyRow['Key_name'] == "PRIMARY")
													$outputBuffer .= ",\r\n   PRIMARY KEY (";
												elseif ($keyRow['Non_unique'] == "0")
													$outputBuffer .= ",\r\n   UNIQUE KEY (";
												else
													$outputBuffer .= ",\r\n   KEY `" . $keyRow['Key_name'] . "` (";
												
												$outputBuffer .= "`" . $keyRow['Column_name'] . "`";
											} else {
												$outputBuffer .= ",`" . $keyRow['Column_name'] . "`";
											}
											
											$currentKey = $keyRow['Key_name'];
										}
										
										if (isset($currentKey) && $currentKey != "")
											$outputBuffer .= ")";
									}
								}
								
								$outputBuffer .= "\r\n)";
								
								if ($conn->getAdapter() == "mysql") {
									if ($tableEngine) {
										$outputBuffer .= ' ENGINE=' . $tableEngine;
									}
									
									if ($tableCharset) {
										$outputBuffer .= ' DEFAULT CHARSET ' . $tableCharset;
									}
								}
								
								$outputBuffer .= ";\r\n\r\n";
							}
						}
						
						if ($conn->getAdapter() == "mysql")
							$structureSQL = $conn->query("SHOW FULL FIELDS FROM `$t`");
						else
							$structureSQL = $conn->describeTable($t);
						
						if (isset($exportData)) {
							
							$columnList = array();
							
							if ($conn->getAdapter() == "mysql") {
								
								$dataSQL = $conn->query("SELECT * FROM `$t`");
								
								// put the column names in an array
								if ($conn->isResultSet($structureSQL)) {
									while ($structureRow = $conn->fetchAssoc($structureSQL)) {
										$columnList[] = $structureRow['Field'];
										$type[] = $structureRow['Type'];
									}
								}
								
								$columnImplosion = implode("`, `", $columnList);
								
								if ($conn->isResultSet($dataSQL)) {
									
									if ($insertType == "COMPACT")
										$outputBuffer .= "INSERT INTO `$t` (`$columnImplosion`) VALUES \r\n";
									
									$firstLine = true;
									
									while ($dataRow = $conn->fetchAssoc($dataSQL)) {
										
										if ($insertType == "COMPLETE") {
											$outputBuffer .= "INSERT INTO `$t` (`$columnImplosion`) VALUES ";
										} else {
											if (!$firstLine)
												$outputBuffer .= ",\r\n";
										}
										
										$outputBuffer .= "(";
										
										$first = true;
										
										for ($i=0; $i<sizeof($columnList); $i++) {
											if (!$first)
												$outputBuffer .= ", ";
											
											$currentData = $dataRow[$columnList[$i]];
											
											if (isset($type) && $currentData && ((isset($binaryDTs) && in_array($type[$i], $binaryDTs)) || stristr($type[$i], "binary") !== false)) {
												$outputBuffer .= "0x" . bin2hex($currentData);
											} else {
												$outputBuffer .= "'" . $conn->escapeString($currentData) . "'";
											}
											
											$first = false;
										}
										
										$outputBuffer .= ")";
										
										if ($insertType == "COMPLETE")
											$outputBuffer .= ";\r\n";
										
										$firstLine = false;
										
									}
									
									if ($insertType == "COMPACT")
										$outputBuffer .= ";\r\n";
									
								} else {
									$outputBuffer .= "-- [" . sprintf(__("Table `%s` is empty"), $t) . "]\r\n";
								}
								
							} else if ($conn->getAdapter() == "sqlite") {
								
								$dataSQL = $conn->query("SELECT * FROM '$t'");
								
								// put the column names in an array
								if ($conn->isResultSet($structureSQL)) {
									foreach ($structureSQL as $structureRow) {
										$columnList[] = $structureRow[0];
										$type[] = $structureRow[1];
									}
								}
								
								$columnImplosion = implode("', '", $columnList);
								
								if ($conn->isResultSet($dataSQL)) {
									
									$firstLine = true;
									
									while ($dataRow = $conn->fetchAssoc($dataSQL)) {
										
										$outputBuffer .= "INSERT INTO '$t' ('$columnImplosion') VALUES (";
										
										$first = true;
										
										for ($i=0; $i<sizeof($columnList); $i++) {
											if (!$first)
												$outputBuffer .= ", ";
											
											$currentData = $dataRow[$columnList[$i]];
											
											$outputBuffer .= "'" . $conn->escapeString($currentData) . "'";
											
											$first = false;
										}
										
										$outputBuffer .= ");\r\n";
										
										$firstLine = false;
										
									}
									
								} else {
									$outputBuffer .= "-- [" . sprintf(__("Table `%s` is empty"), $t) . "]\r\n";
								}
								
							}
						}
						
						$outputBuffer .= "\r\n";
						
					} else if ($format == "CSV") {
						
						if (isset($printFieldnames)) {
							$structureSQL = $conn->describeTable($t);
								
							if ($conn->isResultSet($structureSQL)) {
								$first = true;
								
								if ($conn->getAdapter() == "mysql") {
									
									while ($structureRow = $conn->fetchArray($structureSQL)) {
										if (!$first)
											$outputBuffer .= $delimiter;
										
										$outputBuffer .= "\"" . $structureRow[0] . "\"";
										
										$first = false;
									}
									
								} else if ($conn->getAdapter() == "sqlite") {
									
									foreach ($structureSQL as $structureRow) {
										if (!$first)
											$outputBuffer .= $delimiter;
										
										$outputBuffer .= "\"" . $structureRow[0] . "\"";
										
										$first = false;
									}
									
								}
								
								$outputBuffer .= "\r\n";
							}
						}
						
						if ($conn->getAdapter() == "mysql") {
							$dataSQL = $conn->query("SELECT * FROM `$t`");
						} else if ($conn->getAdapter() == "sqlite") {
							$dataSQL = $conn->query("SELECT * FROM '$t'");
						}
						
						if ($conn->isResultSet($dataSQL)) {
							while ($dataRow = $conn->fetchArray($dataSQL)) {
								$data = array();
								foreach ($dataRow as $each) {
									$data[] = "\"" . formatDataForCSV($each) . "\"";
								}
								
								$dataLine = implode($delimiter, $data);
								
								$outputBuffer .= $dataLine . "\r\n";
							}
						}
						
					}
					
				}
			
			}
			
			$outputBuffer = trim($outputBuffer);
			
			if ($outputBuffer) {
				if ($output == "BROWSER") {
					echo "<div id=\"EXPORTWRAPPER\">";
						echo "<strong>" . __("Results:") . "</strong> [<a onclick=\"$('EXPORTRESULTS').select()\">" . __("Select all") . "</a>]";
						echo "<textarea id=\"EXPORTRESULTS\">$outputBuffer</textarea>";
					echo "</div>";
				} else {
					
					if (!$handle = @fopen($outputFile, "w")) {
						$error = __("The file could not be opened") . ".";
					} else {
						if (fwrite($handle, $outputBuffer) === false) {
							$error = __("Could not write to file") . ".";
						} else {
							echo '<div style="margin: 10px 12px 5px 14px; color: rgb(100, 100, 100)">';
							echo __("Successfully wrote content to file") . '. <a href="' . $outputFile . '">' . __("Download") . '</a><br /><strong>' . __("Note") . ':</strong> ' . __("If this is a public server, you should delete this file from the server after you download it") . '.</div>';
						}
					}
					
					@fclose($handle);
					
				}
			}
			
		}
	}

	if (isset($error)) {
		echo '<div class="errormessage" style="margin: 14px 12px 7px 14px; width: 340px">' . $error . '</div>';
	}

	?>

	<div class="export">
		
		<h4><?php echo __("Export"); ?></h4>
		
		<form id="EXPORTFORM" onsubmit="submitForm('EXPORTFORM'); return false">
		<table cellpadding="0">
		<?php
		
		$showSeperator = false;
		
		if (isset($db) && !isset($table)) {
		
		?>
		<tr>
			<td class="secondaryheader"><?php echo __("Tables"); ?>:<br />&nbsp;<a onclick="selectAll('exportTable')"><?php echo __("All"); ?></a> / <a onclick="selectNone('exportTable')"><?php echo __("None"); ?></a></td>
			<td>
			<select name="EXPORTTABLE[]" id="exportTable" multiple="multiple" size="10">
			<?php
			
			$conn->selectDB($db);
			
			$tableSql = $conn->listTables();
			
			if ($conn->isResultSet($tableSql)) {
				while ($tableRow = $conn->fetchArray($tableSql)) {
					echo '<option value="' . $tableRow[0] . '"';
					
					if (isset($tables) && in_array($tableRow[0], $tables))
						echo ' selected="selected"';
										
					echo '>' . $tableRow[0] . '</option>';
				}
			}
			
			?>
			</select>
			</td>
		</tr>
		<?php
		
		$showSeperator = true;
		
		} else if (!isset($db) && $conn->getAdapter() != "sqlite") {
		?>
		
		<tr>
			<td class="secondaryheader"><?php echo __("Databases"); ?>:<br />&nbsp;<a onclick="selectAll('exportDb')"><?php echo __("All"); ?></a> / <a onclick="selectNone('exportDb')"><?php echo __("None"); ?></a></td>
			<td>
			<select name="EXPORTDB[]" id="exportDb" multiple="multiple" size="10">
			<?php
			
			$dbSql = $conn->listDatabases();
			
			if ($conn->isResultSet($dbSql)) {
				while ($dbRow = $conn->fetchArray($dbSql)) {
					echo '<option value="' . $dbRow[0] . '"';
					
					if (isset($dbs) && in_array($dbRow[0], $dbs))
						echo ' selected="selected"';
					
					echo '>' . $dbRow[0] . '</option>';
				}
			}
			
			?>
			</select>
			</td>
		</tr>
		<?php
		
		$showSeperator = true;
		
		} else if (isset($db) && isset($table)) {
		
		?>
		<tr>
			<td class="secondaryheader"><?php echo __("Format"); ?>:</td>
			<td>
				<label><input type="radio" name="FORMAT" id="SQLTOGGLE" value="SQL" onchange="updatePane('SQLTOGGLE', 'sqlpane', 'csvpane')" onclick="updatePane('SQLTOGGLE', 'sqlpane', 'csvpane')" <?php if ((isset($format) && $format == "SQL")|| !isset($format)) echo 'checked="checked"'; ?> /><?php echo __("SQL"); ?></label><br />
				<label><input type="radio" name="FORMAT" value="CSV" onchange="updatePane('SQLTOGGLE', 'sqlpane', 'csvpane')" onclick="updatePane('SQLTOGGLE', 'sqlpane', 'csvpane')" <?php if (isset($format) && $format == "CSV") echo 'checked="checked"'; ?> /><?php echo __("CSV"); ?></label>
			</td>
		</tr>
		<?php
		
		$showSeperator = true;
		
		}
		
		?>
		</table>
		
		<?php
		
		if ($showSeperator)
			echo '<div class="exportseperator"></div>';
		
		?>
		
		<table cellpadding="0" id="sqlpane"<?php if (isset($format) && $format == "CSV") echo ' style="display: none"'; ?>>
		<tr>
			<td class="secondaryheader"><?php echo __("Export"); ?>:</td>
			<td>
				<label><input type="checkbox" name="STRUCTURE" value="STRUCTURE" <?php if (isset($exportStructure) || !($_POST)) echo 'checked="checked"'; ?> /><?php echo __("Structure"); ?></label><br />
				<label><input type="checkbox" name="DATA" value="DATA" <?php if (isset($exportData) || !($_POST)) echo 'checked="checked"'; ?> /><?php echo __("Data"); ?></label>
			</td>
		</tr>
		<?php
		
		if ($conn->getAdapter() == "mysql") {
		
		?>
		<tr>
			<td class="secondaryheader"><?php echo __("Options"); ?>:</td>
			<td>
				<label><input type="radio" name="INSERTTYPE" value="COMPACT" <?php if ((isset($insertType) && $insertType == "COMPACT") || !isset($insertType)) echo 'checked="checked"'; ?> /><?php echo __("Compact inserts"); ?></label><br />
				<label><input type="radio" name="INSERTTYPE" value="COMPLETE" <?php if (isset($insertType) && $insertType == "COMPLETE") echo 'checked="checked"'; ?> /><?php echo __("Complete inserts"); ?></label>
			</td>
		</tr>
		<?php
		
		}
		
		?>
		</table>
		
		<table cellpadding="0" id="csvpane"<?php if ((isset($format) && $format == "SQL") || !isset($format)) echo ' style="display: none"'; ?>>
		<tr>
			<td class="secondaryheader"><?php echo __("Delimiter"); ?>:</td>
			<td>
				<label><input type="radio" name="DELIMITER" value="COMMA"<?php if (isset($delimiter) && $delimiter == "," || !isset($delimiter)) echo ' checked="checked"'; ?> /><?php echo __("Comma"); ?></label><br />
				<label><input type="radio" name="DELIMITER" value="TAB"<?php if (isset($delimiter) && $delimiter == "\t") echo ' checked="checked"'; ?> /><?php echo __("Tab"); ?></label><br />
				<label><input type="radio" name="DELIMITER" value="SEMICOLON"<?php if (isset($delimiter) && $delimiter == ";") echo ' checked="checked"'; ?> /><?php echo __("Semicolon"); ?></label><br />
				<label><input type="radio" name="DELIMITER" value="SPACE"<?php if (isset($delimiter) && $delimiter == " ") echo ' checked="checked"'; ?> /><?php echo __("Space"); ?></label>
			</td>
		</tr>
		<tr>
			<td class="secondaryheader"><?php echo __("Options"); ?>:</td>
			<td>
				<label><input type="checkbox" name="FIELDNAMES" value="TRUE"<?php if (isset($printFieldnames)) echo ' checked="checked"'; ?> /><?php echo __("Print field names on first line"); ?></label><br />
			</td>
		</tr>
		</table>
		
		<div class="exportseperator"></div>
		
		<table cellpadding="0">
		<tr>
			<td class="message" colspan="2">
			<?php echo __("If you are exporting a large number of rows, it is recommended that you output the results to a text file"); ?>.
			</td>
		</tr>
		<tr>
			<td class="secondaryheader"><?php echo __("Output to"); ?>:</td>
			<td>
				<label><input type="radio" name="OUTPUT" value="BROWSER"<?php if (isset($output) && $output == "BROWSER" || !isset($output)) echo ' checked="checked"'; ?> /><?php echo __("Browser"); ?></label><br />
				<label><input type="radio" name="OUTPUT" id="OUTPUTFILE" value="FILE" onchange="exportFilePrep()"<?php if (isset($output) && $output == "FILE") echo ' checked="checked"'; ?> /><?php echo __("Text file"); ?>:</label><input type="text" class="text" name="OUTPUTFILETEXT" id="OUTPUTFILETEXT" value="<?php if (isset($outputFile)){ echo basename($outputFile); } else if (isset($format) && $format == "CSV") { echo strtolower(__("Export")) . ".csv"; } else { echo strtolower(__("Export")) . ".sql"; } ?>" style="vertical-align: middle; margin-left: 5px" />
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" /></td>
		</tr>
		</table>
		
		</form>
		
	</div>

	<?php

} else if ($file == 'home.php') {

	include "functions.php";

	loginCheck();

	?>
	<table class="hometable">
	<tr>
		<td>
		<h4><?php echo __("Welcome to SQL Buddy!"); ?></h4>
		</td>
	</tr>
	<tr>
		<td style="padding: 0 0 13px 10px">
		
		<?php
		
		$dbVersion = $conn->getVersion();
		
		if ($conn->getAdapter() == "mysql") {	
			
			if (isset($_SESSION['SB_LOGIN_USER']) && $conn->getOptionValue("host")) {
				$message = sprintf(__("You are connected to MySQL %s with the user %s."), $dbVersion, $_SESSION['SB_LOGIN_USER'] . "@" . $conn->getOptionValue("host"));
			}
			
		} else if ($conn->getAdapter() == "sqlite") {
			$message = sprintf(__("You are connected to %s."), "SQLite " . $dbVersion);
		}
		
		echo "<p>" . $message . "</p>";
		
		?>
		
		<table cellspacing="0" cellpadding="0">
		<?php
		
		if (function_exists("curl_init") && ((isset($sbconfig['EnableUpdateCheck']) && $sbconfig['EnableUpdateCheck'] == true) || !isset($sbconfig['EnableUpdateCheck']))) {
			
			//check for a new version
			$crl = curl_init();
			$url = "http://www.sqlbuddy.com/versioncheck2.php";
			curl_setopt($crl, CURLOPT_URL, $url);
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, 5); // 5 seconds
			$content = curl_exec($crl);
			curl_close($crl);
			
			if (strlen($content) > 0) {
				$content = strip_tags($content);
				
				list($version, $notes) = explode("\n", $content, 2);
				
				?>
				<tr>
				<td class="inputfield">
				<?php echo __("Updates"); ?>:
				</td>
				<td>
				<?php
				
				if (version_compare($version, VERSION_NUMBER, ">")) {
					echo '<span style="background-color: rgb(255, 255, 200); padding: 1px 3px 1px 4px">' . __("A new version of SQL Buddy is available!") . '</span> <a href="http://www.sqlbuddy.com/download/dl.php">' . __("Download") . ' &raquo;</a>';
				} else {
					echo __("There are no updates available") . ".";
				}
				
				?>
				</td>
				</tr>
				<?php
				
			}
		}
		
		?>
		<tr>
			<td class="inputfield">
			<?php echo __("Language"); ?>:
			</td>
			<td>
			<?php
			
			if (sizeof($langList) > 0) {
				
				echo '<select id="langSwitcher" onchange="switchLanguage()">';
				
				foreach ($langList as $langCode => $langName) {
					echo '<option value="' . $langCode . '"';
					
					if ($lang == $langCode)
						echo " selected";
					
					echo '>' . $langName . '</option>';
				}
				
				echo '</select>';
				
			}
			
			?>
			</td>
		</tr>
		<tr>
			<td class="inputfield">
			<?php echo __("Theme"); ?>:
			</td>
			<td>
			<select id="themeSwitcher" onchange="switchTheme()">
			<?php
			
			foreach ($themeList as $t => $n) {
				echo '<option value="' . $t . '"';
				
				if ($theme == $t) {
					echo " selected";
				}
				
				echo '>' . $n . '</option>';
			}
			
			?>
			</select>
			</td>
		</tr>
		</table>
		
		</td>
	</tr>
	<tr>
		<td>
		<h4><?php echo __("Getting started"); ?></h4>
		</td>
	</tr>
	<tr>
		<td style="padding: 1px 0 15px 10px">
		
		<ul>
		<li><a href="http://www.sqlbuddy.com/help/"><?php echo __("Help"); ?></a></li>
		<li><a href="http://www.sqlbuddy.com/translations/"><?php echo __("Translations"); ?></a></li>
		<li><a href="http://www.sqlbuddy.com/contact/"><?php echo __("Contact"); ?></a></li>
		</ul>
		
		</td>
	</tr>
	<?php

	if ($conn->getAdapter() != "sqlite") {

	?>
	<tr>
		<td>
		<h4><?php echo __("Create a new database"); ?></h4>
		</td>
	</tr>
	<tr>
		<td style="padding: 0px 0 20px 10px">
		
		<form onsubmit="createDatabase(); return false;">
		<table cellspacing="0" cellpadding="0">
		<tr>
		<td class="inputfield">
			<?php echo __("Name"); ?>:
		</td>
		<td>
			<input type="text" class="text" id="DBNAME">
		</td>
		</tr>
		<?php
		
		if (isset($charsetList)) {
			echo "<tr>";
			echo "<td class=\"inputfield\">";
			echo __("Charset") . ":";
			echo "</td>";
			echo "<td>";
			echo "<select id=\"DBCHARSET\">";
			echo "<option></option>";
			
			$defaultCharSql = $conn->query("SHOW VARIABLES LIKE 'character_set_server'");
			
			if ($conn->isResultSet($defaultCharSql)) {
				$defaultCharset = $conn->result($defaultCharSql, 0, "Value");
			}
			
			foreach ($charsetList as $charset) {
				echo "<option value=\"" . $charset . "\"";
				
				if (isset($defaultCharset) && $charset == $defaultCharset) {
					echo ' selected="selected"';
				}
				
				echo ">" . $charset . "</option>";
			}
			echo "</select>";
			echo "</td>";
			echo "</tr>";
		}
		
		?>
		<tr>
			<td></td>
			<td>
			<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />
			</td>
		</tr>
		</table>
		</form>
		
		</td>
	</tr>
	<?php

	}

	?>
	<tr>
		<td>
		<h4><?php echo __("Did you know..."); ?></h4>
		</td>
	</tr>
	<tr>
		<td style="padding: 0 0 10px 10px;">
		
		<p><?php
		
		$didYouKnow[] = __("There is an easier way to select a large group of items when browsing table rows. Check the first row, hold the shift key, and check the final row. The checkboxes between the two rows will be automatically checked for you.");
		$didYouKnow[] = __("The columns in the browse and query tabs are resizable. Adjust them to as wide or narrow as you like.");
		$didYouKnow[] = __("The login page is based on a default user of root@localhost. By editing config.php, you can change the default user and host to whatever you want.");
		
		$rand = rand(0, count($didYouKnow) - 1);
		
		echo $didYouKnow[$rand];
		
		?></p>
				
		</td>
	</tr>
	<tr>
		<td>
		<h4><?php echo __("Keyboard shortcuts"); ?></h4>
		</td>
	</tr>
	<tr>
		<td style="padding: 4px 0 5px 10px">
		
		<table class="keyboardtable">
		<tr>
			<th><?php echo __("Press this key..."); ?></th>
			<th><?php echo __("...and this will happen"); ?></th>
		</tr>
		<tr>
			<td>a</td>
			<td><?php echo __("select all"); ?></td>
		</tr>
		<tr>
			<td>n</td>
			<td><?php echo __("select none"); ?></td>
		</tr>
		<tr>
			<td>e</td>
			<td><?php echo __("edit selected items"); ?></td>
		</tr>
		<tr>
			<td>d</td>
			<td><?php echo __("delete selected items"); ?></td>
		</tr>
		<tr>
			<td>r</td>
			<td><?php echo __("refresh page"); ?></td>
		</tr>
		<tr>
			<td>q</td>
			<td><?php echo __("load the query tab"); ?></td>
		</tr>
		<tr>
			<td>f</td>
			<td><?php echo __("browse tab - go to first page of results"); ?></td>
		</tr>
		<tr>
			<td>l</td>
			<td><?php echo __("browse tab - go to last page of results"); ?></td>
		</tr>
		<tr>
			<td>g</td>
			<td><?php echo __("browse tab - go to previous page of results"); ?></td>
		</tr>
		<tr>
			<td>h</td>
			<td><?php echo __("browse tab - go to next page of results"); ?></td>
		</tr>
		<tr>
			<td>o</td>
			<td><?php echo __("optimize selected tables"); ?></td>
		</tr>
		</table>
	</tr>
	</table>	

<?php

} else if ($file == 'import.php') {

	include "functions.php";

	loginCheck();

	?>

	<div class="import">
		
		<div id="importMessage" style="display: none; margin-bottom: 11px"></div>
		
		<h4><?php echo __("Import"); ?></h4>
		
		<form id="importForm" onsubmit="startImport()" action="ajaximportfile.php?db=<?php if (isset($db)) echo $db; ?>&table=<?php if (isset($table)) echo $table; ?>&ajaxRequest=1&requestKey=<?php echo $requestKey; ?>" method="post" enctype="multipart/form-data">
		<table cellpadding="0">
		<?php
		
		if (!isset($table)) {
		?>
		<tr>
			<td class="secondaryheader" colspan="2"><?php echo __("Choose a .sql file to import"); ?>.</td>
		</tr>
		<?php
		}
		
		?>
		<tr>
			<td class="secondaryheader"><?php echo __("File"); ?>:</td>
			<td>
				<input type="file" name="INPUTFILE" />
			</td>
		</tr>
		<?php
		
		if (isset($table)) {
		?>
		<tr>
			<td class="secondaryheader"><?php echo __("Format"); ?>:</td>
			<td>
				<label><input type="radio" name="FORMAT" value="SQL" onchange="updatePane('CSVTOGGLE', 'icsvpane')" onclick="updatePane('CSVTOGGLE', 'icsvpane')" checked="checked"' /><?php echo __("SQL"); ?></label><br />
				<label><input type="radio" name="FORMAT" id="CSVTOGGLE" value="CSV" onchange="updatePane('CSVTOGGLE', 'icsvpane')" onclick="updatePane('CSVTOGGLE', 'icsvpane')" /><?php echo __("CSV"); ?></label>
			</td>
		</tr>
		<?php
		}
		
		?>
		</table>
		
		<div class="exportseperator"></div>
		
		<table cellpadding="0" id="icsvpane" style="display: none">
		<tr>
			<td class="secondaryheader"><?php echo __("Options"); ?>:</td>
			<td>
				<label><input type="checkbox" name="IGNOREFIRST" value="TRUE" /><?php echo __("Ignore first line"); ?></label><br />
			</td>
		</tr>
		<tr>
			<td colspan="2"><div class="exportseperator"></div></td>
		</tr>
		</table>
		
		<table cellpadding="0">
		<tr>
			<td colspan="2"><input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" /><span id="importLoad" style="padding-left: 10px; color: rgb(150, 150, 150); display: none;"><?php echo __("Importing..."); ?></span></td>
		</tr>
		</table>
		
		</form>
		
	</div>

	<iframe id="importFrame" name="importFrame" src="about:blank" style="display: none; width: 0; height: 0; line-height: 0"></iframe>

	<?php		

} else if ($file == 'insert.php') {

	include "functions.php";

	loginCheck();

	requireDatabaseAndTableBeDefined();

	if (isset($db))
		$conn->selectDB($db);

	if (isset($table))
		$structureSql = $conn->describeTable($table);

	if ($conn->isResultSet($structureSql) && $conn->getAdapter() == "mysql") {
		while ($structureRow = $conn->fetchAssoc($structureSql)) {
			$types[$structureRow['Field']] = $structureRow['Type'];
		}
		$structureSql = $conn->describeTable($table);
	}

	if ($conn->isResultSet($structureSql) || sizeof($structureSql) > 0) {
		
		if ($_POST) {
			
			$insertFields = "";
			$insertValues = "";
			
			foreach ($_POST as $key=>$value) {
				
				if ($conn->getAdapter() == "sqlite") {
					$insertFields .= $key . ",";
				} else {
					$insertFields .= "`" . $key . "`,";
				}
				
				if (is_array($value)) {
					$value = implode(",", $value);
				}
				
				if (isset($types) && substr($value, 0, 2) == "0x" && isset($binaryDTs) && in_array($types[$key], $binaryDTs)) {
					$insertValues .= $conn->escapeString(urldecode($value)) . ",";
				} else {
					$insertValues .= "'" . $conn->escapeString(urldecode($value)) . "',";
				}
				
			}
			
			$insertFields = substr($insertFields, 0, -1);
			$insertValues = substr($insertValues, 0, -1);
			
			if ($conn->getAdapter() == "sqlite") {
				$insertQuery = "INSERT INTO $table (" . $insertFields . ") VALUES (" . $insertValues . ")";
			} else {
				$insertQuery = "INSERT INTO `$table` (" . $insertFields . ") VALUES (" . $insertValues . ")";
			}
			
			$conn->query($insertQuery) or ($dbError = $conn->error());
			
			$insertId = $conn->insertId();
			
			if (isset($dbError)) {
				echo '<div class="errormessage" style="margin: 6px 12px 10px; width: 350px">' . $dbError . '</div>';
			} else {
				echo '<div class="insertmessage" id="freshmess">';
				echo __("Your data has been inserted into the database.");
				if ($insertId)
					echo ' (Id: ' . $insertId . ')';
				echo '</div>';
				
				?>
				
				<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
				
				clearPanesOnLoad = true;
				yellowFade($('freshmess'));
				
				</script>
				
				<?php
			}
			
		}
		
		?>
		
		<form id="insertform" onsubmit="submitForm('insertform'); return false">
		<table class="insert" cellspacing="0" cellpadding="0">
		<?php
		
		$firstField = true;
		
		if ($conn->getAdapter() == "sqlite") {
		
			if (sizeof($structureSql) > 0) {
				foreach ($structureSql as $column) {
					
					echo '<tr>';
					echo '<td class="fieldheader"><span style="color: steelblue">';
					if (strpos($column[1], "primary key") > 0) echo '<u>';
					echo $column[0];
					if (strpos($column[1], "primary key") > 0) echo '</u>';
					echo "</span> " . $column[1] . '</td>';
					echo "</tr>";
					echo "<tr>";
					echo '<td class="inputarea">';
					
					if (strpos($column[1], "text") !== false) {
						echo '<textarea name="' . $column[0] . '">';
						if (isset($dbError)) {
							echo $_POST[$column[0]];
						}
						echo '</textarea>';
					} else {
						echo '<input type="text"';
						echo ' name="' . $column[0] . '"';
						if (isset($dbError)) {
							echo 'value="' . $_POST[$column[0]] . '"';
						}
						if ($firstField) {
							echo ' id="FIRSTFIELD"';
							$firstField = false;
						}
						echo ' class="text" />';
					}
					
					?>
					
					</td>
					</tr>
					
					<?php
				}
			}
		
		} else if ($conn->getAdapter() == "mysql") {
			
			if ($conn->isResultSet($structureSql)) {
				while ($structureRow = $conn->fetchAssoc($structureSql)) {
					
					preg_match("/^([a-z]+)(.([0-9]+).)?(.*)?$/", $structureRow['Type'], $matches);
					
					$curtype = $matches[1];
					$cursizeQuotes = $matches[2];
					$cursize = $matches[3];
					$curextra = $matches[4];
					
					echo '<tr>';
					echo '<td class="fieldheader"><span style="color: steelblue">';
					if ($structureRow['Key'] == 'PRI') echo '<u>';
					echo $structureRow['Field'];
					if ($structureRow['Key'] == 'PRI') echo '</u>';
					echo "</span> " . $curtype . $cursizeQuotes . ' ' . $structureRow['Extra'] . '</td>';
					echo "</tr>";
					echo "<tr>";
					echo '<td class="inputarea">';
					if ($structureRow['Type'] == "text") {
						echo '<textarea name="' . $structureRow['Field'] . '">';
						if (isset($dbError))
							echo $_POST[$structureRow['Field']];
						echo '</textarea>';
					}
					elseif (substr($structureRow['Type'], 0, 4) == "enum") {
						$trimmed = substr($structureRow['Type'], 6, -2);
						$listOptions = explode("','", $trimmed);
						echo '<select name="' . $structureRow['Field'] . '">';
						echo '<option> - - - - - </option>';
						foreach ($listOptions as $option) {
							echo '<option value="' . $option . '">' . $option . '</option>';
						}
						echo '</select>';
					}
					elseif (substr($structureRow['Type'], 0, 3) == "set") {
						$trimmed = substr($structureRow['Type'], 5, -2);
						$listOptions = explode("','", $trimmed);
						foreach ($listOptions as $option) {
							$id = $option . rand(1, 1000);
							echo '<label for="' . $id . '"><input name="' . $structureRow['Field'] . '[]" value="' . $option . '" id="' . $id . '" type="checkbox">' . $option . '</label><br />';
						}
					} else {
						echo '<input type="text"';
						echo ' name="' . $structureRow['Field'] . '"';
						if (isset($dbError)) {
							echo 'value="' . $_POST[$structureRow['Field']] . '"';
						}
						if ($firstField && $structureRow['Extra'] != "auto_increment") {
							echo ' id="FIRSTFIELD"';
							$firstField = false;
						}
						echo ' class="text" />';
					}
					
					?>
					
					</td>
					</tr>
					
					<?php
				}
			}
			
		}
		
		?>
		<tr>
		<td style="padding-top: 5px; padding-bottom: 4px">
		<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />
		</td>
		</tr>
		</table>
		</form>
		
		<?php
		
		if (!$firstField) {
		?>
			<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
			
			$('FIRSTFIELD').focus();
			
			</script>
		<?php
		}

	} else {
		?>
		
		<div class="errorpage">
		<h4><?php echo __("Oops"); ?></h4>
		<p><?php printf(__('There was a bit of trouble locating the "%s" table.'), $table); ?></p>
		</div>
		
		<?php
	}

} else if ($file == 'query.php') {

	include "functions.php";

	loginCheck();

	if (isset($db))
		$conn->selectDB($db);

	if (isset($_POST['query']))
		$query = $_POST['query'];

	echo '<div style="padding-left: 5px">';

	if (isset($db)) {
		echo '<span style="color: rgb(135, 135, 135)">' . sprintf(__("Run a query on the %s database"), $db) . '.</span>';
	}

	if (isset($query)) {
		$displayQuery = $query;
	} else if (isset($db) && isset($table) && $conn->getAdapter() == "mysql") {
		$displayQuery = "SELECT * FROM `$table` LIMIT 100";
	} else if (isset($db) && isset($table) && $conn->getAdapter() == "sqlite") {
		$displayQuery = "SELECT * FROM '$table' LIMIT 100";
	}

	?>

	<form onsubmit="executeQuery(); return false;">
	<table cellpadding="0" cellspacing="0" style="margin: 2px 0px">
	<tr>
		<td>
		<textarea name="QUERY" id="QUERY"><?php
		
		if (isset($displayQuery))
			echo $displayQuery;
		
		?></textarea>
		</td>
		<td valign="bottom" style="padding-left: 7px">
		<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />
		</td>
	</tr>
	</table>
	</form>

	</div>

	<?php

	if (isset($query)) {
		
		echo '<div style="margin-top: 10px">';
		
		require "includes/browse.php";
		
		echo '</div>';
	}

	?>
	<script type="text/javascript" authkey="<?php echo $requestKey; ?>">

	$('QUERY').focus();

	</script>

	<?php

} else if ($file == 'dboverview.php') {

	include "functions.php";

	loginCheck();

	if (isset($db)) {

	$conn->selectDB($db);

	//run delete queries

	if (isset($_POST['runQuery'])) {
		
		$runQuery = $_POST['runQuery'];
		
		$queryList = splitQueryText($runQuery);
		
		foreach ($queryList as $query) {
			$query = trim($query);
			
			if ($query != "") {
				$conn->query($query) or ($dbError = $conn->error());
				
				// make a list of the tables that were dropped/emptied
				if (substr($query, 0, 10) == "DROP TABLE")
					$droppedList[] = substr($query, 12, -1);
				
				if (substr($query, 0, 10) == "TRUNCATE `")
					$emptiedList[] = substr($query, 10, -1);
				
				if (substr($query, 0, 13) == "DELETE FROM '")
					$emptiedList[] = substr($query, 13, -1);
				
			}
		}
	}

	// if tables were dropped, remove them from the side menu
	if (isset($droppedList) && isset($db)) {
		?>
		
		<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
		
		var targ = $(getSubMenuId('<?php echo $db . "','" . $droppedList[0]; ?>'));
		while (!targ.hasClass("sublist")) {
			targ = targ.parentNode;
		}
		var toRecalculate = targ.id;
		
		<?php
		for ($mn=0; $mn<count($droppedList); $mn++) {
		?>
			$(getSubMenuId('<?php echo $db . "','" . $droppedList[$mn]; ?>')).dispose();
		<?php
		}
		?>
		
		recalculateSubmenuHeight(toRecalculate);
		
		</script>
		
		<?php
	}

	// if tables were emptied, reset their counts in js
	if (isset($emptiedList) && isset($db)) {
		?>
		
		<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
		<?php
		
		for ($mn=0; $mn<count($emptiedList); $mn++) {
			echo "sb.tableRowCounts[\"" . $db . "_" . $emptiedList[$mn] . "\"] = \"0\";\n";
			echo "var sideA = $(getSubMenuId('" . $db . "', '" . $emptiedList[$mn] . "'));\n";
			echo 'var subc = $E(".subcount", sideA);';
			echo "\nsubc.set(\"text\", \"(0)\");\n";
		}
		
		?>
		</script>
		
		<?php
	}


	if (isset($dbError)) {
		echo '<div class="errormessage" style="margin: 6px 12px 10px 7px; width: 550px"><strong>';
		echo __("Error performing operation");
		echo '</strong><p>' . $dbError . '</p></div>';
	}

	?>

	<table cellpadding="0" class="dboverview" width="750" style="margin: 5px 7px 0">
	<tr>
	<td>

	<?php

	$tableSql = $conn->listTables();

	if ($conn->isResultSet($tableSql)) {
		
		echo '<div style="margin-bottom: 15px">';
		
		echo '<table class="browsenav">';
		echo '<tr>';
		echo '<td class="options">';
		
		echo __("Select") . ':&nbsp;&nbsp;<a onclick="checkAll()">' . __("All") . '</a>&nbsp;&nbsp;<a onclick="checkNone()">' . __("None") . '</a>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . __("With selected") . ':&nbsp;&nbsp;<a onclick="emptySelectedTables()">' . __("Empty") . '</a>&nbsp;&nbsp;<a onclick="dropSelectedTables()">' . __("Drop") . '</a>';
		
		if ($conn->getAdapter() == "mysql") {
			echo '&nbsp;&nbsp;<a onclick="optimizeSelectedTables()">' . __("Optimize") . '</a>';
		}
		
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		
		echo '<div class="grid">';
		
		echo '<div class="emptyvoid">&nbsp;</div>';
		
		echo '<div class="gridheader impotent">';
			echo '<div class="gridheaderinner">';
			echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
			echo '<td><div column="1" class="headertitle column1">' . __("Table") . '</div></td>';
			echo '<td><div class="columnresizer"></div></td>';
			echo '<td><div column="2" class="headertitle column2">' . __("Rows") . '</div></td>';
			echo '<td><div class="columnresizer"></div></td>';
			
			if (isset($charsetList) && isset($collationList)) {
				echo '<td><div column="3" class="headertitle column3">' . __("Charset") . '</div></td>';
				echo '<td><div class="columnresizer"></div></td>';
				echo '<td><div column="4" class="headertitle column4">' . __("Overhead") . '</div></td>';
				echo '<td><div class="columnresizer"></div></td>';
			} else if ($conn->getAdapter() == "mysql") {
				echo '<td><div column="3" class="headertitle column3">' . __("Overhead") . '</div></td>';
				echo '<td><div class="columnresizer"></div></td>';
			}
			
			echo '<td><div class="emptyvoid" style="border-right: 0">&nbsp;</div></td>';
			echo '</tr>';
			echo '</table>';
			echo '</div>';
		echo '</div>';
		
		echo '<div class="leftchecks" style="max-height: 400px">';
		
		$m = 0;
		
		while ($tableRow = $conn->fetchArray($tableSql)) {
			echo '<dl class="manip';
			
			if ($m % 2 == 1)
				echo ' alternator';
			else 
				echo ' alternator2';
			
			echo '"><dt><input type="checkbox" class="check' . $m . '" onclick="rowClicked(' . $m++ . ')" querybuilder="' . $tableRow[0] . '" /></dt></dl>';
		}
		
		echo '</div>';
		
		$tableSql = $conn->listTables();
		
		echo '<div class="gridscroll withchecks" style="overflow-x: hidden; max-height: 400px">';
		
		$m = 0;
		
		while ($tableRow = $conn->fetchArray($tableSql)) {
			
			$rowCount = $conn->tableRowCount($tableRow[0]);
			
			if ($conn->getAdapter() == "mysql") {
				$infoSql = $conn->query("SHOW TABLE STATUS LIKE '" . $tableRow[0] . "'");
				$infoRow = $conn->fetchAssoc($infoSql);
				
				$overhead = $infoRow["Data_free"];
				
				$formattedOverhead = "";
				
				if ($overhead > 0)
					$formattedOverhead = memoryFormat($overhead);
			}
			
			echo '<div class="row' . $m . ' browse';
			
			if ($m % 2 == 1) { echo ' alternator'; }
			else 
			{ echo ' alternator2'; }
			
			echo '">';
			echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
			echo '<td><div class="item column1"><div style="float: left; overflow: hidden; width: 185px">' . $tableRow[0] . '</div><img src="http://sqlbuddylite.googlecode.com/svn/tags/r/goto.png" class="goto" onclick="subTabLoad(\'' . $db . '\', \'' . $tableRow[0] . '\')" /></div></td>';
			echo '<td><div class="item column2">' . number_format($rowCount) . '</div></td>';
			
			if (isset($collationList) && array_key_exists("Collation", $infoRow)) {
				echo '<td><div class="item column3">' . $collationList[$infoRow['Collation']] . '</div></td>';
				echo '<td><div class="item column4">' . $formattedOverhead . '</div></td>';
			} else if ($conn->getAdapter() == "mysql") {
				echo '<td><div class="item column4">' . $formattedOverhead . '</div></td>';
			}
			
			echo '</tr>';
			echo '</table>';
			echo '</div>';
			
			$m++;
		}
		
		echo '</div>';
		echo '</div>';
		
		echo '<br />';
		
	}

	if ($conn->getAdapter() != "sqlite") {

	?>

	<div class="inputbox" style="width: 275px; margin-bottom: 15px">
	<h4><?php echo __("Options"); ?></h4>

	<a onclick="confirmDropDatabase()"><?php printf(__("Drop the '%s' database"), $db); ?></a>
	</div>

	<?php

	}


	if (isset($charsetList)) {

	$currentChar = "";
	$currentCharSql = $conn->query("SHOW VARIABLES LIKE 'character_set_database'");

	if ($conn->isResultSet($currentCharSql)) {
		$currentChar = $conn->result($currentCharSql, 0, "Value");
	}

	?>

	<div class="inputbox" style="width: 325px; margin-bottom: 15px">
	<h4><?php echo __("Edit database"); ?></h4>

	<div id="editDatabaseMessage"></div>
	<form onsubmit="editDatabase(); return false">
	<table cellpadding="4">

	<?php

		echo "<tr>";
		echo "<td class=\"secondaryheader\">";
		echo __("Charset") . ":";
		echo "</td>";
		echo "<td class=\"inputarea\">";
		echo "<select id=\"DBRECHARSET\" style=\"width: 145px\">";
		echo "<option></option>";
		foreach ($charsetList as $charset) {
			echo "<option value=\"" . $charset . "\"";
			
			if (isset($currentChar) && $charset == $currentChar)
				echo " selected=\"selected\"";
			
			echo ">" . $charset . "</option>";
		}
		echo "</select>";
		echo "</td>";
		echo '<td align="left" style="padding-left: 10px">';
		echo '<input type="submit" class="inputbutton" value="' . __("Submit") . '" />';
		echo '</td>';
		echo "</tr>";

	?>

	</table>
	</form>
	</div>

	<?php

	}

	?>

	<div id="reporterror" class="errormessage" style="display: none; margin-bottom: 15px"></div>

	<div class="inputbox">
		<h4><?php echo __("Create a new table"); ?></h4>
		
		<form onsubmit="createTable(); return false">
		<table cellpadding="0" style="width: 300px">
		<tr>
			<td class="secondaryheader" style="width: 80px">
			<?php echo __("Name") ?>:
			</td>
			<td>
			<input type="text" class="text" id="TABLENAME" style="width: 150px" />
			</td>
		</tr>
		<?php
		
		if (isset($charsetList)) {
			echo "<tr>";
			echo "<td class=\"secondaryheader\" style=\"width: 60px\">";
			echo __("Charset") . ":";
			echo "</td>";
			echo "<td>";
			echo "<select id=\"TABLECHARSET\" style=\"width: 155px\">";
			echo "<option></option>";
			foreach ($charsetList as $charset) {
				echo "<option value=\"" . $charset . "\"";
				
				if (isset($currentChar) && $charset == $currentChar)
					echo " selected=\"selected\"";
				
				echo ">" . $charset . "</option>";
			}
			echo "</select>";
			echo "</td>";
			echo "</tr>";
		}
		
		?>
		<tr>
			<td style="padding-top: 5px; color: gray" colspan="2">
			<?php echo __("Setup the fields for the table below"); ?>:
			</td>
		</tr>
		</table>
		<div id="fieldlist">
			
			<div class="fieldbox">
			<table cellpadding="0" class="overview">
			<tr>
			<td colspan="4" class="fieldheader">
			<span class="fieldheadertitle">&lt;<?php echo __("New field"); ?>&gt;</span>
			<a class="fieldclose" onclick="removeField(this)"></a>
			</td>
			</tr>
			<?php
			
			if ($conn->getAdapter() == "mysql") {
				
				?>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Name"); ?>:
				</td>
				<td>
				<input type="text" class="text" name="NAME" onkeyup="updateFieldName(this)" />
				</td>
				<td class="secondaryheader" style="padding-left: 5px">
				<?php echo __("Type"); ?>:
				</td>
				<td>
				<select name="TYPE" onchange="updateFieldName(this); toggleValuesLine(this)">
				<?php
				
				foreach ($typeList as $type) {
					echo '<option value="' . $type . '">' . $type . '</option>';
				}
				
				?>
				</select>
				</td>
				</tr>
				<tr class="valueline" style="display: none">
				<td class="secondaryheader">
				<?php echo __("Values"); ?>:
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="VALUES" onkeyup="updateFieldName(this)" />
				</td>
				<td colspan="2" style="color: gray">
				<?php echo __("Enter in the format: ('1','2')"); ?>
				</td>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Size") ?>:
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="SIZE" onkeyup="updateFieldName(this)" />
				</td>
				<td class="secondaryheader" style="padding-left: 5px">
				<?php echo __("Key"); ?>:
				</td>
				<td class="inputarea">
				<select name="KEY" onchange="updateFieldName(this)">
				<option value=""></option>
				<option value="primary"><?php echo __("primary"); ?></option>
				<option value="unique"><?php echo __("unique"); ?></option>
				<option value="index"><?php echo __("index"); ?></option>
				</select>
				</td>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Default") ?>:
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="DEFAULT" onkeyup="updateFieldName(this)" />
				</td>
				<?php
				
				if (isset($charsetList)) {
					echo "<td class=\"secondaryheader\" style=\"padding-left: 5px\">";
					echo __("Charset") . ":";
					echo "</td>";
					echo "<td class=\"inputarea\">";
					echo "<select name=\"CHARSET\" onchange=\"updateFieldName(this)\">";
					echo "<option></option>";
					foreach ($charsetList as $charset) {
						echo "<option value=\"" . $charset . "\">" . $charset . "</option>";
					}
					echo "</select>";
					echo "</td>";
				} else {
					echo "<td></td>";
					echo "<td></td>";
				}
				
				?>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Other"); ?>:
				</td>
				<td colspan="3">
				<label><input type="checkbox" name="UNSIGN" onchange="updateFieldName(this)"><?php echo __("Unsigned"); ?></label>
				<label><input type="checkbox" name="BINARY" onchange="updateFieldName(this)"><?php echo __("Binary"); ?></label>
				<label><input type="checkbox" name="NOTNULL" onchange="updateFieldName(this)"><?php echo __("Not Null"); ?></label>
				<label><input type="checkbox" name="AUTO" onchange="updateFieldName(this)"><?php echo __("Auto Increment"); ?></label>
				</td>
				</tr>
				<?php
				
			} else if ($conn->getAdapter() == "sqlite") {
				
				?>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Name"); ?>:
				</td>
				<td>
				<input type="text" class="text" name="NAME" onkeyup="updateFieldName(this)" />
				</td>
				<td class="secondaryheader" style="padding-left: 5px">
				<?php echo __("Type"); ?>:
				</td>
				<td>
				<select name="TYPE" onchange="updateFieldName(this)">
				<option value="">typeless</option>
				<?php
				
				foreach ($sqliteTypeList as $type) {
					echo '<option value="' . $type . '">' . $type . '</option>';
				}
				
				?>
				</select>
				</td>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Size") ?>:
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="SIZE" onkeyup="updateFieldName(this)" />
				</td>
				<td class="secondaryheader" style="padding-left: 5px">
				<?php echo __("Key"); ?>:
				</td>
				<td class="inputarea">
				<select name="KEY" onchange="updateFieldName(this)">
				<option value=""></option>
				<option value="primary"><?php echo __("primary"); ?></option>
				</select>
				</td>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Default") ?>:
				</td>
				<td class="inputarea">
				<input type="text" class="text" name="DEFAULT" onkeyup="updateFieldName(this)" />
				</td>
				<td></td>
				<td></td>
				</tr>
				<tr>
				<td class="secondaryheader">
				<?php echo __("Other"); ?>:
				</td>
				<td colspan="3">
				<label><input type="checkbox" name="NOTNULL" onchange="updateFieldName(this)"><?php echo __("Not Null"); ?></label>
				<label><input type="checkbox" name="UNIQUE" onchange="updateFieldName(this)"><?php echo __("Unique"); ?></label>
				<?php
				
				// autoincrement supported in SQLite 3+
				if (version_compare($conn->getVersion(), "3.0.0", ">=")) {
				?>
					<label><input type="checkbox" name="AUTO" onchange="updateFieldName(this)"><?php echo __("Auto Increment"); ?></label>
				<?php
				}
				
				?>
				</td>
				</tr>
				<?php
				
			}
			
			?>
			</table>
			</div>
			
		</div>
			
		<table cellpadding="0" width="370" id="fieldcontrols">
		<tr>
		<td style="padding: 5px 0 4px">
		<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />
		</td>
		<td style="padding: 0px 4px 0" align="right" valign="top">
		<a onclick="addTableField()" style="font-size: 11px !important"><?php echo __("Add field"); ?></a><div style="visibility: hidden; height: 0"><input type="submit" /></div>
		</td>
		</tr>
		</table>
		</form>
		
	</div>

	</td>
	</table>

	<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
	setTimeout("startGrid()", 1);
	</script>

	<?php

	}else{
		
		?>
		
		<div class="errorpage">
		<h4><?php echo __("Oops"); ?></h4>
		<p><?php echo __("For some reason, the database parameter was not included with your request."); ?></p>
		</div>
		
		<?php
		exit;
		
	}
	
} else if ($file == 'login.php') {

	include "functions.php";

	$adapter = (isset($sbconfig['DefaultAdapter'])) ? $sbconfig['DefaultAdapter'] : "mysql";
	$host = (isset($sbconfig['DefaultHost'])) ? $sbconfig['DefaultHost'] : "localhost";
	$user = (isset($sbconfig['DefaultUser'])) ? $sbconfig['DefaultUser'] : "root";
	$pass = (isset($sbconfig['DefaultPass'])) ? $sbconfig['DefaultPass'] : "";	

	// SQLite only
	$database = (isset($sbconfig['DefaultDatabase'])) ? $sbconfig['DefaultDatabase'] : "";	

	if ($_POST) {
		if (isset($_POST['ADAPTER']))
			$adapter = $_POST['ADAPTER'];
		
		if (isset($_POST['HOST']))
			$host = $_POST['HOST'];
			
		if (isset($_POST['USER']))
			$user = $_POST['USER'];
			
		if (isset($_POST['PASS']))
			$pass = $_POST['PASS'];
		
		if (isset($_POST['DATABASE']))
			$database = $_POST['DATABASE'];
	}

	if (!in_array($adapter, $adapterList)) {
		$adapter = "mysql";
	}

	if (($adapter != "sqlite" && $host && $user && ($pass || $_POST)) || ($adapter == "sqlite" && $database)) {
		
		if ($adapter == "sqlite") {
			$connString = "sqlite:database=$database";
			$connCheck = new SQL($connString);
			$user = "";
			$pass = "";
		} else {
			$connString = "$adapter:host=$host";
			$connCheck = new SQL($connString, $user, $pass);
		}
		
		if ($connCheck->isConnected()) {
			$_SESSION['SB_LOGIN'] = true;
			$_SESSION['SB_LOGIN_STRING'] = $connString;
			$_SESSION['SB_LOGIN_USER'] = $user;
			$_SESSION['SB_LOGIN_PASS'] = $pass;
			
			$path = $_SERVER["SCRIPT_NAME"];
			$pathSplit = explode("/", $path);
			
			$redirect = "";
			
			for ($i=0; $i<count($pathSplit)-1; $i++) {
				if (trim($pathSplit[$i]) != "")
					$redirect .= "/" . $pathSplit[$i];
			}
			
			if (array_key_exists("HTTPS", $_SERVER) && $_SERVER['HTTPS'] == "on") {
				$protocol = "https://";
			} else {
				$protocol = "http://";
			}
			
			$redirect = $protocol . $_SERVER["HTTP_HOST"] . $redirect . "/";
			
			redirect($redirect);
			exit;
		} else {
			$error = __("There was a problem logging you in.");
		}
	}

	startOutput();

	?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/REC-html40/strict.dtd">

	<html xmlns="http://www.w3.org/1999/xhtml" version="-//W3C//DTD XHTML 1.1//EN" xml:lang="en">
		<head>
			<title>SQL Buddy</title>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
			<link type="text/css" rel="stylesheet" href="core.css" />

			<script type="text/javascript" src="core.js"></script>
		</head>
		<body style="background: none">
		<div id="container">
		<div id="loginform">
			<form name="loginform" method="post">
			<div class="loginspacer">
			<?php
			
			// make sure they aren't using IE below version 7
			
			$ua = $_SERVER['HTTP_USER_AGENT'];
			
			$ie = strstr($ua, 'MSIE') ? true : false;
			$ieVer = $ie ? preg_split('/msie/i', $ua) : false;
			$ieVer = $ieVer ? floatval($ieVer[1]) : false;
			
			// turn into whole number
			$ieVer = (int)($ieVer);
			
			if ($ua && $ie && $ieVer < 7) {
				
				?>
				<table cellpadding="0" id="tb">
				<tr>
				<td class="loginheader"><h3><?php echo __("Unsupported browser"); ?></h3><a href="http://www.sqlbuddy.com/help/" title="Help"><?php echo __("Help!"); ?></a></td>
				</tr>
				<tr>
				<td><?php echo __("We're sorry, but currently only Internet Explorer 7 is supported. It is available as a free download on Microsoft's website. Other free browsers are also supported, including Firefox, Safari, and Opera."); ?></td>
				</tr>
				</table>
				<?php
				
			} else {
				
				?>
				<table cellpadding="0" id="tb">
				<tr>
				<td colspan="2"><div class="loginheader"><h3><strong><?php echo __("Login"); ?></strong></h3><a href="http://www.sqlbuddy.com/help/" title="Help"><?php echo __("Help!"); ?></a></div></td>
				</tr>
				<?php
				if (isset($error)) {
					echo '<tr><td colspan="2"><div class="errormess">' . $error . '</div></td></tr>';
				}
				if (isset($_GET['timeout'])) {
					echo '<tr><td colspan="2"><div class="errormess">' . __("Your session has timed out. Please login again.") . '</div></td></tr>';
				}
				
				if (sizeof($adapterList) > 1) {
				
				?>
				<tr>
				<td class="field"></td>
				<td>
				<select name="ADAPTER" id="ADAPTER" onchange="adapterChange()">
				<?php
				
				if (in_array("mysql", $adapterList)) {
					?>
					<option value="mysql"<?php if ($adapter == "mysql") echo " selected"; ?>><?php echo __("MySQL"); ?></option>
					<?php
				}
				
				if (in_array("sqlite", $adapterList)) {
					?>
					<option value="sqlite"<?php if ($adapter == "sqlite") echo " selected"; ?>><?php echo __("SQLite"); ?></option>
					<?php
				}
				
				?>
				</select>
				</td>
				</tr>
				<?php
				
				}
				
				?>
				</table>
				<table cellpadding="0" id="REGOPTIONS"<?php if ($adapter == "sqlite") echo ' style="display: none"'; ?>>
				<tr>
				<td class="field"><?php echo __("Host"); ?>:</td>
				<td><input type="text" class="text" name="HOST" value="<?php echo $host; ?>" /></td>
				</tr>
				<tr>
				<td class="field"><?php echo __("Username"); ?>:</td>
				<td><input type="text" class="text" name="USER" value="<?php echo $user; ?>" /></td>
				</tr>
				<tr>
				<td class="field"><?php echo __("Password"); ?>:</td>
				<td><input type="password" class="text" name="PASS" id="PASS" /></td>
				</tr>
				</table>
				<table cellpadding="0" id="LITEOPTIONS"<?php if ($adapter == "mysql") echo ' style="display: none"'; ?>>
				<tr>
				<td class="field"><?php echo __("Database"); ?>:</td>
				<td><input type="text" class="text" name="DATABASE" id="DATABASE" value="<?php echo $database; ?>" /></td>
				</tr>
				</table>
				<table cellpadding="0">
				<tr>
				<td class="field"></td>
				<td><input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" /></td>
				</tr>
				</table>
				<?php
				
			}
			
			?>
			</div>
			</form>
		</div>
		</div>
		<script type="text/javascript">
		<!--
		
		<?php
		
		if ($adapter != "sqlite") {
			echo "$('PASS').focus();";
		} else {
			echo "$('DATABASE').focus();";
		}
		
		?>
		
		if (!navigator.cookieEnabled) {
			var tb = $('tb');
			var newTr = new Element('tr');
			var newTd = new Element('td');
			newTd.setAttribute("colspan", 2);
			var newDiv = new Element('div');
			newDiv.className = "errormess";
			newDiv.set('text', "<?php echo __("You don't appear to have cookies enabled. For sessions to work, most php installations require cookies."); ?>");
			newTd.appendChild(newDiv);
			newTr.appendChild(newTd);
			tb.appendChild(newTr);
		}
		
		function adapterChange() {
			var adapter = $('ADAPTER');
			var currentAdapter = adapter.options[adapter.selectedIndex].value;
			
			if (currentAdapter == "sqlite") {
				$('REGOPTIONS').style.display = 'none';
				$('LITEOPTIONS').style.display = '';
				$('DATABASE').focus();
			} else {
				$('REGOPTIONS').style.display = '';
				$('LITEOPTIONS').style.display = 'none';
				$('PASS').focus();
			}
			
		}
		
		// -->
		</script>
	</body>
	</html>

	<?php
	
} else if ($file == 'logout.php') {

	if (!session_id())
		session_start();

	if (isset($_SESSION['SB_LOGIN'])) {
		$_SESSION['SB_LOGIN'] = null;
		unset($GLOBALS['_SESSION']['SB_LOGIN']);
	}

	if (isset($_SESSION['SB_LOGIN_STRING'])) {
		$_SESSION['SB_LOGIN_STRING'] = null;
		unset($GLOBALS['_SESSION']['SB_LOGIN_STRING']);
	}

	if (isset($_SESSION['SB_LOGIN_USER'])) {
		$_SESSION['SB_LOGIN_USER'] = null;
		unset($GLOBALS['_SESSION']['SB_LOGIN_USER']);
	}

	if (isset($_SESSION['SB_LOGIN_PASS'])) {
		$_SESSION['SB_LOGIN_PASS'] = null;
		unset($GLOBALS['_SESSION']['SB_LOGIN_PASS']);
	}

	header("Location: login.php");

} else if ($file == 'structure.php') {

	include "functions.php";

	loginCheck();

	requireDatabaseAndTableBeDefined();

	if (isset($db))
		$conn->selectDB($db);

	if (isset($_POST)) {
		
		// process form - add index
		if (isset($_POST['INDEXTYPE']))
			$indexType = $_POST['INDEXTYPE'];
		
		if (isset($_POST['INDEXCOLUMNLIST']))
			$indexColumnList = $_POST['INDEXCOLUMNLIST'];
		
		if (isset($indexType) && isset($indexColumnList) && $indexType && $indexColumnList) {
			$indexColumnList = implode("`, `", $indexColumnList);
			
			$indexQuery = "ALTER TABLE `$table` ADD ";
			
			 if ($indexType == "INDEX")
				$indexQuery .= "INDEX";
			else if ($indexType == "UNIQUE")
				$indexQuery .= "UNIQUE";
			
			$indexQuery .= " (`" . $indexColumnList . "`)";
			
			$conn->query($indexQuery) or ($dbError = $conn->error());
		}
		
		?>
		
		<script type="text/javascript" authkey="<?php echo $requestKey; ?>"	>
		clearPanesOnLoad = true;
		</script>
		
		<?php
		
	}

	//run delete queries
	if (isset($_POST['runQuery'])) {
		$runQuery = $_POST['runQuery'];
		
		$queryList = splitQueryText($runQuery);
		foreach ($queryList as $query) {
			if (trim($query) != "")
				$conn->query($query) or ($dbError = $conn->error());
		}
	}

	if (isset($dbError)) {
		echo '<div class="errormessage" style="margin: 6px 12px 10px; width: 602px"><strong>' . __("Error performing operation") . '</strong><p>' . $dbError . '</p></div>';
	}

	$structureSql = $conn->describeTable($table);

	if ($conn->getAdapter() == "mysql" && $conn->isResultSet($structureSql)) {

	?>

	<table cellpadding="0" width="100%" class="structure" style="margin: 2px 7px 7px">
	<tr>
	<td valign="top" width="575">
		
		<table class="browsenav">
		<tr>
		<td class="options">
		
		<?php
		
		echo '<strong>' . __("Columns") . '</strong>&nbsp;&nbsp;&nbsp;&nbsp;';
		
		echo __("Select") . ':&nbsp;&nbsp;<a onclick="checkAll()">' . __("All") . '</a>&nbsp;&nbsp;<a onclick="checkNone()">' . __("None") . '</a>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . __("With selected") . ':&nbsp;&nbsp;<a onclick="editSelectedRows()">' . __("Edit") . '</a>&nbsp;&nbsp;<a onclick="deleteSelectedColumns()">' . __("Delete") . '</a>';
		
		?>
		
		</td>
		</tr>
		</table>
		
		<?php
		
		echo '<div class="grid">';
		
		echo '<div class="emptyvoid">&nbsp;</div>';
		
		echo '<div class="gridheader impotent">';
		echo '<div class="gridheaderinner">';
		echo '<table cellpadding="0" cellspacing="0">';
		echo '<tr>';
		echo '<td><div column="1" class="headertitle column1">' . __("Name") . '</div></td>';
		echo '<td><div class="columnresizer"></div></td>';
		echo '<td><div column="2" class="headertitle column2">' . __("Type") . '</div></td>';
		echo '<td><div class="columnresizer"></div></td>';
		echo '<td><div column="3" class="headertitle column3">' . __("Default") . '</div></td>';
		echo '<td><div class="columnresizer"></div></td>';
		echo '</tr>';
		echo '</table>';
		echo '</div>';
		echo '</div>';
		
		echo '<div class="leftchecks" style="max-height: 300px">';
		
		$m = 0;
		
		while ($structureRow = $conn->fetchAssoc($structureSql)) {
			echo '<dl class="manip';
			
			if ($m % 2 == 1)
				echo ' alternator';
			else 
				echo ' alternator2';
			
			echo '"><dt><input type="checkbox" class="check' . $m . '" onclick="rowClicked(' . $m++ . ')" querybuilder="' . $structureRow['Field'] . '" /></dt></dl>';
		}
		
		echo '</div>';
		
		$structureSql = $conn->describeTable($table);
		
		echo '<div class="gridscroll withchecks" style="overflow-x: hidden; max-height: 300px">';
		
		$m = 0;
		
		while ($structureRow = $conn->fetchAssoc($structureSql)) {
			
			echo '<div class="row' . $m . ' browse';
			
			if ($m % 2 == 1) { echo ' alternator'; }
			else 
			{ echo ' alternator2'; }
			
			echo '">';
			echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
			echo '<td><div class="item column1">' . $structureRow['Field'] . '</div></td>';
			echo '<td><div class="item column2">' . $structureRow['Type'] . '</div></td>';
			echo '<td><div class="item column3">' . $structureRow['Default'] . '</div></td>';
			echo '</tr>';
			echo '</table>';
			echo '</div>';
			
			$fieldList[] = $structureRow['Field'];
			
			$m++;
		}
		
		echo '</div>';
		echo '</div>';
		
		?>

		<div id="newfield" class="inputbox">
			<h4><?php echo __("Add a column"); ?></h4>
		
			<form onsubmit="submitAddColumn(); return false">
			<table cellpadding="5">
			<tr>
			<td class="secondaryheader">
			<?php echo __("Name"); ?>:
			</td>
			<td>
			<input type="text" class="text" name="NAME" style="width: 145px" />
			</td>
			<td class="secondaryheader">
			<?php echo __("Type"); ?>:
			</td>
			<td>
			<select name="TYPE" onchange="toggleValuesLine(this, 'newfield')" style="width: 145px">
			<?php
			
			foreach ($typeList as $type) {
				echo '<option value="' . $type . '">' . $type . '</option>';
			}
			
			?>
			</select>
			</td>
			</tr>
			<tr class="valueline" style="display: none">
			<td class="secondaryheader">
			<?php echo __("Values"); ?>:
			</td>
			<td colspan="3" class="inputarea">
			<input type="text" class="text" name="VALUES" style="width: 145px" />
			</td>
			</tr>
			<tr>
			<td class="secondaryheader">
			<?php echo __("Size"); ?>:
			</td>
			<td>
			<input type="text" class="text" name="SIZE" style="width: 145px" />
			</td>
			<td class="secondaryheader">
			<?php echo __("Key"); ?>:
			</td>
			<td>
			<select name="KEY" style="width: 145px">
			<option value=""> - - - - </option>
			<option value="primary"><?php echo __("primary"); ?></option>
			<option value="unique"><?php echo __("unique"); ?></option>
			<option value="index"><?php echo __("index"); ?></option>
			</select>
			</td>
			</tr>
			<tr>
			<td class="secondaryheader">
			<?php echo __("Default"); ?>:
			</td>
			<td>
			<input type="text" class="text" name="DEFAULT" style="width: 145px" />
			</td>
			<?php
			
			if (isset($charsetList)) {
				echo "<td class=\"secondaryheader charsetToggle\">";
				echo __("Charset") . ":";
				echo "</td>";
				echo "<td class=\"inputarea charsetToggle\">";
				echo "<select name=\"CHARSET\" style=\"width: 145px\">";
				echo "<option></option>";
				foreach ($charsetList as $charset) {
					echo "<option value=\"" . $charset . "\">" . $charset . "</option>";
				}
				echo "</select>";
				echo "</td>";
			} else {
				echo "<td></td>";
				echo "<td></td>";
			}
			
			?>
			</tr>
			<tr>
			<td class="secondaryheader">
			<?php echo __("Other"); ?>:
			</td>
			<td colspan="3">
			<label><input type="checkbox" name="UNSIGN"><?php echo __("Unsigned"); ?></label>
			<label><input type="checkbox" name="BINARY"><?php echo __("Binary"); ?></label>
			<label><input type="checkbox" name="NOTNULL"><?php echo __("Not Null"); ?></label>
			<label><input type="checkbox" name="AUTO"><?php echo __("Auto Increment"); ?></label>
			</td>
			</tr>
			<tr>
			<td class="secondaryheader" colspan="3">
			<?php echo __("Insert this column"); ?>:&nbsp;&nbsp;<select id="INSERTPOS">
			<option value=""><?php echo __("At end of table"); ?></option>
			<option value=" FIRST"><?php echo __("At beginning of table"); ?></option>
			<option value=""> - - - - - - - - </option>
			<?php
			for ($i=0; $i<count($fieldList); $i++) {	
				echo '<option value=" AFTER ' . $fieldList[$i] . '">' . __("After") . ' ' . $fieldList[$i] . '</option>';
			}
			?>
			</select>
			</td>
			<td align="right" style="padding-right: 30px">
			<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />
			</td>
			</tr>
			</table>
			</form>
		</div>
		
		<div class="inputbox" style="width: 235px">
		<h4><?php echo __("Edit table"); ?></h4>
		
		<div id="editTableMessage"></div>
		<form onsubmit="editTable(); return false">
		<table cellpadding="0">
		<tr>
		<td class="secondaryheader">
		<?php echo __("Name"); ?>:
		</td>
		<td class="inputarea">
		<input type="text" class="text" name="RENAME" id="RENAME" value="<?php echo $table; ?>" style="width: 140px" />
		</td>
		</tr>
		<?php
		
		if (isset($charsetList) && isset($collationList)) {
			
			$infoSql = $conn->query("SHOW TABLE STATUS LIKE '$table'");
			
			if ($conn->isResultSet($infoSql) == 1) {
			
			$infoRow = $conn->fetchAssoc($infoSql);
			
			echo "<tr>";
			echo "<td class=\"secondaryheader\">";
			echo __("Charset") . ":";
			echo "</td>";
			echo "<td class=\"inputarea\">";
			echo "<select name=\"CHARSET\" id=\"RECHARSET\" style=\"width: 145px\">";
			echo "<option></option>";
			foreach ($charsetList as $charset) {
				echo "<option value=\"" . $charset . "\"";
				
				if ($collationList[$infoRow['Collation']] == $charset) {
					echo ' selected="selected"';
				}
				
				echo ">" . $charset . "</option>";
			}
			echo "</select>";
			echo "</td>";
			echo "</tr>";
			
			}
		}
		
		echo '<tr>';
		echo '<td></td>';
		echo '<td align="left">';
		echo '<input type="submit" class="inputbutton" value="' . __("Submit") . '" />';
		echo '</td>';
		echo '</tr>';
		
		?>
		</table>
		</form>
		</div>
		
		<?php
		
		$indexListSQL = $conn->query("SHOW INDEX FROM `$table`");
		
		if ($conn->isResultSet($indexListSQL)) {
			
			?>
			
			<div style="width: 440px">
			
			<table class="browsenav" style="margin-top: 15px">
			<tr>
			<td class="options">
			
			<?php
			
			echo '<strong>' . __("Indexes") . '</strong>&nbsp;&nbsp;&nbsp;&nbsp;';
			
			echo __("Select") . ':&nbsp;&nbsp;<a onclick="checkAll(\'grid2\')">' . __("All") . '</a>&nbsp;&nbsp;<a onclick="checkNone(\'grid2\')">' . __("None") . '</a>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . __("With selected") . ':&nbsp;&nbsp;<a onclick="deleteSelectedIndexes(\'grid2\')">' . __("Delete") . '</a>';
			
			?>
			
			</td>
			</tr>
			</table>
			
			<?php
			
			$indexList = array();
			
			while ($indexListRow = $conn->fetchAssoc($indexListSQL)) {	
				if (!array_key_exists($indexListRow['Key_name'], $indexList)) {
					$indexList[$indexListRow['Key_name']] = $indexListRow['Column_name'];
				} else {
					$indexList[$indexListRow['Key_name']] .= ", " . $indexListRow['Column_name'];
				}
			}
			
			echo '<div class="grid" id="grid2">';
		
			echo '<div class="emptyvoid">&nbsp;</div>';
			
			echo '<div class="gridheader impotent">';
			echo '<div class="gridheaderinner">';
			echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
			echo '<td><div column="1" class="headertitle column1">' . __("Key") . '</div></td>';
			echo '<td><div class="columnresizer"></div></td>';
			echo '<td><div column="2" class="headertitle column2">' . __("Columns") . '</div></td>';
			echo '<td><div class="columnresizer"></div></td>';
			echo '<td><div class="emptyvoid" style="width: 15px; border-right: 0"></div></td>';
			echo '</tr>';
			echo '</table>';
			echo '</div>';
			echo '</div>';
			
			echo '<div class="leftchecks" style="max-height: 400px">';
			
			$m = 0;
			
			foreach ($indexList as $keyName => $columns) {
				echo '<dl class="manip';
				
				if ($m % 2 == 1)
					echo ' alternator';
				else 
					echo ' alternator2';
				
				echo '"><dt><input type="checkbox" class="check' . $m . '" onclick="rowClicked(' . $m++ . ', \'grid2\')" querybuilder="' . $keyName . '" /></dt></dl>';
			}
			
			echo '</div>';
			
			echo '<div class="gridscroll withchecks" style="overflow-x: hidden; max-height: 400px">';
			
			$m = 0;
			
			foreach ($indexList as $keyName => $columns) {
				echo '<div class="row' . $m . ' browse';
				
				if ($m % 2 == 1) { echo ' alternator'; }
				else 
				{ echo ' alternator2'; }
				
				echo '">';
				echo '<table cellpadding="0" cellspacing="0">';
				echo '<tr>';
				echo '<td><div class="item column1">' . $keyName . '</div></td>';
				echo '<td><div class="item column2">' . $columns . '</div></td>';
				echo '</tr>';
				echo '</table>';
				echo '</div>';
				
				
				$m++;
			}
			
			echo '</div>';
			echo '</div>';
			
			$m++;
			
		}
		
		?>
			
		<div id="newindex" class="inputbox" style="width: 275px">
		<h4><?php echo __("Add an index"); ?></h4>
		<div class="universalindent">
			<form id="ADDINDEXFORM" onsubmit="submitForm('ADDINDEXFORM'); return false">
			<table cellpadding="4">
			<tr>
				<td class="secondaryheader">
				<?php echo __("Type"); ?>:
				</td>
				<td class="inputarea" valign="top">
				<select name="INDEXTYPE" style="width: 115px">
				<option value="INDEX"><?php echo __("Index"); ?></option>
				<option value="UNIQUE"><?php echo __("Unique"); ?></option>
				</select>
				</td>
			</tr>
			<tr>
				<td class="secondaryheader" style="width: 70px">
				<?php echo __("Column(s)"); ?>:
				</td>
				<td class="inputarea" valign="top">
				<?php
				
				$finish = (count($fieldList) < 5) ? count($fieldList) : 5;
				
				for ($i=0; $i<$finish; $i++) {	
					echo '<label><input type="checkbox" name="INDEXCOLUMNLIST[]" value="' . $fieldList[$i] . '">' . $fieldList[$i] . '</label><br />';
				}
				
				if (count($fieldList) > 5) {
					echo '<a onclick="show(\'columnListFull\'); hide(\'columnListLink\'); return false;" id="columnListLink">' . sprintf(__("Show %d more..."), count($fieldList) - 5) . '</a>';
					echo '<div id="columnListFull" style="display: none">';
					for ($i=5; $i<count($fieldList); $i++) {	
						echo '<label><input type="checkbox" name="INDEXCOLUMNLIST[]" value="' . $fieldList[$i] . '">' . $fieldList[$i] . '</label><br />';
					}
					echo '</div>';
				}
				
				?>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
				<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />
				</td>
			</tr>
			</table>
			</form>
		</div>
		</div>
		
	</td>
	<td valign="top">
		<div style="margin: 4px 0 0 20px; padding-left: 15px; border-left: 1px solid rgb(215, 215, 215)">
		
		<h3><?php echo __("Options"); ?></h3>
		
		<div style="padding: 2px 0 15px">
			<a onclick="confirmEmptyTable()"><?php echo __("Empty table"); ?></a><br />
			<a onclick="confirmDropTable()"><?php echo __("Drop table"); ?></a><br />
			<a onclick="optimizeTable()"><?php echo __("Optimize table"); ?></a>
		</div>
		
		<?php
		
		$infoSql = $conn->query("SHOW TABLE STATUS LIKE '$table'");
		
		if ($conn->isResultSet($infoSql) == 1) {
		
		$infoRow = $conn->fetchAssoc($infoSql);
		
		?>
		
		<h3><?php echo __("Table Information"); ?></h3>
		<dl class="information">
		<?php
		
		$engine = (array_key_exists("Type", $infoRow)) ? $infoRow['Type'] : $infoRow['Engine'];
		
		echo '<dt>' . __("Storage engine") . ':</dt><dd>' . $engine . '</dd>';
		
		if (array_key_exists('Collation', $infoRow) && isset($collationList)) {
			echo '<dt>' . ("Charset") . ':</dt><dd>' . $collationList[$infoRow['Collation']] . '</dd>';
		}
		
		echo '<dt>' . __("Rows") . ':</dt><dd>' . number_format($infoRow['Rows']) . '</dd>';
		echo '<dt>' . __("Size") . ':</dt><dd>' . memoryFormat($infoRow['Data_length']) . '</dd>';
		echo '<dt>' . __("Overhead") . ':</dt><dd>' . memoryFormat($infoRow['Data_free']) . '</dd>';
		echo '<dt>' . __("Auto Increment") . ':</dt><dd>' . number_format($infoRow['Auto_increment']) . '</dd>';
		
		?>
		</dl>
		<div class="clearer"></div>
		
		<?php
		
		}
		
		?>
		
		<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
		setTimeout("startGrid()", 1);
		</script>
		
		</div>
	</td>
	</tr>
	</table>

	<?php

	} else if ($conn->getAdapter() == "sqlite" && sizeof($structureSql) > 0) {

	?>
	<table cellpadding="0" width="100%" class="structure" style="margin: 2px 7px 7px">
	<tr>
	<td valign="top" width="575">
		
		<table class="browsenav">
		<tr>
		<td class="options">
		
		<?php
		
		echo '<strong>' . __("Columns") . '</strong>';
			
		?>
		
		</td>
		</tr>
		</table>
		
		<?php
		
		echo '<div class="grid">';
		
		echo '<div class="gridheader impotent">';
		echo '<div class="gridheaderinner">';
		echo '<table cellpadding="0" cellspacing="0">';
		echo '<tr>';
		echo '<td><div column="1" class="headertitle column1">' . __("Name") . '</div></td>';
		echo '<td><div class="columnresizer"></div></td>';
		echo '<td><div column="2" class="headertitle column2">' . __("Type") . '</div></td>';
		echo '<td><div class="columnresizer"></div></td>';
		echo '</tr>';
		echo '</table>';
		echo '</div>';
		echo '</div>';
		
		echo '<div class="gridscroll" style="overflow-x: hidden; max-height: 300px">';
		
		$m = 0;
		
		foreach ($structureSql as $column) {
			
			echo '<div class="row' . $m . ' browse';
			
			if ($m % 2 == 1) { echo ' alternator'; }
			else 
			{ echo ' alternator2'; }
			
			echo '">';
			echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
			echo '<td><div class="item column1">' . $column[0] . '</div></td>';
			echo '<td><div class="item column2">' . $column[1] . '</div></td>';
			echo '</tr>';
			echo '</table>';
			echo '</div>';
			
			$fieldList[] = $column[0];
			
			$m++;
		}
		
		echo '</div>';
		echo '</div>';
		
		if (version_compare($conn->getVersion(), "3.1.3", ">")) {
		
		?>

		<div id="newfield" class="inputbox">
			<h4><?php echo __("Add a column"); ?></h4>
		
			<form onsubmit="submitAddColumn(); return false">
			<table cellpadding="5">
			<tr>
			<td class="secondaryheader">
			<?php echo __("Name"); ?>:
			</td>
			<td>
			<input type="text" class="text" name="NAME" style="width: 145px" />
			</td>
			<td class="secondaryheader">
			<?php echo __("Type"); ?>:
			</td>
			<td>
			<select name="TYPE" style="width: 150px">
			<option value="">typeless</option>
			<?php
			
			foreach ($sqliteTypeList as $type) {
				echo '<option value="' . $type . '">' . $type . '</option>';
			}
			
			?>
			</select>
			</td>
			</tr>
			<tr>
			<td class="secondaryheader">
			<?php echo __("Size"); ?>:
			</td>
			<td>
			<input type="text" class="text" name="SIZE" style="width: 145px" />
			</td>
			<td class="secondaryheader">
			<?php echo __("Default"); ?>:
			</td>
			<td>
			<input type="text" class="text" name="DEFAULT" style="width: 145px" />
			</td>
			</tr>
			<tr>
			<td class="secondaryheader">
			<?php echo __("Other"); ?>:
			</td>
			<td colspan="3">
			<label><input type="checkbox" name="NOTNULL"><?php echo __("Not Null"); ?></label>
			<label><input type="checkbox" name="UNIQUE"><?php echo __("Unique"); ?></label>
			</td>
			</tr>
			<tr>
			<td colspan="4" align="right" style="padding-right: 30px">
			<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />
			</td>
			</tr>
			</table>
			</form>
		</div>
		
		<div class="inputbox" style="width: 235px">
		<h4><?php echo __("Edit table"); ?></h4>
		
		<div id="editTableMessage"></div>
		<form onsubmit="editTable(); return false">
		<table cellpadding="0">
		<tr>
		<td class="secondaryheader">
		<?php echo __("Name"); ?>:
		</td>
		<td class="inputarea">
		<input type="text" class="text" name="RENAME" id="RENAME" value="<?php echo $table; ?>" style="width: 140px" />
		</td>
		</tr>
		<tr>
		<td></td>
		<td align="left">
		<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />
		</td>
		</tr>
		</table>
		</form>
		</div>
		
		<?php
		
		}
		
		?>

	</td>	
	<td valign="top">
		<div style="margin: 4px 0 0 20px; padding-left: 15px; border-left: 1px solid rgb(215, 215, 215)">
		
		<h3><?php echo __("Options"); ?></h3>
		
		<div style="padding: 2px 0 15px">
			<a onclick="confirmEmptyTable()"><?php echo __("Empty table"); ?></a><br />
			<a onclick="confirmDropTable()"><?php echo __("Drop table"); ?></a><br />
		</div>
		
		<?php
		
		$rowCount = $conn->tableRowCount($table);
		
		?>
		
		<h3><?php echo __("Table Information"); ?></h3>
		<dl class="information">
		<?php
		
		echo '<dt>' . __("Rows") . ':</dt><dd>' . number_format($rowCount) . '</dd>';
			
		?>
		</dl>
		<div class="clearer"></div>
		
		<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
		setTimeout("startGrid()", 1);
		</script>
		
		</div>
	</td>
	</tr>
	</table>

	<?php

	} else {
		?>
		
		<div class="errorpage">
		<h4><?php echo __("Oops"); ?></h4>
		<p><?php printf(__('There was a bit of trouble locating the "%s" table.'), $table); ?></p>
		</div>
		
		<?php
	}	




} else if ($file == 'users.php') {

	include "functions.php";

	loginCheck();

	function removeAdminPrivs($priv) {
		if ($priv == "FILE" || $priv == "PROCESS" ||  $priv == "RELOAD" ||  $priv == "SHUTDOWN" ||  $priv == "SUPER")
			return false;
		else
			return true;
	}

	if ($_POST) {
		
		if (isset($_POST['NEWHOST']))
			$newHost = $_POST['NEWHOST'];
		else
			$newHost = "localhost";
		
		if (isset($_POST['NEWNAME']))
			$newName = $_POST['NEWNAME'];
		
		if (isset($_POST['NEWPASS']))
			$newPass = $_POST['NEWPASS'];
		
		if (isset($_POST['ACCESSLEVEL']))
			$accessLevel = $_POST['ACCESSLEVEL'];
		else
			$accessLevel = "GLOBAL";
		
		if ($accessLevel != "LIMITED")
			$accessLevel = "GLOBAL";
		
		if (isset($_POST['DBLIST']))
			$dbList = $_POST['DBLIST'];
		else
			$dbList = array();
		
		if (isset($_POST['NEWCHOICE']))
			$newChoice = $_POST['NEWCHOICE'];
		
		if (isset($_POST['NEWPRIVILEGES']))
			$newPrivileges = $_POST['NEWPRIVILEGES'];
		
		if (isset($newName) && ($accessLevel == "GLOBAL" || ($accessLevel == "LIMITED" && sizeof($dbList) > 0))) {
			
			if ($newChoice == "ALL") {
				$privList = "ALL";
			} else {
				
				if (sizeof($newPrivileges) > 0) {
					if ($accessLevel == "LIMITED") {
						$newPrivileges = array_filter($newPrivileges, "removeAdminPrivs");
					}
					
					$privList = implode(", ", $newPrivileges);
					
				} else {
					$privList = "USAGE";
				}
			}
			
			if ($accessLevel == "LIMITED") {
				foreach ($dbList as $theDb) {
					$newQuery = "GRANT " . $privList;
					
					$newQuery .= " ON `$theDb`.*";
					
					$newQuery .= " TO '" . $newName . "'@'" . $newHost . "'";
					
					if ($newPass)
						$newQuery .= " IDENTIFIED BY '" . $newPass . "'";
					
					if (isset($_POST['GRANTOPTION']))
						$newQuery .= " WITH GRANT OPTION";
					
					$conn->query($newQuery) or ($dbError = $conn->error());
				}
			} else {
				$newQuery = "GRANT " . $privList;
				
				$newQuery .= " ON *.*";
				
				$newQuery .= " TO '" . $newName . "'@'" . $newHost . "'";
				
				if ($newPass)
					$newQuery .= " IDENTIFIED BY '" . $newPass . "'";
				
				if (isset($_POST['GRANTOPTION']))
					$newQuery .= " WITH GRANT OPTION";
				
				$conn->query($newQuery) or ($dbError = $conn->error());
			}
			
			$conn->query("FLUSH PRIVILEGES") or ($dbError = $conn->error());
			
		}
	}

	$connected = $conn->selectDB("mysql");

	// delete users
	if (isset($_POST['deleteUsers']) && $connected) {
		$deleteUsers = $_POST['deleteUsers'];
		
		// boom!
		$userList = explode(";", $deleteUsers);
		
		foreach ($userList as $each) {
			$split = explode("@", $each, 2);
			
			if (isset($split[0]))
				$user = trim($split[0]);
			
			if (isset($split[1]))
				$host = trim($split[1]);
			
			if (isset($user) && isset($host)) {
				$conn->query("REVOKE ALL PRIVILEGES ON *.* FROM '$user'@'$host'");
				$conn->query("REVOKE GRANT OPTION ON *.* FROM '$user'@'$host'");
				$conn->query("DELETE FROM `user` WHERE `User`='$user' AND `Host`='$host'");
				$conn->query("DELETE FROM `db` WHERE `User`='$user' AND `Host`='$host'");
				$conn->query("DELETE FROM `tables_priv` WHERE `User`='$user' AND `Host`='$host'");
				$conn->query("DELETE FROM `columns_priv` WHERE `User`='$user' AND `Host`='$host'");
			}
		}
		$conn->query("FLUSH PRIVILEGES");
	}

	if (isset($dbError)) {
		echo '<div class="errormessage" style="margin: 6px 12px 10px 7px; width: 550px">';
		echo '<strong>' . __("Error performing operation") . '</strong><p>' . $dbError . '</p>';
		echo '</div>';
	}

	?>

	<div class="users">

	<?php

	if ($connected) {
		
		$userSql = $conn->query("SELECT * FROM `user`");
		
		if ($conn->isResultSet($userSql)) {
			
			?>
			
			<table class="browsenav">
			<tr>
			<td class="options">
			<?php
			
			echo __("Select") . ':&nbsp;&nbsp;<a onclick="checkAll()">' . __("All") . '</a>&nbsp;&nbsp;<a onclick="checkNone()">' . __("None") . '</a>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . __("With selected") . ':&nbsp;&nbsp;<a onclick="editSelectedRows()">' . __("Edit") . '</a>&nbsp;&nbsp;<a onclick="deleteSelectedUsers()">' . __("Delete") . '</a>';
			
			?>
			
			</td>
			</tr>
			</table>
			
			<?php
			
			echo '<div class="grid">';
			
			echo '<div class="emptyvoid">&nbsp;</div>';
			
			echo '<div class="gridheader impotent">';
				echo '<div class="gridheaderinner">';
				echo '<table cellpadding="0" cellspacing="0">';
				echo '<tr>';
				echo '<td><div column="1" class="headertitle column1">' . __("Host") . '</div></td>';
				echo '<td><div class="columnresizer"></div></td>';
				echo '<td><div column="2" class="headertitle column2">' . __("User") . '</div></td>';
				echo '<td><div class="columnresizer"></div></td>';
				echo '</tr>';
				echo '</table>';
				echo '</div>';
			echo '</div>';
			
			echo '<div class="leftchecks" style="max-height: 400px">';
			
			$m = 0;
			
			while ($userRow = $conn->fetchAssoc($userSql)) {
				$queryBuilder = $userRow['User'] . "@" . $userRow['Host'];
				echo '<dl class="manip';
				
				if ($m % 2 == 1)
					echo ' alternator';
				else 
					echo ' alternator2';
				
				echo '"><dt><input type="checkbox" class="check' . $m . '" onclick="rowClicked(' . $m++ . ')" querybuilder="' . $queryBuilder . '" /></dt></dl>';
			}
			
			echo '</div>';
			
			$userSql = $conn->query("SELECT * FROM `user`");
			
			echo '<div class="gridscroll withchecks" style="overflow-x: hidden; max-height: 400px">';
			
			if ($conn->isResultSet($userSql)) {
				$m = 0;
				
				while ($userRow = $conn->fetchAssoc($userSql)) {
					
					echo '<div class="row' . $m . ' browse';
					
					if ($m % 2 == 1) { echo ' alternator'; }
					else 
					{ echo ' alternator2'; }
					
					echo '">';
					echo '<table cellspacing="0" cellpadding="0">';
					echo '<tr>';
					echo '<td><div class="item column1">' . $userRow['Host'] . '</div></td>';
					echo '<td><div class="item column2">' . $userRow['User'] . '</div></td>';
					echo '</tr>';
					echo '</table>';
					echo '</div>';
					
					$m++;
				}
			}
			
			echo '</div>';
			echo '</div>';
		
		}
		
		$hasPermissions = false;
		
		// check to see if this user has proper permissions to manage users
		$checkSql = $conn->query("SELECT `Grant_priv` FROM `user` WHERE `Host`='" . $conn->getOptionValue("host") . "' AND `User`='" . $_SESSION['SB_LOGIN_USER'] . "' LIMIT 1");
		
		if ($conn->isResultSet($checkSql)) {
			$grantValue = $conn->result($checkSql, 0, "Grant_priv");
			
			if ($grantValue == "Y") {
				$hasPermissions = true;
			}
		}
		
		if ($hasPermissions) {
		
		?>
		
		<div class="inputbox" style="margin-top: 15px">
			<h4><?php echo __("Add a new user"); ?></h4>
				
			<form id="NEWUSERFORM" onsubmit="submitForm('NEWUSERFORM'); return false">
			<table cellpadding="0">
			<tr>
				<td class="secondaryheader"><?php echo __("Host"); ?>:</td>
				<td><input type="text" class="text" name="NEWHOST" value="localhost" /></td>
			</tr>
			<tr>
				<td class="secondaryheader"><?php echo __("Name"); ?>:</td>
				<td><input type="text" class="text" name="NEWNAME" /></td>
			</tr>
			<tr>
				<td class="secondaryheader"><?php echo __("Password"); ?>:</td>
				<td><input type="password" class="text" name="NEWPASS" /></td>
			</tr>
			<?php
			
			$dbList = $conn->listDatabases();
			
			if ($conn->isResultSet($dbList)) {
			
			?>
			<tr>
				<td class="secondaryheader"><?php echo __("Allow access to"); ?>:</td>
				<td>
				<label><input type="radio" name="ACCESSLEVEL" value="GLOBAL" id="ACCESSGLOBAL" onchange="updatePane('ACCESSSELECTED', 'dbaccesspane'); updatePane('ACCESSGLOBAL', 'adminprivlist')" onclick="updatePane('ACCESSSELECTED', 'dbaccesspane'); updatePane('ACCESSGLOBAL', 'adminprivlist')" checked="checked" /><?php echo __("All databases"); ?></label><br />
				<label><input type="radio" name="ACCESSLEVEL" value="LIMITED" id="ACCESSSELECTED" onchange="updatePane('ACCESSSELECTED', 'dbaccesspane'); updatePane('ACCESSGLOBAL', 'adminprivlist')" onclick="updatePane('ACCESSSELECTED', 'dbaccesspane'); updatePane('ACCESSGLOBAL', 'adminprivlist')" /><?php echo __("Selected databases"); ?></label>
				
				<div id="dbaccesspane" style="display: none"  class="privpane">
					<table cellpadding="0">
					<?php
					
					while ($dbRow = $conn->fetchArray($dbList)) {
						echo '<tr>';
						echo '<td>';
						echo '<label><input type="checkbox" name="DBLIST[]" value="' . $dbRow[0] . '" />' . $dbRow[0] . '</label>';
						echo '</td>';
						echo '</tr>';
					}
					
					?>
					</table>
				</div>
				
				</td>
			</tr>
			<?php
			
			}
			
			?>
			<tr>
				<td class="secondaryheader"><?php echo __("Give user"); ?>:</td>
				<td>
				<label><input type="radio" name="NEWCHOICE" value="ALL" onchange="updatePane('PRIVSELECTED', 'privilegepane')" onclick="updatePane('PRIVSELECTED', 'privilegepane')" checked="checked" /><?php echo __("All privileges"); ?></label><br />
				<label><input type="radio" name="NEWCHOICE" value="SELECTED" id="PRIVSELECTED" onchange="updatePane('PRIVSELECTED', 'privilegepane')" onclick="updatePane('PRIVSELECTED', 'privilegepane')" /><?php echo __("Selected privileges"); ?></label>
				
				<div id="privilegepane" style="display: none"  class="privpane">
					<div class="paneheader">
					<?php echo __("User privileges"); ?>
					</div>
					<table cellpadding="0" id="userprivs">
					<tr>
						<td width="50%">
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="SELECT" /><?php echo __("Select"); ?></label>
						</td>
						<td width="50%">
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="INSERT" /><?php echo __("Insert"); ?></label>
						</td>
					</tr>
					<tr>
						<td>
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="UPDATE" /><?php echo __("Update"); ?></label>
						</td>
						<td>
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="DELETE" /><?php echo __("Delete"); ?></label>
						</td>
					</tr>
					<tr>
						<td>
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="INDEX" /><?php echo __("Index"); ?></label>
						</td>
						<td>
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="ALTER" /><?php echo __("Alter"); ?></label>
						</td>
					</tr>
					<tr>
						<td>
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="CREATE" /><?php echo __("Create"); ?></label>
						</td>
						<td>
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="DROP" /><?php echo __("Drop"); ?></label>
						</td>
					</tr>
					<tr>
						<td colspan="2">
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="CREATE TEMPORARY TABLES" /><?php echo __("Temp tables"); ?></label>
						</td>
					</tr>
					</table>
					<div id="adminprivlist">
					<div class="paneheader">
					<?php echo __("Administrator privileges"); ?>
					</div>
					<table cellpadding="0" id="adminprivs">
					<tr>
						<td width="50%">
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="FILE" /><?php echo __("File"); ?></label>
						</td>
						<td width="50%">
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="PROCESS" /><?php echo __("Process"); ?></label>
						</td>
					</tr>
					<tr>
						<td>
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="RELOAD" /><?php echo __("Reload"); ?></label>
						</td>
						<td>
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="SHUTDOWN" /><?php echo __("Shutdown"); ?></label>
						</td>
					</tr>
					<tr>
						<td>
						<label><input type="checkbox" name="NEWPRIVILEGES[]" value="SUPER" /><?php echo __("Super"); ?></label>
						</td>
						<td>
						</td>
					</tr>
					</table>
					</div>
				</div>
				
				</td>
			</tr>
			</table>
			
			<table cellpadding="0">
			<tr>
				<td class="secondaryheader"><?php echo __("Options"); ?>:</td>
				<td>
				<label><input type="checkbox" name="GRANTOPTION" value="true" /><?php echo __("Grant option"); ?></label>
				</td>
			</tr>
			</table>
			
			<div style="margin-top: 10px; height: 22px; padding: 4px 0">
				<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />
			</div>
			</form>
		</div>
		<?php
		
		} else {
			?>
			<h4 style="margin-top: 20px"><?php echo __("Error"); ?></h4>
			<p><?php echo __("You do not have enough permissions to create new users."); ?></p>
			<?php
		}

	} else {
		?>
		<div class="errorpage">
		<h4><?php echo __("Error"); ?></h4>
		<p><?php echo __("You do not have enough permissions to view or manage users."); ?></p>
		</div>
		<?php
	}

	?>
	</div>

	<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
	setTimeout(function(){ startGrid(); }, 1);
	</script>
	<?php

} else if ($file == 'serve.php') {

	include "functions.php";

	function compressCSS($input) {
		// remove comments
		$input = preg_replace("/\/\*.*\*\//Us", "", $input);
		
		// remove unnecessary characters
		$input = str_replace(":0px", ":0", $input);
		$input = str_replace(":0em", ":0", $input);
		$input = str_replace(" 0px", " 0", $input);
		$input = str_replace(" 0em", " 0", $input);
		$input = str_replace(";}", "}", $input);
		
		// remove spaces, etc
		$input = preg_replace('/\s\s+/', ' ', $input);
		$input = str_replace(" {", "{", $input);
		$input = str_replace("{ ", "{", $input);
		$input = str_replace("\n{", "{", $input);
		$input = str_replace("{\n", "{", $input);
		$input = str_replace(" }", "}", $input);
		$input = str_replace("} ", "}", $input);
		$input = str_replace(": ", ":", $input);
		$input = str_replace(" :", ":", $input);
		$input = str_replace(";\n", ";", $input);
		$input = str_replace(" ;", ";", $input);
		$input = str_replace("; ", ";", $input);
		$input = str_replace(", ", ",", $input);
		
		return trim($input);
	}

	function compressJS($input) {
		
		// remove comments
		$input = preg_replace("/\/\/.*\n/Us", "", $input);
		$input = preg_replace("/\/\*.*\*\//Us", "", $input);
		
		// remove spaces, etc
		$input = preg_replace("/\t/", "", $input);
		$input = preg_replace("/\n\n+/m", "\n", $input);
		$input = str_replace(";\n", ";", $input);
		$input = str_replace(" = ", "=", $input);
		$input = str_replace(" == ", "==", $input);
		$input = str_replace(" || ", "||", $input);
		$input = str_replace(" && ", "&&", $input);
		$input = str_replace(")\n{", "){", $input);
		$input = str_replace("if (", "if(", $input);
		
		return trim($input);
	}

	if (isset($_GET['file'])) {
		
		$filename = $_GET['file'];
		
		if (!(strpos($filename, "css/") === 0 || strpos($filename, "themes/") === 0 || strpos($filename, "js/") === 0))
			exit;
		
		if (strpos($filename, "..") !== false)
			exit;
		
		if (file_exists($filename)) {
			if (extension_loaded('zlib') && ((isset($sbconfig['EnableGzip']) && $sbconfig['EnableGzip'] == true) || !isset($sbconfig['EnableGzip']))) {
				ob_start("ob_gzhandler");
				header("Content-Encoding: gzip");
			} else {
				ob_start();
			}
			
			$last_modified_time = filemtime($filename);
			$etag = md5_file($filename);
			
			header("Last-Modified: " . gmdate("D, d M Y H:i:s", $last_modified_time) . " GMT");
			header("Expires: " . gmdate("D, d M Y H:i:s", time()+24*60*60*60) . " GMT");
			header("Etag: $etag");
			
			if ((array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER) && @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time) || (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)) {
				header("HTTP/1.1 304 Not Modified");
				exit;
			}
			
			$contents = file_get_contents($filename);
			
			if (substr($filename, -4) == ".css") {
				header("Content-Type: text/css");
				$contents = compressCSS($contents);
			} else if (substr($filename, -3) == ".js" && strpos($filename, "mootools") === false) {
				header("Content-Type: application/x-javascript");
				$contents = compressJS($contents);
			} else if (substr($filename, -3) == ".js") {
				header("Content-Type: application/x-javascript");
			}
			
			echo $contents;
			
			ob_end_flush();
		} else {
			echo "File doesn't exist!";
		}
	}
	
} else {

	include "functions.php";

	loginCheck(false);
	outputPage();
}


} else {
	
	include "functions.php";

	loginCheck(false);
	outputPage();

}


?>