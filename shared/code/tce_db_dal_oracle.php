<?php
//============================================================+
// File name   : tce_db_dal_oracle.php
// Begin       : 2009-10-09
// Last Update : 2009-10-10
// 
// Description : Oracle driver for TCExam Database
//               Abstraction Layer (DAL).
//               This abstraction use the same SQL syntax
//               of MySQL.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com S.r.l.
//               Via della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//
// License: 
//    Copyright (C) 2004-2009  Nicola Asuni - Tecnick.com S.r.l.
//    
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//    
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//     
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Oracle driver for TCExam Database Abstraction Layer (DAL).
 * This abstraction layer uses the same SQL syntax of MySQL.
 * @package com.tecnick.tcexam.shared.postgresql
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2009-10-09
 */

/**
 * Open a connection to a Oracle Server and select a database.
 * If a second call is made to this function with the same arguments, no new link will be established, but instead, the link identifier of the already opened link will be returned.
 * @param string $host database server host name.
 * @param string $port database connection port
 * @param string $username Name of the user that owns the server process.
 * @param string $password Password of the user that owns the server process.
 * @param string $database Database name.
 * @return Oracle link identifier on success, or FALSE on failure. 
 */
function F_db_connect($host = 'localhost', $port = '1521', $username = 'root', $password = '', $database = '') {
	$dbstring = '//'.$host.':'.$port;
	if (!empty($database)) {
		$dbstring .= '/'.$database;
	}
	if(!$db = @oci_connect($username, $password, $dbstring, 'UTF8')) {
		return FALSE;
	}
	// change date format
	@F_db_query('ALTER SESSION SET NLS_DATE_FORMAT=\'YYYY-MM-DD HH24:MI:SS\'', $db);
	return $db;
}

/**
 * Closes the non-persistent connection to a database associated with the given connection resource.
 * @param resource $link_identifier database link identifier.
 * @return bool TRUE on success or FALSE on failure
 */
function F_db_close($link_identifier) {
	return oci_close($link_identifier);
}

/**
 * Returns the text of the error message from previous database operation
 * @return string error message.
 */
function F_db_error() {
	$e = oci_error();
	return '['.$e['code'].']: '.$e['message'].'';
}

/**
 * Sends a query to the currently active database on the server that's associated with the specified link identifier.<br>
 * NOTE: Convert MySQL RAND() function to Oracle RANDOM() on ORDER BY clause of selection queries.
 * @param string $query The query tosend. The query string should not end with a semicolon. 
 * @param resource $link_identifier database link identifier.
 * @return FALSE in case of error, TRUE or resource-identifier in case of success.
 */
function F_db_query($query, $link_identifier) {
	if ($query == 'START TRANSACTION') {
		return true;
	}
	// convert MySQL RAND() function to Oracle dbms_random.random
	$query = preg_replace('/ORDER BY RAND\(\)/si', 'ORDER BY dbms_random.random', $query);
	// remove last limit clause
	$query = preg_replace("/LIMIT 1([\s]*)$/si", '', $query);
	$stid = @oci_parse($link_identifier, $query);
	if (!$stid) {
		return false;
	}
	if (@oci_execute($stid)) {
		return $stid;
	}
	return false;
}

/**
 * Fetch a result row as an associative and numeric array.
 * Note: This function sets NULL fields to PHP NULL value.
 * @param resource $result result resource to the query result.
 * @return Returns an array that corresponds to the fetched row, or FALSE if there are no more rows.
*/
function F_db_fetch_array($result) {
	$arr = oci_fetch_array($result, OCI_BOTH + OCI_RETURN_NULLS + OCI_RETURN_LOBS);
	if ($arr !== false) {
		$arr = array_change_key_case($arr, CASE_LOWER);
		$arr = array_map('stripslashes', $arr);
	}
	return $arr;
}

/**
 * Returns number of rows (tuples) affected by the last INSERT, UPDATE or DELETE query associated with link_identifier.
 * @param resource $link_identifier database link identifier [UNUSED].
 * @param resource $result result resource to the query result.
 * @return Number of rows.
 */
function F_db_affected_rows($link_identifier, $result) {
	return oci_num_rows($result);
}

/**
 * Get number of rows in result.
 * @param resource $result result resource to the query result.
 * @return Number of affected rows.
 */
function F_db_num_rows($result) {
	$output = array();
	@oci_fetch_all($result, $output);
	if (isset($output['TOTAL'][0])) {
		return $output['TOTAL'][0];
	}
	return oci_num_rows($result);
}

/**
 * Get the ID generated from the previous INSERT operation
 * @param resource $link_identifier database link identifier.
 * @param string Table name.
 * @param string Field name (column name).
 * @return int ID generated from the last INSERT operation.
 */
function F_db_insert_id($link_identifier, $tablename = '', $fieldname = '') {
	$query = 'SELECT '.$tablename.'_seq.currval FROM dual';
	if($r = @F_db_query($query, $link_identifier)) {
		if($m = oci_fetch_array($r, OCI_NUM)) {
			return $m[0];
		}
	}
	return 0;
}

/**
 * Escape a string for insertion into a SQL text field (avoiding SQL injection).
 * @param string $str The string that is to be escaped.
 * @param boolean $stripslashes if true and magic_quotes_gpc is on, then strip slashes from string
 * @return string Returns the escaped string, or FALSE on error.
 * @since 5.0.005 2007-12-05
 */
function F_escape_sql($str, $stripslashes=true) {
	// Reverse magic_quotes_gpc/magic_quotes_sybase effects if ON.
	if ($stripslashes AND get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
	return pg_escape_string($str);
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
