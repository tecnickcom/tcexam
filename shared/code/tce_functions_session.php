<?php

//============================================================+
// File name   : tce_functions_session.php
// Begin       : 2001-09-26
// Last Update : 2023-11-30
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
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
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



// PHP session settings
//ini_set('session.save_handler', 'user');
ini_set('session.name', 'PHPSESSID');
//ini_set('session.gc_maxlifetime', K_SESSION_LIFE);
ini_set('session.cookie_lifetime', 0);
ini_set('session.use_cookies', true);
ini_set('session.use_strict_mode', 'On');
ini_set('session.cookie_httponly', K_COOKIE_HTTPONLY ? 'On' : 'Off');
ini_set('session.cookie_secure', K_COOKIE_SECURE ? 'On' : 'Off');
ini_set('session.cookie_samesite', K_COOKIE_SAMESITE);

/**
 * Session Handler Class implementing SessionHandlerInterface
 * @package com.tecnick.tcexam.shared
 */
class TCExamSessionHandler implements SessionHandlerInterface
{
    /**
     * Open session.
     * @param string $path path were to store session data
     * @param string $name name of session
     * @return bool always TRUE
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Close session.<br>
     * Call garbage collector function to remove expired sessions.
     * @return bool always TRUE
     */
    public function close(): bool
    {
        $this->gc(ini_get('session.gc_maxlifetime'));
        return true;
    }

    /**
     * Get session data.
     * @param string $id session ID.
     * @return string|false session data or false on failure.
     */
    public function read(string $id): string|false
    {
        global $db;
        $id = F_escape_sql($db, $id);
        $sql = 'SELECT cpsession_data
				FROM ' . K_TABLE_SESSIONS . '
				WHERE cpsession_id=\'' . $id . '\'
					AND cpsession_expiry>=\'' . date(K_TIMESTAMP_FORMAT) . '\'
				LIMIT 1';
        if ($r = F_db_query($sql, $db)) {
            if ($m = F_db_fetch_array($r)) {
                return $m['cpsession_data'];
            }

            return '';
        }

        return '';
    }

    /**
     * Insert or Update session.
     * @param string $id session ID.
     * @param string $data session data.
     * @return bool true on success, false on failure.
     */
    public function write(string $id, string $data): bool
    {
        global $db;
        // workaround for PHP bug 41230
        if ((! isset($db) || ! $db) && ! $db = @F_db_connect(K_DATABASE_HOST, K_DATABASE_PORT, K_DATABASE_USER_NAME, K_DATABASE_USER_PASSWORD, K_DATABASE_NAME)) {
            return false;
        }

        $id = F_escape_sql($db, $id);
        $data = F_escape_sql($db, $data);
        $expiry = date(K_TIMESTAMP_FORMAT, (time() + K_SESSION_LIFE));
        // check if this session already exist on database
        $sql = 'SELECT cpsession_id
				FROM ' . K_TABLE_SESSIONS . '
				WHERE cpsession_id=\'' . $id . '\'
				LIMIT 1';
        if ($r = F_db_query($sql, $db)) {
            if ($m = F_db_fetch_array($r)) {
                // SQL to update existing session
                $sqlup = 'UPDATE ' . K_TABLE_SESSIONS . ' SET
					cpsession_expiry=\'' . $expiry . '\',
					cpsession_data=\'' . $data . '\'
					WHERE cpsession_id=\'' . $id . "'";
            } else {
                // SQL to insert new session
                $sqlup = 'INSERT INTO ' . K_TABLE_SESSIONS . ' (
					cpsession_id,
					cpsession_expiry,
					cpsession_data
					) VALUES (
					\'' . $id . '\',
					\'' . $expiry . '\',
					\'' . $data . '\'
					)';
            }

            return F_db_query($sqlup, $db) !== false;
        }

        return false;
    }

    /**
     * Deletes the specific session.
     * @param string $id session ID of session to destroy.
     * @return bool true on success, false on failure.
     */
    public function destroy(string $id): bool
    {
        global $db;
        $id = F_escape_sql($db, $id);
        $sql = 'DELETE FROM ' . K_TABLE_SESSIONS . " WHERE cpsession_id='" . $id . "'";
        return F_db_query($sql, $db) !== false;
    }

    /**
     * Garbage collector.<br>
     * Deletes expired sessions.<br>
     * NOTE: while time() function returns a 32 bit integer, it works fine until year 2038.
     * @param int $maxlifetime max session lifetime in seconds.
     * @return int|false number of deleted sessions or false on failure.
     */
    public function gc(int $maxlifetime): int|false
    {
        global $db;
        $expiry_time = date(K_TIMESTAMP_FORMAT);
        $sql = 'DELETE FROM ' . K_TABLE_SESSIONS . " WHERE cpsession_expiry<='" . $expiry_time . "'";
        if (! $r = F_db_query($sql, $db)) {
            return false;
        }

        return F_db_affected_rows($db, $r);
    }
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
    $sess_array = [];
    $vars = preg_split('/[;}]/', $sd);
    for ($i = 0; $i < count($vars) - 1; ++$i) {
        $parts = explode('|', $vars[$i]);
        $key = $parts[0];
        $val = unserialize($parts[1] . ';');
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
    return md5(getPasswordHash(uniqid(microtime() . getClientFingerprint() . K_RANDOM_SECURITY . session_id(), true)));
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
    return $inc[0] . session_id() . K_RANDOM_SECURITY . getClientFingerprint();
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

// Sets user-level session storage functions using SessionHandlerInterface.
session_set_save_handler(new TCExamSessionHandler(), true);

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

if (! isset($_REQUEST['menu_mode']) || $_REQUEST['menu_mode'] != 'startlongprocess') {
    // fix flush problem on long processes
    session_id($PHPSESSID); //set session id
}

session_start(); //start session
header('Cache-control: private'); // fix IE6 bug

//============================================================+
// END OF FILE
//============================================================+
