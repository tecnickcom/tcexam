<?php
//============================================================+
// File name   : tce_functions_session.php
// Begin       : 2001-09-26
// Last Update : 2020-05-06
//
// Description : User-level session storage functions.
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
//    Copyright (C) 2004-2020 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * User-level session storage functions.<br>
 * This script uses the session_set_save_handler() function to set the user-level session storage functions which are used for storing and retrieving data associated with a session.<br>
 * The session data is stored on a local database.
 * NOTE: This script automatically starts the user's session.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2001-09-26
 */

/**
 */

// PHP session settings
//ini_set('session.save_handler', 'user');
ini_set('session.name', 'PHPSESSID');
//ini_set('session.gc_maxlifetime', K_SESSION_LIFE);
//ini_set('session.cookie_lifetime', K_COOKIE_EXPIRE);
ini_set('session.use_cookies', true);

/**
 * Open session.
 * @param $save_path (string) path were to store session data
 * @param $session_name (string) name of session
 * @return bool always TRUE
 */
function F_session_open($save_path, $session_name)
{
    return true;
}

/**
 * Close session.<br>
 * Call garbage collector function to remove expired sessions.
 * @return bool always TRUE
 */
function F_session_close()
{
    F_session_gc(); //call garbage collector
    return true;
}

/**
 * Get session data.
 * @param $key (string) session ID.
 * @return string session data.
 */
function F_session_read($key)
{
    global $db;
    $key = F_escape_sql($db, $key);
    $sql = 'SELECT cpsession_data
			FROM '.K_TABLE_SESSIONS.'
			WHERE cpsession_id=\''.$key.'\'
				AND cpsession_expiry>=\''.date(K_TIMESTAMP_FORMAT).'\'
			LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            return $m['cpsession_data'];
        } else {
            return('');
        }
    }
    return('');
}

/**
 * Insert or Update session.
 * @param $key (string) session ID.
 * @param $val (string) session data.
 * @return resource database query result.
 */
function F_session_write($key, $val)
{
    global $db;
    if ((!isset($db)) or (!$db)) {
        // workaround for PHP bug 41230
        if (!$db = @F_db_connect(K_DATABASE_HOST, K_DATABASE_PORT, K_DATABASE_USER_NAME, K_DATABASE_USER_PASSWORD, K_DATABASE_NAME)) {
            return;
        }
    }
    $key = F_escape_sql($db, $key);
    $val = F_escape_sql($db, $val);
    $expiry = date(K_TIMESTAMP_FORMAT, (time() + K_SESSION_LIFE));
    // check if this session already exist on database
    $sql = 'SELECT cpsession_id
			FROM '.K_TABLE_SESSIONS.'
			WHERE cpsession_id=\''.$key.'\'
			LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            // SQL to update existing session
            $sqlup = 'UPDATE '.K_TABLE_SESSIONS.' SET
				cpsession_expiry=\''.$expiry.'\',
				cpsession_data=\''.$val.'\'
				WHERE cpsession_id=\''.$key.'\'';
        } else {
            // SQL to insert new session
            $sqlup = 'INSERT INTO '.K_TABLE_SESSIONS.' (
				cpsession_id,
				cpsession_expiry,
				cpsession_data
				) VALUES (
				\''.$key.'\',
				\''.$expiry.'\',
				\''.$val.'\'
				)';
        }
        return F_db_query($sqlup, $db);
    }
    return false;
}

/**
 * Deletes the specific session.
 * @param $key (string) session ID of session to destroy.
 * @return resource database query result.
 */
function F_session_destroy($key)
{
    global $db;
    $key = F_escape_sql($db, $key);
    $sql = 'DELETE FROM '.K_TABLE_SESSIONS.' WHERE cpsession_id=\''.$key.'\'';
    return F_db_query($sql, $db);
}

/**
 * Garbage collector.<br>
 * Deletes expired sessions.<br>
 * NOTE: while time() function returns a 32 bit integer, it works fine until year 2038.
 * @return int number of deleted sessions.
 */
function F_session_gc()
{
    global $db;
    $expiry_time = date(K_TIMESTAMP_FORMAT);
    $sql = 'DELETE FROM '.K_TABLE_SESSIONS.' WHERE cpsession_expiry<=\''.$expiry_time.'\'';
    if (!$r = F_db_query($sql, $db)) {
        return false;
    }
    return F_db_affected_rows($db, $r);
}

/**
 * Convert encoded session string data to array.
 * @author Nicola Asuni
 * @since 2001-10-18
 * @param $sd (string) input data string
 * @return array
 */
function F_session_string_to_array($sd)
{
    $sess_array = array();
    $vars = preg_split('/[;}]/', $sd);
    for ($i=0; $i < count($vars)-1; $i++) {
        $parts = explode('|', $vars[$i]);
        $key = $parts[0];
        $val = unserialize($parts[1].';');
        $sess_array[$key] = $val;
    }
    return $sess_array;
}

/**
 * Generate a client fingerprint (unique ID for the client browser)
 * @author Nicola Asuni
 * @since 2010-10-04
 * @return string client ID
 */
function getClientFingerprint()
{
    $sid = K_RANDOM_SECURITY;
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $sid .= $_SERVER['HTTP_USER_AGENT'];
    }
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        $sid .= $_SERVER['HTTP_ACCEPT'];
    }
    if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
        $sid .= $_SERVER['HTTP_ACCEPT_ENCODING'];
    }
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $sid .= $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }
    if (isset($_SERVER['HTTP_DNT'])) {
        $sid .= $_SERVER['HTTP_DNT'];
    }
    if (isset($_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'])) {
        $sid .= $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'];
    }
    return md5($sid);
}

/**
 * Generate and return a new session ID.
 * @author Nicola Asuni
 * @since 2010-10-04
 * @return string PHPSESSID
 */
function getNewSessionID()
{
    return md5(getPasswordHash(uniqid(microtime().getClientFingerprint().K_RANDOM_SECURITY.session_id(), true)));
}

/**
 * Hash password for Database storing.
 * @param $password (string) Password to hash.
 * @return string password hash
 */
function getPasswordHash($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifies that a password matches a hash
 * @param $password (string) The password to verify
 * @param $hash (string) Password hash
 * 
 * @return boolean
 */
function checkPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Generate unencoded CSRF token string
 * 
 * @return string
 */
function getPlainCSRFToken()
{
    $inc = get_included_files();
    return $inc[0].session_id().K_RANDOM_SECURITY.getClientFingerprint();
}

/**
 * Check the CSRF token
 * @param $token (string) tocken to check
 * 
 * @return boolean
 */
function checkCSRFToken($token)
{
    return checkPassword(getPlainCSRFToken(), $token);
}

/**
 * Generate CSRF token
 * 
 * @return string
 */
function F_getCSRFToken()
{
    return getPasswordHash(getPlainCSRFToken());
}

// ------------------------------------------------------------

// Sets user-level session storage functions.
session_set_save_handler('F_session_open', 'F_session_close', 'F_session_read', 'F_session_write', 'F_session_destroy', 'F_session_gc');

// start user session
if (isset($_COOKIE['PHPSESSID'])) {
    // cookie takes precedence
    $_REQUEST['PHPSESSID'] = $_COOKIE['PHPSESSID'];
}
if (isset($_REQUEST['PHPSESSID'])) {
    // sanitize $PHPSESSID from get/post/cookie
    $PHPSESSID = preg_replace('/[^0-9a-f]*/', '', $_REQUEST['PHPSESSID']);
    if (strlen($PHPSESSID) != 32) {
        // generate new ID
        $PHPSESSID = getNewSessionID();
    }
} else {
    // create new PHPSESSID
    $PHPSESSID = getNewSessionID();
}

if ((!isset($_REQUEST['menu_mode'])) or ($_REQUEST['menu_mode'] != 'startlongprocess')) {
    // fix flush problem on long processes
    session_id($PHPSESSID); //set session id
}

session_start(); //start session
header('Cache-control: private'); // fix IE6 bug

//============================================================+
// END OF FILE
//============================================================+
