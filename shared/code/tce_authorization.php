<?php
//============================================================+
// File name   : tce_authorization.php
// Begin       : 2001-09-26
// Last Update : 2009-10-10
//
// Description : Check user authorization level.
//               Grants / deny access to pages.
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com S.r.l.
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
 * This script handles user's sessions.
 * Just the registered users granted with a username and a password are entitled to access the restricted areas (level > 0) of TCExam and the public area to perform the tests.
 * The user's level is a numeric value that indicates which resources (pages, modules, services) are accessible by the user.
 * To gain access to a specific resource, the user's level must be equal or greater to the one specified for the requested resource.
 * TCExam has 10 predefined user's levels:<ul>
 * <li>0 = anonymous user (unregistered).</li>
 * <li>1 = basic user (registered);</li>
 * <li>2-9 = configurable/custom levels;</li>
 * <li>10 = administrator with full access rights</li>
 * </ul>
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-09-26
 */

/**
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_functions_authorization.php');
require_once('../../shared/code/tce_functions_session.php');

$logged = FALSE; // the user is not yet logged in
$PHPSESSIDSQL = F_escape_sql($PHPSESSID);

// --- read existing user's session data from database
$sqls = 'SELECT * FROM '.K_TABLE_SESSIONS.' WHERE cpsession_id=\''.$PHPSESSIDSQL.'\'';
if($rs = F_db_query($sqls, $db)) {
	if($ms = F_db_fetch_array($rs)) { // the user's session already exist
		session_decode($ms['cpsession_data']); //decode session data
		$expiry = date(K_TIMESTAMP_FORMAT); // update session expiration time
		$sqlx = 'UPDATE '.K_TABLE_SESSIONS.' SET cpsession_expiry=\''.$expiry.'\' WHERE cpsession_id=\''.$PHPSESSIDSQL.'\'';
		if(!$rx = F_db_query($sqlx, $db)) {
			F_display_db_error();
		}
	} else { // session do not exist so, create new anonymous session
		$_SESSION['session_user_id'] = 1;
		$_SESSION['session_user_name'] = '- ['.substr($PHPSESSID, 12, 8).']';
		$_SESSION['session_user_ip'] = getNormalizedIP($_SERVER['REMOTE_ADDR']);
		$_SESSION['session_user_level'] = 0;
		$_SESSION['session_user_firstname'] = '';
		$_SESSION['session_user_lastname'] = '';
		// read client cookie 
		if(isset($_COOKIE['LastVisit'])) {
			$_SESSION['session_last_visit'] = intval($_COOKIE['LastVisit']);
		} else {
			$_SESSION['session_last_visit'] = 0;
		}
		// set client cookie
		$cookie_now_time = time(); // note: while time() function returns a 32 bit integer, it works fine until year 2038.
		$cookie_expire_time = $cookie_now_time + K_COOKIE_EXPIRE; // set cookie expiration time
		setcookie('LastVisit', $cookie_now_time, $cookie_expire_time, K_COOKIE_PATH, K_COOKIE_DOMAIN, K_COOKIE_SECURE);
		setcookie('PHPSESSID', $PHPSESSID, $cookie_expire_time, K_COOKIE_PATH, K_COOKIE_DOMAIN, K_COOKIE_SECURE);
	}
} else {
	F_display_db_error();
}

// --- check for single-sign-on authentications
require_once('../../shared/config/tce_cas.php');
if (K_CAS_ENABLED) {
	require_once('../../shared/cas/CAS.php');
	phpCAS::client(K_CAS_VERSION, K_CAS_HOST, K_CAS_PORT, K_CAS_PATH, false);
	phpCAS::setNoCasServerValidation();
	phpCAS::forceAuthentication();
	if($_SESSION['session_user_name'] != phpCAS::getUser()) { 
		$_POST['xuser_name'] = phpCAS::getUser();
		$_POST['xuser_password'] = phpCAS::getUser();
		$_POST['logaction'] = 'login';
	}
}

// --- check if login information has been submitted
if ((isset($_POST['logaction'])) AND ($_POST['logaction'] == 'login')) {
	$xuser_password = md5($_POST['xuser_password']); // one-way password encoding
	// check if submitted login information are correct
	$sql = 'SELECT * FROM '.K_TABLE_USERS.' WHERE user_name=\''.F_escape_sql($_POST['xuser_name']).'\' AND user_password=\''.$xuser_password.'\'';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			// sets some user's session data
			$_SESSION['session_user_id'] = $m['user_id'];
			$_SESSION['session_user_name'] = $m['user_name'];
			$_SESSION['session_user_ip'] = getNormalizedIP($_SERVER['REMOTE_ADDR']);
			$_SESSION['session_user_level'] = $m['user_level'];
			$_SESSION['session_user_firstname'] = urlencode($m['user_firstname']);
			$_SESSION['session_user_lastname'] = urlencode($m['user_lastname']);
			// read client cookie 
			if(isset($_COOKIE['LastVisit'])) {
				$_SESSION['session_last_visit'] = intval($_COOKIE['LastVisit']);
			} else {
				$_SESSION['session_last_visit'] = 0;
			}
			$logged=TRUE;
		} elseif(!F_check_unique(K_TABLE_USERS, 'user_name=\''.F_escape_sql($_POST['xuser_name']).'\'')) {
				// the user name exist but the password is wrong
				F_print_error('WARNING', $l['m_login_wrong']);
		} else {
			// try to get account information from alternative systems (RADIUS, LDAP, CAS, ...)
			require_once('../../shared/code/tce_altauth.php');
			if(($altusr = F_altLogin($_POST['xuser_name'], $_POST['xuser_password'])) !== false) {
				// replicate user account on TCExam local database
				$sql = 'INSERT INTO '.K_TABLE_USERS.' (
					user_regdate,
					user_ip,
					user_name,
					user_email,
					user_password,
					user_regnumber,
					user_firstname,
					user_lastname,
					user_birthdate,
					user_birthplace,
					user_ssn,
					user_level
					) VALUES (
					\''.F_escape_sql(date(K_TIMESTAMP_FORMAT)).'\',
					\''.F_escape_sql(getNormalizedIP($_SERVER['REMOTE_ADDR'])).'\',
					\''.F_escape_sql($_POST['xuser_name']).'\',
					'.F_empty_to_null(F_escape_sql($altusr['user_email'])).',
					\''.md5($_POST['xuser_password']).'\',
					'.F_empty_to_null(F_escape_sql($altusr['user_regnumber'])).',
					'.F_empty_to_null(F_escape_sql($altusr['user_firstname'])).',
					'.F_empty_to_null(F_escape_sql($altusr['user_lastname'])).',
					'.F_empty_to_null(F_escape_sql($altusr['user_birthdate'])).',
					'.F_empty_to_null(F_escape_sql($altusr['user_birthplace'])).',
					'.F_empty_to_null(F_escape_sql($altusr['user_ssn'])).', 
					\''.F_escape_sql($altusr['user_level']).'\'
					)';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error();
				} else {
					$user_id = F_db_insert_id($db, K_TABLE_USERS, 'user_id');
					// add user to defaul tuser group
					$sql = 'INSERT INTO '.K_TABLE_USERGROUP.' (
						usrgrp_user_id,
						usrgrp_group_id
						) VALUES (
						\''.$user_id.'\', 
						\''.F_escape_sql($altusr['usrgrp_group_id']).'\'
						)';
					if(!$r = F_db_query($sql, $db)) {
						F_display_db_error();
					}
					// sets some user's session data
					$_SESSION['session_user_id'] = $user_id;
					$_SESSION['session_user_name'] = F_escape_sql($_POST['xuser_name']);
					$_SESSION['session_user_ip'] = getNormalizedIP($_SERVER['REMOTE_ADDR']);
					$_SESSION['session_user_level'] = $altusr['user_level'];
					$_SESSION['session_user_firstname'] = urlencode($altusr['user_firstname']);
					$_SESSION['session_user_lastname'] = urlencode($altusr['user_lastname']);
					$_SESSION['session_last_visit'] = 0;
					$logged = true;
				}
			} else {
				F_print_error('WARNING', $l['m_login_wrong']);
			}
		}
	} else {
		F_display_db_error();
	}
}

if (!isset($pagelevel)) {
	// set default page level
	$pagelevel = 0;
}

// check user's level
if($pagelevel) { // pagelevel=0 means access to anonymous user
	// pagelevel >= 1
	if($_SESSION['session_user_level'] < $pagelevel) { //check user level
		// To gain access to a specific resource, the user's level must be equal or greater to the one specified for the requested resource.
		F_login_form(); //display login form
	}
}

if($logged) { //if user is just logged in: reloads page
	switch(K_REDIRECT_LOGIN_MODE) {
		case 1: {
			// relative redirect
			header('Location: '.$_SERVER['SCRIPT_NAME']);
			break;
		}
		case 2: {
			// absolute redirect
			header('Location: '.K_PATH_HOST.$_SERVER['SCRIPT_NAME']);
			break;
		}
		case 3:
		default: {
			// html redirect
			echo '<'.'?xml version="1.0" encoding="'.$l['a_meta_charset'].'"?'.'>'.K_NEWLINE;
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.K_NEWLINE;
			echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$l['a_meta_language'].'" lang="'.$l['a_meta_language'].'" dir="'.$l['a_meta_dir'].'">'.K_NEWLINE;
			echo '<head>'.K_NEWLINE;
			echo '<title>ENTER</title>'.K_NEWLINE;
			//reload page
			echo '<meta http-equiv="refresh" content="0" />'.K_NEWLINE; //reload page
			echo '</head>'.K_NEWLINE;
			echo '<body>'.K_NEWLINE;
			echo '<a href="'.$_SERVER['SCRIPT_NAME'].'">ENTER</a>'.K_NEWLINE;
			echo '</body>'.K_NEWLINE;
			echo '</html>'.K_NEWLINE;
			break;
		}
	}
	exit;
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
