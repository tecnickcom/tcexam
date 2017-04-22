<?php
//============================================================+
// File name   : tce_ldap.php
// Begin       : 2008-03-28
// Last Update : 2015-03-27
//
// Description : Configuration file for LDAP
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
//    Copyright (C) 2004-2015  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Configuration file for LDAP
 * LDAP is the Lightweight Directory Access Protocol, and is a protocol used to access "Directory Servers".
 * The Directory is a special kind of database that holds information in a tree structure.
 * Check http://www.php.net/manual/en/ref.ldap.php for requirements and installation.
 * @package com.tecnick.tcexam.shared.cfg
 * @author Nicola Asuni
 * @since 2008-03-28
 */

/**
 * If true enable LDAP.
 */
define('K_LDAP_ENABLED', false);

/**
 * LDAP hostname. If you are using OpenLDAP 2.x.x you can specify a URL instead of the hostname. To use LDAP with SSL, compile OpenLDAP 2.x.x with SSL support, configure PHP with SSL, and set this parameter as ldaps://hostname/.
 */
define('K_LDAP_HOST', 'localhost');

/**
 * The port to connect to. Not used when using URLs.
 */
define('K_LDAP_PORT', 389);

/**
 * LDAP protocol version.
 */
define('K_LDAP_PROTOCOL_VERSION', 3);

/**
 * The DN for the ROOT Account
 * Set to null for anonymous LDAP binding
 */
define('K_LDAP_ROOT_DN', null);

/**
 * The password for the ROOT Account
 * Set to null for anonymous LDAP binding
 */
define('K_LDAP_ROOT_PASS', null);

/**
 * The base DN for the directory.
 */
define('K_LDAP_BASE_DN', 'ou=users,dc=mydom,dc=example,dc=org');

/**
 * The search filter can be simple or advanced, using boolean operators in the format described in the LDAP documentation. Use "#USERNAME#" as a placeholder for the username passed by login form. For W2K3 use: "(sAMAccountName=#USERNAME#)"
 */
define('K_LDAP_FILTER', 'uid=#USERNAME#');

/**
 * Array of the required attributes. This array maps TCExam user data with LDAP attributes.
 */
$ldap_attr = array();
$ldap_attr['dn'] = 'dn';
$ldap_attr['user_email'] = 'mail';
$ldap_attr['user_firstname'] = 'givenName';
$ldap_attr['user_lastname'] = 'sn';
$ldap_attr['user_birthdate'] = '';
$ldap_attr['user_birthplace'] = '';
$ldap_attr['user_regnumber'] = '';
$ldap_attr['user_ssn'] = '';

/**
 * Set to true if LDAP uses UTF-8 encoding.
 */
define('K_LDAP_UTF8', true);

/**
 * Default user level
 */
define('K_LDAP_USER_LEVEL', 1);

/**
 * Default user group ID.
 * This is the TCExam group id to which the LDAP accounts belongs.
 * You can also set 0 for all available groups or a string containing a comma-separated list of group IDs.
 */
define('K_LDAP_USER_GROUP_ID', 1);

//============================================================+
// END OF FILE
//============================================================+
