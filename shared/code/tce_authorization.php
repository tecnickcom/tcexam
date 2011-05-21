<?php
//============================================================+
// File name   : tce_authorization.php
// Begin       : 2001-09-26
// Last Update : 2011-05-21
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
//    Copyright (C) 2004-2011  Nicola Asuni - Tecnick.com S.r.l.
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
//    Additionally, you can't remove, move or hide the original TCExam logo,
//    copyrights statements and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
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
 * @brief TCExam Shared Area
 * @author Nicola Asuni
 * @since 2001-09-26
 */

/**
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_functions_authorization.php');
require_once('../../shared/code/tce_functions_session.php');

$logged = false; // the user is not yet logged in

// --- read existing user's session data from database
$PHPSESSIDSQL = F_escape_sql($PHPSESSID);
$session_hash = md5($PHPSESSID.getClientFingerprint());
$sqls = 'SELECT * FROM '.K_TABLE_SESSIONS.' WHERE cpsession_id=\''.$PHPSESSIDSQL.'\'';
if ($rs = F_db_query($sqls, $db)) {
	if ($ms = F_db_fetch_array($rs)) { // the user's session already exist
		// decode session data
		session_decode($ms['cpsession_data']);
		// check for possible session hijacking
		if (K_CHECK_SESSION_FINGERPRINT AND ((!isset($_SESSION['session_hash'])) OR ($_SESSION['session_hash'] != $session_hash))) {
			// display login form
			session_regenerate_id();
			F_login_form();
			exit();
		}
		// update session expiration time
		$expiry = date(K_TIMESTAMP_FORMAT);
		$sqlx = 'UPDATE '.K_TABLE_SESSIONS.' SET cpsession_expiry=\''.$expiry.'\' WHERE cpsession_id=\''.$PHPSESSIDSQL.'\'';
		if (!$rx = F_db_query($sqlx, $db)) {
			F_display_db_error();
		}
	} else { // session do not exist so, create new anonymous session
		$_SESSION['session_hash'] = $session_hash;
		$_SESSION['session_user_id'] = 1;
		$_SESSION['session_user_name'] = '- ['.substr($PHPSESSID, 12, 8).']';
		$_SESSION['session_user_ip'] = getNormalizedIP($_SERVER['REMOTE_ADDR']);
		$_SESSION['session_user_level'] = 0;
		$_SESSION['session_user_firstname'] = '';
		$_SESSION['session_user_lastname'] = '';
		// read client cookie
		if (isset($_COOKIE['LastVisit'])) {
			$_SESSION['session_last_visit'] = intval($_COOKIE['LastVisit']);
		} else {
			$_SESSION['session_last_visit'] = 0;
		}
		// track when user request logout
		if (isset($_REQUEST['logout'])) {
			$_SESSION['logout'] = true;
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

// --- check for single-sign-on authentication
require_once('../../shared/config/tce_cas.php');
if (K_CAS_ENABLED) {
	require_once('../../shared/cas/CAS.php');
	phpCAS::client(K_CAS_VERSION, K_CAS_HOST, K_CAS_PORT, K_CAS_PATH, false);
	phpCAS::setNoCasServerValidation();
	phpCAS::forceAuthentication();
	if ($_SESSION['session_user_name'] != phpCAS::getUser()) {
		$_POST['xuser_name'] = phpCAS::getUser();
		$_POST['xuser_password'] = phpCAS::getUser();
		$_POST['logaction'] = 'login';
	}
}

// --- check for HTTP-Basic authentication
$http_basic_auth = false;
require_once('../../shared/config/tce_httpbasic.php');
if (K_HTTPBASIC_ENABLED AND (!isset($_SESSION['logout']) OR !$_SESSION['logout'])) {
	if (isset($_SERVER['AUTH_TYPE']) AND ($_SERVER['AUTH_TYPE'] == 'Basic')
		AND isset($_SERVER['PHP_AUTH_USER']) AND isset($_SERVER['PHP_AUTH_PW'])
		AND ($_SESSION['session_user_name'] != $_SERVER['PHP_AUTH_USER'])) {
		$_POST['xuser_name'] = $_SERVER['PHP_AUTH_USER'];
		$_POST['xuser_password'] = $_SERVER['PHP_AUTH_PW'];
		$_POST['logaction'] = 'login';
		$http_basic_auth = true;
	}
}

// --- check if login information has been submitted
if ((isset($_POST['logaction'])) AND ($_POST['logaction'] == 'login')) {
	$xuser_password = md5($_POST['xuser_password']); // one-way password encoding
	// check if submitted login information are correct
	$sql = 'SELECT * FROM '.K_TABLE_USERS.' WHERE user_name=\''.F_escape_sql($_POST['xuser_name']).'\' AND user_password=\''.$xuser_password.'\'';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			// sets some user's session data
			$_SESSION['session_user_id'] = $m['user_id'];
			$_SESSION['session_user_name'] = $m['user_name'];
			$_SESSION['session_user_ip'] = getNormalizedIP($_SERVER['REMOTE_ADDR']);
			$_SESSION['session_user_level'] = $m['user_level'];
			$_SESSION['session_user_firstname'] = urlencode($m['user_firstname']);
			$_SESSION['session_user_lastname'] = urlencode($m['user_lastname']);
			// read client cookie
			if (isset($_COOKIE['LastVisit'])) {
				$_SESSION['session_last_visit'] = intval($_COOKIE['LastVisit']);
			} else {
				$_SESSION['session_last_visit'] = 0;
			}
			$logged = true;
		} elseif (!F_check_unique(K_TABLE_USERS, 'user_name=\''.F_escape_sql($_POST['xuser_name']).'\'')) {
				// the user name exist but the password is wrong
				if ($http_basic_auth) {
					// update the password in case of HTTP Basic Authentication
					$sqlu = 'UPDATE '.K_TABLE_USERS.' SET
						user_password=\''.$xuser_password.'\'
						WHERE user_name=\''.F_escape_sql($_POST['xuser_name']).'\'';
					if(!$ru = F_db_query($sqlu, $db)) {
						F_display_db_error();
					}
					// get user data
					$sqld = 'SELECT * FROM '.K_TABLE_USERS.' WHERE user_name=\''.F_escape_sql($_POST['xuser_name']).'\' AND user_password=\''.$xuser_password.'\'';
					if ($rd = F_db_query($sqld, $db)) {
						if ($md = F_db_fetch_array($rd)) {
							// sets some user's session data
							$_SESSION['session_user_id'] = $md['user_id'];
							$_SESSION['session_user_name'] = $md['user_name'];
							$_SESSION['session_user_ip'] = getNormalizedIP($_SERVER['REMOTE_ADDR']);
							$_SESSION['session_user_level'] = $md['user_level'];
							$_SESSION['session_user_firstname'] = urlencode($md['user_firstname']);
							$_SESSION['session_user_lastname'] = urlencode($md['user_lastname']);
							$_SESSION['session_last_visit'] = 0;
							$logged = true;
						}
					} else {
						F_display_db_error();
					}
				} else {
					// the password is wrong
					F_print_error('WARNING', $l['m_login_wrong']);
				}
		} else {
			// this user doesn't exist on TCExam database
			// try to get account information from alternative systems (RADIUS, LDAP, CAS, ...)
			require_once('../../shared/code/tce_altauth.php');
			$altusr = F_altLogin(stripslashes($_POST['xuser_name']), stripslashes($_POST['xuser_password']));
			if ($altusr !== false) {
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
					'.F_empty_to_null($altusr['user_email']).',
					\''.md5($_POST['xuser_password']).'\',
					'.F_empty_to_null($altusr['user_regnumber']).',
					'.F_empty_to_null($altusr['user_firstname']).',
					'.F_empty_to_null($altusr['user_lastname']).',
					'.F_empty_to_null($altusr['user_birthdate']).',
					'.F_empty_to_null($altusr['user_birthplace']).',
					'.F_empty_to_null($altusr['user_ssn']).',
					\''.F_escape_sql($altusr['user_level']).'\'
					)';
				if (!$r = F_db_query($sql, $db)) {
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
					if (!$r = F_db_query($sql, $db)) {
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
				//F_print_error('WARNING', $l['m_login_wrong']);
				$login_error = true;
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
if ($pagelevel) { // pagelevel=0 means access to anonymous user
	// pagelevel >= 1
	if ($_SESSION['session_user_level'] < $pagelevel) { //check user level
		// To gain access to a specific resource, the user's level must be equal or greater to the one specified for the requested resource.
		F_login_form(); //display login form
	}
}

if ($logged) { //if user is just logged in: reloads page
	// html redirect
	$htmlredir = '<'.'?xml version="1.0" encoding="'.$l['a_meta_charset'].'"?'.'>'.K_NEWLINE;
	$htmlredir .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.K_NEWLINE;
	$htmlredir .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$l['a_meta_language'].'" lang="'.$l['a_meta_language'].'" dir="'.$l['a_meta_dir'].'">'.K_NEWLINE;
	$htmlredir .= '<head>'.K_NEWLINE;
	$htmlredir .= '<title>ENTER</title>'.K_NEWLINE;
	$htmlredir .= '<meta http-equiv="refresh" content="0" />'.K_NEWLINE; //reload page
	$htmlredir .= '</head>'.K_NEWLINE;
	$htmlredir .= '<body>'.K_NEWLINE;
	$htmlredir .= '<a href="'.$_SERVER['SCRIPT_NAME'].'">ENTER</a>'.K_NEWLINE;
	$htmlredir .= '</body>'.K_NEWLINE;
	$htmlredir .= '</html>'.K_NEWLINE;
	switch (K_REDIRECT_LOGIN_MODE) {
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
		case 3: {
			// html redirect
			echo $htmlredir;
			break;
		}
		case 4:
		default: {
			// full redirect
			header('Location: '.K_PATH_HOST.$_SERVER['SCRIPT_NAME']);
			echo $htmlredir;
			break;
		}
	}
	exit;
}

//============================================================+
// END OF FILE
//============================================================+
