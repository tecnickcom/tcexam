<?php
//============================================================+
// File name   : tce_ldap.php
// Begin       : 2008-03-28
// Last Update : 2009-10-10
//
// Description : Configuration file for LDAP
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
 * Configuration file for LDAP
 * LDAP is the Lightweight Directory Access Protocol, and is a protocol used to access "Directory Servers". 
 * The Directory is a special kind of database that holds information in a tree structure.
 * Check http://www.php.net/manual/en/ref.ldap.php for requirements and installation.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2008-03-28
 */

/**
 * If true enable LDAP
 */
define ('K_LDAP_ENABLED', false);

/**
 * LDAP hostname.
 * If you are using OpenLDAP 2.x.x you can specify a URL instead of the hostname. 
 * To use LDAP with SSL, compile OpenLDAP 2.x.x with SSL support, configure PHP with SSL, and set this parameter as ldaps://hostname/.
 */
define ('K_LDAP_HOST', 'localhost');

/**
 * The port to connect to. Not used when using URLs. 
 * Defaults to 389.
 */
define ('K_LDAP_PORT', 389);

/**
 * LDAP protocol version.
 * Defaults to 3
 */
define ('K_LDAP_PROTOCOL_VERSION', 3);

/**
 * The base DN for the directory.
 */
define ('K_LDAP_BASE_DN', 'ou=users,dc=mydom,dc=example,dc=org');

/**
 * The search filter can be simple or advanced, using boolean operators in the format described in the LDAP documentation.
 * Use #USERNAME# as a placeholder for the username passed by login form
 * For W2K3 use: "(sAMAccountName=#USERNAME#)"
 */
define ('K_LDAP_FILTER', 'uid=#USERNAME#');

/**
 * Array of the required attributes.
 * This array maps TCExam user data with LDAP attributes.
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
define ('K_LDAP_UTF8', true);

/**
 * Default user level
 */
define ('K_LDAP_USER_LEVEL', 1);

/**
 * Default user group ID
 * This is the TCExam group id to which the ldap accounts belongs.
 */
define ('K_LDAP_USER_GROUP_ID', 1);

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
