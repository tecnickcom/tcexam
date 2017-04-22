<?php
//============================================================+
// File name   : tce_functions_install.php
// Begin       : 2002-05-13
// Last Update : 2013-10-23
//
// Description : Installation functions for TCExam.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2013  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Install TCExam database.
 * @param string $db_type database type (MySQL)
 * @param string $db_host database host
 * @param string $db_port database port number
 * @param string $db_user database user
 * @param string $db_password database password
 * @param string $database_name database name
 * @param string $table_prefix prefix for tables
 * @param boolean $drop_database if true drop existing database
 * @param boolean $create_new if true creates new database
 * @param string $progress_log log file name
 * @return boolean True in case of success, False otherwise.
 */
function F_install_database($db_type, $db_host, $db_port, $db_user, $db_password, $database_name, $table_prefix, $drop_database, $create_new, $progress_log) {
	ini_set('memory_limit', '256M');
	define ('K_DATABASE_TYPE', $db_type); // database type (for Database Abstraction Layer)
	// Load the Database Abstraction Layer for selected DATABASE type
	switch (K_DATABASE_TYPE) {
		case 'ORACLE': {
			require_once('../shared/code/tce_db_dal_oracle.php');
			break;
		}
		case 'POSTGRESQL': {
			require_once('../shared/code/tce_db_dal_postgresql.php');
			break;
		}
		case 'MYSQL': {
			require_once('../shared/code/tce_db_dal_mysqli.php');
			break;
		}
		case 'MYSQLDEPRECATED': {
			require_once('../shared/code/tce_db_dal_mysql.php');
			break;
		}
		default: {
			return false;
		}
	}
	echo "\n".'<li>create or replace database and get connection........';
	error_log('  create or replace database and get connection'."\n", 3, $progress_log); //log info
	if ($db = F_create_database(K_DATABASE_TYPE, $db_host, $db_port, $db_user, $db_password, $database_name, $table_prefix, $drop_database, $create_new)) { //create database if not exist
		echo '<span style="color:#008000">[OK]</span></li>';
		echo "\n".'<li>create database tables..........';
		error_log('  [START] create database tables'."\n", 3, $progress_log); //log info
		// process structure sql file
		if (F_execute_sql_queries($db, strtolower(K_DATABASE_TYPE).'_db_structure.sql', 'tce_', $table_prefix, $progress_log)) {
			echo '<span style="color:#008000">[OK]</span></li>';
			error_log('  [END:OK] create database tables'."\n", 3, $progress_log); //log info
			echo "\n".'<li>fill tables with default data...';
			error_log('  [START] fill tables with default data'."\n", 3, $progress_log); //log info
			// process data sql file
			if (F_execute_sql_queries($db, 'db_data.sql', 'tce_', $table_prefix, $progress_log)) {
				echo '<span style="color:#008000">[OK]</span></li>';
				error_log('  [END:OK] fill tables with default data'."\n", 3, $progress_log); //log info
			} else {
				echo '<span style="color:#CC0000">[ERROR '.F_db_error($db).']</span></li>';
				error_log('  [END:ERROR] fill tables with default data: '.F_db_error($db)."\n", 3, $progress_log); //log info
				return false;
			}
		} else {
			echo '<span style="color:#CC0000">[ERROR '.F_db_error($db).']</span></li>';
			error_log('  [END:ERROR] create database tables: '.F_db_error($db)."\n", 3, $progress_log); //log info
			return false;
		}
	} else {
		echo '<span style="color:#CC0000">[ERROR: '.F_db_error($db).']</span></li>';
		error_log('  [END:ERROR] could not connect to database: '.F_db_error($db)."\n", 3, $progress_log); //log info
		return false;
	}
	flush();
	return true;
}


/**
 * Parses an SQL file and execute queries.
 * @param string $db database connector
 * @param string $sql_file file to parse
 * @param string $search string to replace
 * @param string $replace replace string
 * @param string $progress_log log file name
 * @return boolean true in case of success, false otherwise.
 */
function F_execute_sql_queries($db, $sql_file, $search, $replace, $progress_log) {
	ini_set('memory_limit', -1); // remove memory limit

	$sql_data = @fread(@fopen($sql_file, 'r'), @filesize($sql_file)); //open and read file
	if ($search) {
		$sql_data = str_replace($search, $replace, $sql_data); // execute search and replace for the given parameters
	}
	$sql_data = str_replace("\r", '', $sql_data); // remove CR
	$sql_data = "\n".$sql_data; //prepare string for replacements
	$sql_data = preg_replace("/\/\*([^\*]*)\*\//si", ' ', $sql_data); // remove comments (/* ... */)
	$sql_data = preg_replace("/\n([\s]*)\#([^\n]*)/si", '', $sql_data); // remove comments (lines starting with '#' (MySQL))
	$sql_data = preg_replace("/\n([\s]*)\-\-([^\n]*)/si", '', $sql_data); // remove comments (lines starting with '--' (PostgreSQL))
	$sql_data = preg_replace("/;([\s]*)\n/si", ";\r", $sql_data); // mark valid new lines
	$sql_data = str_replace("\n", " ", $sql_data); // remove carriage returns
	$sql_data = preg_replace("/(;\r)$/si", '', $sql_data); // remove last ";\r"
	$sql_query = explode(";\r", trim($sql_data)); // split sql string into SQL statements
	//execute queries
	while(list($key, $sql) = each($sql_query)) { //for query on sql file
		error_log('    [SQL] '.$key."\n", 3, $progress_log); //create progress log file
		echo ' '; //print something to keep browser live
		if (($key % 300) == 0) { //force flush output every 300 processed queries
			echo '<!-- '.$key.' -->'."\n"; flush(); //force flush output to browser
		}
		if(!$r = F_db_query($sql, $db)) {
			return FALSE;
		}
	}
	return TRUE;
}

/**
 * Create new database. Existing database will be dropped.
 * Oracle databases must be created manually (create the tcexam user and set the database name equal to user name)
 * @param string $host Database server path. It can also include a port number. e.g. "hostname:port" or a path to a local socket e.g. ":/path/to/socket" for the localhost. Note: Whenever you specify "localhost" or "localhost:port" as server, the MySQL client library will override this and try to connect to a local socket (named pipe on Windows). If you want to use TCP/IP, use "127.0.0.1" instead of "localhost". If the MySQL client library tries to connect to the wrong local socket, you should set the correct path as mysql.default_host in your PHP configuration and leave the server field blank.
 * @param string $dbtype database type ('MYSQL' or 'POSTGREQL')
 * @param string $host database host
 * @param string $port database port
 * @param string $user Name of the user that owns the server process.
 * @param string $password Password of the user that owns the server process.
 * @param string $database Database name.
 * @param string $table_prefix prefix for tables
 * @param boolean $drop if true drop existing database
 * @param boolean $create if true creates new database
 * @return database link identifier on success, FALSE otherwise.
 */
function F_create_database($dbtype, $host, $port, $user, $password, $database, $table_prefix, $drop, $create) {
	// open default connection
	if ($drop OR $create) {
		if ($db = @F_db_connect($host, $port, $user, $password)) {
			if ($dbtype == 'ORACLE') {
				if ($drop) {
					$table_prefix = strtoupper($table_prefix);
					// DROP sequences
					$sql = 'select \'DROP SEQUENCE \'||sequence_name||\'\' from user_sequences where sequence_name like \''.$table_prefix.'%\'';
					if($r = @F_db_query($sql, $db)) {
						while($m = @F_db_fetch_array($r)) {
							@F_db_query($m[0], $db);
						}
					}
					// DROP triggers
					$sql = 'select \'DROP TRIGGER \'||trigger_name||\'\' from user_triggers where trigger_name like \''.$table_prefix.'%\'';
					if($r = @F_db_query($sql, $db)) {
						while($m = @F_db_fetch_array($r)) {
							@F_db_query($m[0], $db);
						}
					}
					// DROP tables
					$sql = 'select \'DROP TABLE \'||table_name||\' CASCADE CONSTRAINTS\' from user_tables where table_name like \''.$table_prefix.'%\'';
					if($r = @F_db_query($sql, $db)) {
						while($m = @F_db_fetch_array($r)) {
							@F_db_query($m[0], $db);
						}
					}
				} else {
					echo '<span style="color:#000080">[SKIP DROP]</span> ';
				}
				// Note: Oracle Database automatically creates a schema when you create a user,
				//       so you have to create a tcexam user before calling this.
			} else {
				if ($drop) {
					// DROP existing database (if exist)
					@F_db_query('DROP DATABASE '.$database.'', $db);
				} else {
					echo '<span style="color:#000080">[SKIP DROP]</span> ';
				}
				if($create) {
					// create database
					$sql = 'CREATE DATABASE '.$database.'';
					if ($dbtype == 'MYSQL') {
						$sql .= ' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
					} elseif ($dbtype == 'POSTGRESQL') {
						$sql .= ' ENCODING=\'UNICODE\'';
					}
					if(!$r = @F_db_query($sql, $db)) {
						return FALSE;
					}
				} else {
					echo '<span style="color:#000080">[SKIP CREATE]</span> ';
				}
			}
			@F_db_close($db);
		} else {
			return FALSE;
		}
	} else {
		echo '<span style="color:#000080">[SKIP DROP AND CREATE]</span> ';
	}
	if ($db = @F_db_connect($host, $port, $user, $password, $database)) {
		return $db;
	} else {
		return FALSE;
	}
}

/**
 * Update some configuration files.
 * @param string $db_type database type (MySQL)
 * @param string $db_host database host
 * @param string $db_port database port number
 * @param string $db_user database user
 * @param string $db_password database password
 * @param string $database_name database name
 * @param string $table_prefix table prefix
 * @param string $path_host host URL
 * @param string $path_tcexam relative URL where this program is installed
 * @param string $path_main real full server path where this program is installed
 * @param string $standard_port standard http web port
 * @param string $progress_log log file name
 * @return boolean true in case of success, false otherwise
 */
function F_update_config_files($db_type, $db_host, $db_port, $db_user, $db_password, $database_name, $table_prefix, $path_host, $path_tcexam, $path_main, $standard_port, $progress_log) {

	if(!defined('PHP_VERSION_ID')) {
		$version = PHP_VERSION;
		define('PHP_VERSION_ID', (($version[0] * 10000) + ($version[2] * 100) + $version[4]));
	}
	if (PHP_VERSION_ID < 50300) {
		@set_magic_quotes_runtime(0);
	}

	// initialize configuration directories with default values
	
	rename('../shared/config.default', '../shared/config');
	rename('../admin/config.default', '../admin/config');
	rename('../public/config.default', '../public/config');

	$config_file = array(); // configuration files

	$config_file[0] = '../shared/config/tce_db_config.php';
	$config_file[1] = '../shared/config/tce_paths.php';

	// file parameters to change as regular expressions (0=>search, 1=>replace)
	$parameter = array();

	$parameter[0] = array(

		'0'  => array ('0' => "K_DATABASE_TYPE', '([^\']*)'", '1' => "K_DATABASE_TYPE', '".$db_type."'"),
		'1'  => array ('0' => "K_DATABASE_HOST', '([^\']*)'", '1' => "K_DATABASE_HOST', '".$db_host."'"),
		'2'  => array ('0' => "K_DATABASE_PORT', '([^\']*)'", '1' => "K_DATABASE_PORT', '".$db_port."'"),
		'3'  => array ('0' => "K_DATABASE_NAME', '([^\']*)'", '1' => "K_DATABASE_NAME', '".$database_name."'"),
		'4'  => array ('0' => "K_DATABASE_USER_NAME', '([^\']*)'", '1' => "K_DATABASE_USER_NAME', '".$db_user."'"),
		'5'  => array ('0' => "K_DATABASE_USER_PASSWORD', '([^\']*)'", '1' => "K_DATABASE_USER_PASSWORD', '".$db_password."'"),
		'6'  => array ('0' => "K_TABLE_PREFIX', '([^\']*)'", '1' => "K_TABLE_PREFIX', '".$table_prefix."'")
	);

	$parameter[1] = array(
		'0'  => array ('0' => "K_PATH_HOST', '([^\']*)'", '1' => "K_PATH_HOST', '".$path_host."'"),
		'1'  => array ('0' => "K_PATH_TCEXAM', '([^\']*)'", '1' => "K_PATH_TCEXAM', '".$path_tcexam."'"),
		'2'  => array ('0' => "K_PATH_MAIN', '([^\']*)'", '1' => "K_PATH_MAIN', '".$path_main."'"),
		'3'  => array ('0' => "K_STANDARD_PORT', ([^\)]*)", '1' => "K_STANDARD_PORT', ".$standard_port."")
	);

	while(list($key, $file_name) = each($config_file)) { //for each configuration file

		error_log('  [START] process file: '.basename($file_name)."\n", 3, $progress_log); //log info
		echo "\n".'<li>start process <i>'.basename($file_name).'</i> file:';
		echo "\n".'<ul>';
		//try to change file permissions (unix-like only)
		//chmod($file_name, 0777);

		echo "\n".'<li>open file.................';
		error_log('    open file', 3, $progress_log); //log info
		$fp = fopen($file_name, 'r+');
		if (!$fp) {
			echo '<span style="color:#CC0000">[ERROR]</span></li>';
			error_log(' [ERROR]'."\n", 3, $progress_log); //log info
		} else { // the file has been opened
			echo '<span style="color:#008000">[OK]</span></li>';
			error_log(' [OK]'."\n", 3, $progress_log); //log info

			//read the file
			echo "\n".'<li>read file.................';
			error_log('    read file', 3, $progress_log); //log info
			$file_data = fread($fp, filesize($file_name));
			if (!$file_data){
				echo '<span style="color:#CC0000">[ERROR]</span></li>';
				error_log(' [ERROR]'."\n", 3, $progress_log); //log info
			} else {
				echo '<span style="color:#008000">[OK]</span></li>';
				error_log(' [OK]'."\n", 3, $progress_log); //log info

				//change cfg file values
				while(list($pkey, $pval) = each($parameter[$key])) { //for each file parameter
					echo "\n".'<li>update value '.$pkey.' ...........';
					error_log('      update value '.$pkey.'', 3, $progress_log); //log info
					$file_data = preg_replace('#'.$pval[0].'#', $pval[1], $file_data); //update cfg parameters
					echo '<span style="color:#008000">[OK]</span></li>';
					error_log(' [OK]'."\n", 3, $progress_log); //log info
				}
			}

			//write the file
			echo "\n".'<li>write file................';
			error_log('    write file', 3, $progress_log); //log info
			rewind ($fp);
			if (!fwrite ($fp, $file_data)) {
				echo '<span style="color:#CC0000">[ERROR]</span></li>';
				error_log(' [ERROR]'."\n", 3, $progress_log); //log info
			} else {
				echo '<span style="color:#008000">[OK]</span></li>';
				error_log(' [OK]'."\n", 3, $progress_log); //log info
			}

			if (strlen($file_data) < filesize($file_name)) {
				ftruncate ($fp, strlen($file_data)); //truncate file
			}

			echo "\n".'<li>close file................';
			error_log('    close file', 3, $progress_log); //log info
			if (fclose($fp)) {
				echo '<span style="color:#008000">[OK]</span></li>';
				error_log(' [OK]'."\n", 3, $progress_log); //log info
			} else {
				echo '<span style="color:#CC0000">[ERROR]</span></li>';
				error_log(' [ERROR]'."\n", 3, $progress_log); //log info
			}
		}

		//try to set file permissions to read only (unix-like only)
		//chmod($file_name, 0644);
		echo "\n".'</ul>';
		echo "\n".'</li>';
		echo "\n".'<li>end process <i>'.basename($file_name).'</i> file</li>';
		error_log('  [END] process file: '.basename($file_name)."\n", 3, $progress_log); //log info
	}
	if (PHP_VERSION_ID < 50300) {
		set_magic_quotes_runtime(get_magic_quotes_gpc()); //restore magic quotes settings
	}
	flush(); // force browser output
	return TRUE;
}

//============================================================+
// END OF FILE
//============================================================+
