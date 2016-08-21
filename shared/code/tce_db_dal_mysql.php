<?php
//============================================================+
// File name   : tce_db_dal_mysql.php
// Begin       : 2003-10-12
// Last Update : 2014-03-31
//
// Description : MySQL driver for TCExam Database Abstraction
//               Layer (DAL).
//               This abstraction use the same SQL syntax
//               of MySQL.
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
//    Copyright (C) 2004-2014  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * MySQL driver for TCExam Database Abstraction Layer (DAL).
 * This abstraction layer uses the same SQL syntax of MySQL.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2003-10-12
 */

/**
 * Open a connection to a MySQL Server and select a database.
 * @param $host (string) database server host name.
 * @param $port (string) database connection port
 * @param $username (string) Name of the user that owns the server process.
 * @param $password (string) Password of the user that owns the server process.
 * @param $database (string) Database name.
 * @return MySQL link identifier on success, or FALSE on failure.
 */
function F_db_connect($host = 'localhost', $port = '3306', $username = 'root', $password = '', $database = '')
{
    if (!$db = @mysql_connect($host.':'.$port, $username, $password)) {
        return false;
    }
    if ((strlen($database) > 0) and (!@mysql_select_db($database, $db))) {
        return false;
    }
    // set the correct charset encoding
    mysql_query('SET NAMES \'utf8\'');
    mysql_query('SET CHARACTER_SET \'utf8\'');
    mysql_query('SET collation \'utf8_unicode_ci\'');
    mysql_query('SET collation_server \'utf8_unicode_ci\'');
    mysql_query('SET collation_database \'utf8_unicode_ci\'');
    return $db;
}

/**
 * Closes the non-persistent connection to a database associated with the given connection resource.
 * @param $link_identifier (resource) database link identifier.
 * @return bool TRUE on success or FALSE on failure
 */
function F_db_close($link_identifier)
{
    return mysql_close($link_identifier);
}

/**
 * Returns the text of the error message from previous database operation
 * @return string error message.
 */
function F_db_error($link_identifier = null)
{
    if (empty($link_identifier)) {
        return '';
    }
    return '['.mysql_errno($link_identifier).']: '.mysql_error($link_identifier).'';

}

/**
 * Sends a query to the currently active database on the server that's associated with the specified link identifier.<br>
 * @param $query (string) The query tosend. The query string should not end with a semicolon.
 * @param $link_identifier (resource) database link identifier.
 * @return FALSE in case of error, TRUE or resource-identifier in case of success.
 */
function F_db_query($query, $link_identifier)
{
    // convert PostgreSQL RANDOM() function to MySQL RAND()
    //$query = preg_replace("/ORDER BY RANDOM\(\)/i", "ORDER BY RAND()", $query);
    return mysql_query($query, $link_identifier);
}

/**
 * Fetch a result row as an associative and numeric array.
 * Note: This function sets NULL fields to PHP NULL value.
 * @param $result (resource) result resource to the query result.
 * @return Returns an array that corresponds to the fetched row, or FALSE if there are no more rows.
 */
function F_db_fetch_array($result)
{
    return mysql_fetch_array($result);
}

/**
 * Fetch a result row as an associative array.
 * Note: This function sets NULL fields to PHP NULL value.
 * @param $result (resource) result resource to the query result.
 * @return Returns an array that corresponds to the fetched row, or FALSE if there are no more rows.
 */
function F_db_fetch_assoc($result)
{
    return mysql_fetch_assoc($result);
}

/**
 * Returns number of rows (tuples) affected by the last INSERT, UPDATE or DELETE query associated with link_identifier.
 * @param $link_identifier (resource) database link identifier.
 * @param $result (resource) result resource to the query result [UNUSED].
 * @return Number of rows.
 */
function F_db_affected_rows($link_identifier, $result)
{
    return mysql_affected_rows($link_identifier);
}

/**
 * Get number of rows in result.
 * @param $result (resource) result resource to the query result.
 * @return Number of affected rows.
 */
function F_db_num_rows($result)
{
    return mysql_num_rows($result);
}

/**
 * Get the ID generated from the previous INSERT operation
 * @param $link_identifier (resource) database link identifier.
 * @param $tablename (string) Table name.
 * @param $fieldname (string) Field name (column name).
 * @return int ID generated from the last INSERT operation.
 */
function F_db_insert_id($link_identifier, $tablename = '', $fieldname = '')
{
    /*
	 * NOTE : mysql_insert_id() converts the return type of the
	 * native MySQL C API function mysql_insert_id() to a type
	 * of long (named int in PHP). If your AUTO_INCREMENT column
	 * has a column type of BIGINT, the value returned by
	 * mysql_insert_id() will be incorrect.
	 */
     //return mysql_insert_id($link_identifier);
    if ($r = mysql_query('SELECT LAST_INSERT_ID() FROM '.$tablename.'', $link_identifier)) {
        if ($m = mysql_fetch_row($r)) {
            return $m[0];
        }
    }
    return 0;
}

/**
 * Escape a string for insertion into a SQL text field (avoiding SQL injection).
 * @param $link_identifier (resource) database link identifier.
 * @param $str (string) The string that is to be escaped.
 * @param $stripslashes (boolean) if true strip slashes from string
 * @return string Returns the escaped string, or FALSE on error.
 * @since 5.0.005 2007-12-05
 */
function F_escape_sql($link_identifier, $str, $stripslashes = true)
{
    // Reverse magic_quotes_gpc/magic_quotes_sybase effects if ON.
    if ($stripslashes) {
        $str = stripslashes($str);
    }
    return mysql_real_escape_string($str, $link_identifier);
}

//============================================================+
// END OF FILE
//============================================================+
