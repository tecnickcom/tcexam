<?php
//============================================================+
// File name   : tce_db_dal_oracle.php
// Begin       : 2009-10-09
// Last Update : 2014-01-26
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
 * Oracle driver for TCExam Database Abstraction Layer (DAL).
 * This abstraction layer uses the same SQL syntax of MySQL.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2009-10-09
 */

/**
 * Open a connection to a Oracle Server and select a database.
 * If a second call is made to this function with the same arguments, no new link will be established, but instead, the link identifier of the already opened link will be returned.
 * @param $host (string) database server host name.
 * @param $port (string) database connection port
 * @param $username (string) Name of the user that owns the server process.
 * @param $password (string) Password of the user that owns the server process.
 * @param $database (string) Database name.
 * @return Oracle link identifier on success, or FALSE on failure.
 */
function F_db_connect($host = 'localhost', $port = '1521', $username = 'root', $password = '', $database = '')
{
    $dbstring = '//'.$host.':'.$port;
    if (!empty($database)) {
        $dbstring .= '/'.$database;
    }
    if (!$db = @oci_connect($username, $password, $dbstring, 'UTF8')) {
        return false;
    }
    // change date format
    @F_db_query('ALTER SESSION SET NLS_DATE_FORMAT=\'YYYY-MM-DD HH24:MI:SS\'', $db);
    return $db;
}

/**
 * Closes the non-persistent connection to a database associated with the given connection resource.
 * @param $link_identifier (resource) database link identifier.
 * @return bool TRUE on success or FALSE on failure
 */
function F_db_close($link_identifier)
{
    return oci_close($link_identifier);
}

/**
 * Returns the text of the error message from previous database operation
 * @return string error message.
 */
function F_db_error($link_identifier = null)
{
    $e = oci_error();
    return '['.$e['code'].']: '.$e['message'].'';
}

/**
 * Sends a query to the currently active database on the server that's associated with the specified link identifier.<br>
 * NOTE: Convert MySQL RAND() function to Oracle RANDOM() on ORDER BY clause of selection queries.
 * @param $query (string) The query tosend. The query string should not end with a semicolon.
 * @param $link_identifier (resource) database link identifier.
 * @return FALSE in case of error, TRUE or resource-identifier in case of success.
 */
function F_db_query($query, $link_identifier)
{
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
 * @param $result (resource) result resource to the query result.
 * @return Returns an array that corresponds to the fetched row, or FALSE if there are no more rows.
 */
function F_db_fetch_array($result)
{
    $arr = oci_fetch_array($result, OCI_BOTH + OCI_RETURN_NULLS + OCI_RETURN_LOBS);
    if ($arr !== false) {
        $arr = array_change_key_case($arr, CASE_LOWER);
        $arr = array_map('stripslashes', $arr);
    }
    return $arr;
}

/**
 * Fetch a result row as an associative array.
 * Note: This function sets NULL fields to PHP NULL value.
 * @param $result (resource) result resource to the query result.
 * @return Returns an array that corresponds to the fetched row, or FALSE if there are no more rows.
 */
function F_db_fetch_assoc($result)
{
    $arr = oci_fetch_assoc($result, OCI_BOTH + OCI_RETURN_NULLS + OCI_RETURN_LOBS);
    if ($arr !== false) {
        $arr = array_change_key_case($arr, CASE_LOWER);
        $arr = array_map('stripslashes', $arr);
    }
    return $arr;
}

/**
 * Returns number of rows (tuples) affected by the last INSERT, UPDATE or DELETE query associated with link_identifier.
 * @param $link_identifier (resource) database link identifier [UNUSED].
 * @param $result (resource) result resource to the query result.
 * @return Number of rows.
 */
function F_db_affected_rows($link_identifier, $result)
{
    return oci_num_rows($result);
}

/**
 * Get number of rows in result.
 * @param $result (resource) result resource to the query result.
 * @return Number of affected rows.
 */
function F_db_num_rows($result)
{
    $output = array();
    @oci_fetch_all($result, $output);
    if (isset($output['TOTAL'][0])) {
        return $output['TOTAL'][0];
    }
    return oci_num_rows($result);
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
    $query = 'SELECT '.$tablename.'_seq.currval FROM dual';
    if ($r = @F_db_query($query, $link_identifier)) {
        if ($m = oci_fetch_array($r, OCI_NUM)) {
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
    return pg_escape_string($str);
}

//============================================================+
// END OF FILE
//============================================================+
