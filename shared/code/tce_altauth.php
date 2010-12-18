<?php
//============================================================+
// File name   : tce_altauth.php
// Begin       : 2008-03-28
// Last Update : 2010-09-16
//
// Description : Check user authorization against alternative
//               systems (RADIUS, LDAP, CAS, HTTP BASIC, ...)
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
//    Additionally, you can't remove, move or hide the original TCExam logo,
//    copyrights statements and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Check user authorization against alternative systems (RADIUS, LDAP, CAS, HTTP BASIC, ...)
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2008-03-28
 */

/**
 * Alternate Login.
 * Try to login user using alternative systems (Radius, LDAP, CAS, HTTP BASIC, ...)
 * @param $username (string) user name
 * @param $password (string) password
 * @return array of user's data for successful login, false otherwise
 * @since 2008-03-28
 */
function F_altLogin($username, $password) {
	global $l, $db;
	require_once('../config/tce_config.php');

	// TCExam tries to retrive the user login information from the following systems:

	// 1) RADIUS ---------------------------------------------
	require_once('../../shared/config/tce_radius.php');
	if (K_RADIUS_ENABLED) {
		require_once('../../shared/radius/radius.class.php');
		$radius = new Radius(K_RADIUS_SERVER_IP, K_RADIUS_SHARED_SECRET, K_RADIUS_SUFFIX, K_RADIUS_UDP_TIMEOUT, K_RADIUS_AUTHENTICATION_PORT, K_RADIUS_ACCOUNTING_PORT);
		if (K_RADIUS_UTF8) {
				$radusername = utf8_encode($username);
				$radpassword = utf8_encode($password);
			} else {
				$radusername = $username;
				$radpassword = $password;
			}
		if($radius->AccessRequest($radusername, $radpassword)) {
			$usr = array();
			$usr['user_email'] = '';
			$usr['user_firstname'] = '';
			$usr['user_lastname'] = '';
			$usr['user_birthdate'] = '';
			$usr['user_birthplace'] = '';
			$usr['user_regnumber'] = '';
			$usr['user_ssn'] = '';
			$usr['user_level'] = K_RADIUS_USER_LEVEL;
			$usr['usrgrp_group_id'] = K_RADIUS_USER_GROUP_ID;
			return $usr;
		}
	}
	// -------------------------------------------------------

	// 2) LDAP -----------------------------------------------
	require_once('../../shared/config/tce_ldap.php');
	if (K_LDAP_ENABLED) {
		// make ldap connection
		$ldapconn = ldap_connect(K_LDAP_HOST, K_LDAP_PORT);
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, K_LDAP_PROTOCOL_VERSION);
		ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0); // recommended for W2K3
		// bind anonymously and get dn for username.
		if (K_LDAP_UTF8) {
			$ldapusername = utf8_encode($username);
			$ldappassword = utf8_encode($password);
		} else {
			$ldapusername = $username;
			$ldappassword = $password;
		}
		if ($lbind = ldap_bind($ldapconn, $ldapusername, $ldappassword)) {
			// Search user on LDAP tree
			sort($ldap_attr);
			$ldap_filter = str_replace('#USERNAME#', $ldapusername, K_LDAP_FILTER);
			if ($search = @ldap_search($ldapconn, K_LDAP_BASE_DN, $ldap_filter, $ldap_attr)) {
				if ($rdn = @ldap_get_entries($ldapconn, $search)) {
					if (@ldap_bind($ldapconn, $rdn['dn'], $password)) {
						@ldap_unbind($ldapconn);
						$usr = array();
						foreach ($ldap_attr as $k => $v) {
							if ((!empty($v)) AND isset($rdn[$v])) {
								$usr[$k] = $rdn[$v];
							} else {
								$usr[$k] = '';
							}
						}
						$usr['user_level'] = K_LDAP_USER_LEVEL;
						$usr['usrgrp_group_id'] = K_LDAP_USER_GROUP_ID;
						return $usr;
					}
				}
			}
		}
		@ldap_unbind($ldapconn);
	}
	// -------------------------------------------------------

	// 3) CAS ------------------------------------------------
	require_once('../../shared/config/tce_cas.php');
	if (K_CAS_ENABLED) {
		require_once('../../shared/cas/CAS.php');
		$usr = array();
		$usr['user_email'] = '';
		$usr['user_firstname'] = '';
		$usr['user_lastname'] = '';
		$usr['user_birthdate'] = '';
		$usr['user_birthplace'] = '';
		$usr['user_regnumber'] = '';
		$usr['user_ssn'] = '';
		$usr['user_level'] = K_CAS_USER_LEVEL;
		$usr['usrgrp_group_id'] = K_CAS_USER_GROUP_ID;
		return $usr;
	}
	// -------------------------------------------------------

	// 4) HTTP BASIC -----------------------------------------
	require_once('../../shared/config/tce_httpbasic.php');
	if (K_HTTPBASIC_ENABLED
		AND isset($_SERVER['AUTH_TYPE']) AND ($_SERVER['AUTH_TYPE'] == 'Basic')
		AND isset($_SERVER['PHP_AUTH_USER']) AND isset($_SERVER['PHP_AUTH_PW'])) {
		$usr = array();
		$usr['user_email'] = '';
		$usr['user_firstname'] = '';
		$usr['user_lastname'] = '';
		$usr['user_birthdate'] = '';
		$usr['user_birthplace'] = '';
		$usr['user_regnumber'] = '';
		$usr['user_ssn'] = '';
		$usr['user_level'] = K_HTTPBASIC_USER_LEVEL;
		$usr['usrgrp_group_id'] = K_HTTPBASIC_USER_GROUP_ID;
		return $usr;
	}
	// -------------------------------------------------------

	return false;
}

//============================================================+
// END OF FILE
//============================================================+
