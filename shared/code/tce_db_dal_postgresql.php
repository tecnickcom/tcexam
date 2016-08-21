<?php
//============================================================+
// File name   : tce_db_dal_postgresql.php
// Begin       : 2003-10-12
// Last Update : 2014-01-26
//
// Description : PostgreSQL driver for TCExam Database
//               Abstraction Layer (DAL).
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
 * PostgreSQL driver for TCExam Database Abstraction Layer (DAL).
 * This abstraction layer uses the same SQL syntax of MySQL.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2004-12-21
 */

/**
 * Open a connection to a PostgreSQL Server and select a database.
 * If a second call is made to this function with the same arguments, no new link will be established, but instead, the link identifier of the already opened link will be returned.
 * @param $host (string) database server host name.
 * @param $port (string) database connection port
 * @param $username (string) Name of the user that owns the server process.
 * @param $password (string) Password of the user that owns the server process.
 * @param $database (string) Database name.
 * @return PostgreSQL link identifier on success, or FALSE on failure.
 */
function F_db_connect($host = 'localhost', $port = '5432', $username = 'postgres', $password = '', $database = 'template1')
{
    $connection_string = 'host=\''.$host.'\' port=\''.$port.'\' dbname=\''.$database.'\' user=\''.$username.'\' password=\''.$password.'\'';
    if (!$db = @pg_connect($connection_string)) {
        return false;
    }
    return $db;
}

/**
 * Closes the non-persistent connection to a database associated with the given connection resource.
 * @param $link_identifier (resource) database link identifier.
 * @return bool TRUE on success or FALSE on failure
 */
function F_db_close($link_identifier)
{
    return pg_close($link_identifier);
}

/**
 * Returns the text of the error message from previous database operation
 * @return string error message.
 */
function F_db_error($link_identifier = null)
{
    return pg_last_error();
}

/**
 * Sends a query to the currently active database on the server that's associated with the specified link identifier.<br>
 * NOTE: Convert MySQL RAND() function to PostgreSQL RANDOM() on ORDER BY clause of selection queries.
 * @param $query (string) The query tosend. The query string should not end with a semicolon.
 * @param $link_identifier (resource) database link identifier.
 * @return FALSE in case of error, TRUE or resource-identifier in case of success.
 */
function F_db_query($query, $link_identifier)
{
    // convert MySQL RAND() function to PostgreSQL RANDOM()
    $query = preg_replace('/ORDER BY RAND\(\)/si', 'ORDER BY RANDOM()', $query);
    return pg_query($link_identifier, $query);
}

/**
 * Fetch a result row as an associative and numeric array.
 * Note: This function sets NULL fields to PHP NULL value.
 * @param $result (resource) result resource to the query result.
 * @return Returns an array that corresponds to the fetched row, or FALSE if there are no more rows.
 */
function F_db_fetch_array($result)
{
    return pg_fetch_array($result);
}

/**
 * Fetch a result row as an associative array.
 * Note: This function sets NULL fields to PHP NULL value.
 * @param $result (resource) result resource to the query result.
 * @return Returns an array that corresponds to the fetched row, or FALSE if there are no more rows.
 */
function F_db_fetch_assoc($result)
{
    return pg_fetch_assoc($result);
}

/**
 * Returns number of rows (tuples) affected by the last INSERT, UPDATE or DELETE query associated with link_identifier.
 * @param $link_identifier (resource) database link identifier [UNUSED].
 * @param $result (resource) result resource to the query result.
 * @return Number of rows.
 */
function F_db_affected_rows($link_identifier, $result)
{
    return pg_affected_rows($result);
}

/**
 * Get number of rows in result.
 * @param $result (resource) result resource to the query result.
 * @return Number of affected rows.
 */
function F_db_num_rows($result)
{
    return pg_num_rows($result);
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
    if ($r = @pg_query($link_identifier, 'SELECT CURRVAL(\''.$tablename.'_'.$fieldname.'_seq\')')) {
        if ($m = pg_fetch_row($r, 0)) {
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
    return pg_escape_string($link_identifier, $str);
}

//============================================================+
// END OF FILE
//============================================================+
