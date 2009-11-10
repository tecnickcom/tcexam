<?php
//============================================================+
// File name   : tce_functions_session.php
// Begin       : 2001-09-26
// Last Update : 2009-09-30
// 
// Description : User-level session storage functions.
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
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//    
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//    
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//     
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * User-level session storage functions.<br>
 * This script uses the session_set_save_handler() function to set the user-level session storage functions which are used for storing and retrieving data associated with a session.<br>
 * The session data is stored on a local database.
 * NOTE: This script automatically starts the user's session.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-09-26
 */

/**
 */

// PHP session settings
ini_set('session.save_handler', 'user');
ini_set('session.name', 'PHPSESSID');
//ini_set('session.gc_maxlifetime', K_SESSION_LIFE);
//ini_set('session.cookie_lifetime', K_COOKIE_EXPIRE);
ini_set('session.use_cookies', TRUE);

/**
 * Open session.
 * @param string $save_path path were to store session data
 * @param string $session_name name of session
 * @return bool always TRUE
 */
function F_session_open($save_path, $session_name) {
	return true; 
} 

/**
 * Close session.<br>
 * Call garbage collector function to remove expired sessions.
 * @return bool always TRUE
 */
function F_session_close() {
	F_session_gc(); //call garbage collector
	return true; 
} 

/**
 * Get session data.
 * @param string $key session ID.
 * @return string session data.
 */
function F_session_read($key) {
	global $db;
	$key = F_escape_sql($key);
	$sql = 'SELECT cpsession_data 
			FROM '.K_TABLE_SESSIONS.' 
			WHERE cpsession_id=\''.$key.'\' 
				AND cpsession_expiry>=\''.date(K_TIMESTAMP_FORMAT).'\' 
			LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			return $m['cpsession_data'];
		} else return('');
	}
	return('');
}

/**
 * Insert or Update session.
 * @param string $key session ID.
 * @param string $val session data.
 * @return resource database query result.
 */
function F_session_write($key, $val) {
	global $db;
	if ((!isset($db)) OR (!$db)) {
		// workaround for PHP bug 41230
			if(!$db = @F_db_connect(K_DATABASE_HOST, K_DATABASE_PORT,  K_DATABASE_USER_NAME, K_DATABASE_USER_PASSWORD, K_DATABASE_NAME)) {
				return;
			}
	}
	$key = F_escape_sql($key);
	$val = F_escape_sql($val);
	//$val = stripslashes($val);
	$expiry = date(K_TIMESTAMP_FORMAT);
	// check if this session already exist on database
	$sql = 'SELECT cpsession_id 
			FROM '.K_TABLE_SESSIONS.' 
			WHERE cpsession_id=\''.$key.'\' 
			LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
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
	}
	return F_db_query($sqlup, $db);
}

/**
 * Deletes the specific session.
 * @param string $key session ID of session to destroy.
 * @return resource database query result.
 */
function F_session_destroy($key) {
	global $db;
	$key = F_escape_sql($key);
	$sql = 'DELETE FROM '.K_TABLE_SESSIONS.' WHERE cpsession_id=\''.$key.'\'';
	return F_db_query($sql, $db);
}

/**
 * Garbage collector.<br>
 * Deletes expired sessions.<br>
 * NOTE: while time() function returns a 32 bit integer, it works fine until year 2038.
 * @param int $maxlife max session life time in seconds. The default value is defined by K_SESSION_LIFE costant on shared/config/tce_config.php file.
 * @return int number of deleted sessions.
 */
function F_session_gc($maxlife = K_SESSION_LIFE) {
	global $db;
	$expiry_time = date(K_TIMESTAMP_FORMAT, time() - $maxlife);
	$sql = 'DELETE FROM '.K_TABLE_SESSIONS.' WHERE cpsession_expiry<=\''.$expiry_time.'\'';
	if(!$r = F_db_query($sql, $db)) {
		return FALSE;
	}
	return F_db_affected_rows($db, $r);
}

/**
 * Convert encoded session string data to array.
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-10-18
 * @param string $sd input data string
 * @return array
 */
function F_session_string_to_array($sd) {
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

// ------------------------------------------------------------

// Sets user-level session storage functions.
session_set_save_handler('F_session_open', 'F_session_close', 'F_session_read', 'F_session_write', 'F_session_destroy', 'F_session_gc'); 

// start user session

if (isset($_REQUEST['PHPSESSID']) AND (strlen($_REQUEST['PHPSESSID'])>=32)) {
	//Load $PHPSESSID from get/post/cookie
	$PHPSESSID = substr($_REQUEST['PHPSESSID'],0,32);
} else {
	//create new PHPSESSID	
	$PHPSESSID = md5($_SERVER['REMOTE_ADDR'].''.time().'TCExam');
}

if ((!isset($_REQUEST['menu_mode'])) OR ($_REQUEST['menu_mode'] != 'startlongprocess')) { //temporary fix for flush problem for long processes
	session_id($PHPSESSID); //set session id
}

session_start(); //start session
header('Cache-control: private'); // fix IE6 bug

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
