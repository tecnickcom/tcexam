<?php
//============================================================+
// File name   : tce_auth.php
// Begin       : 2010-09-17
// Last Update : 2013-07-05
//
// Description : Define access levels for each public page
//               Note:
//                0 = Anonymous user (uregistered user)
//                1 = registered user
//               10 = System Administrator
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com LTD
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
 * Configuration file: define access levels for each public page.
 * @package com.tecnick.tcexam.public.cfg
 * @author Nicola Asuni
 * @since 2010-09-17
 */

// ************************************************************
// SECURITY WARNING :
// SET THIS FILE AS READ ONLY AFTER MODIFICATIONS
// ************************************************************


/**
 * Required user's level to access index page.
 */
define ('K_AUTH_PUBLIC_INDEX', 1);

/**
 * Required user's level to execute a test
 */
define ('K_AUTH_PUBLIC_TEST_EXECUTE', 1);

/**
 * Required user's level to view test general information
 */
define ('K_AUTH_PUBLIC_TEST_INFO', 1);

/**
 * Required user's level to view test results
 */
define ('K_AUTH_PUBLIC_TEST_RESULTS', 1);

/**
 * Required user's level to access user page submenu
 */
define ('K_AUTH_PAGE_USER', 1);

/**
 * Required user's level to change email
 */
define ('K_AUTH_USER_CHANGE_EMAIL', 1);

/**
 * Required user's level to change password
 */
define ('K_AUTH_USER_CHANGE_PASSWORD', 1);

/**
 * Required user's level to display a link for administration area
 */
define ('K_ADMIN_LINK', 5);

/**
 * Minimum page level for which a valid client SSL certificate is required.
 * Use false or a level above 10 to disable the control.
 * Use 0 to enable for all area.
 * Use 10 to enable just for the ADMIN pages.
 */
define ('K_AUTH_SSL_LEVEL', false);

/**
 * Comma separated lit of SSL certificates IDs required to
 * access pages with K_AUTH_SSL_LEVEL level or more.
 */
define ('K_AUTH_SSLIDS', '');

//============================================================+
// END OF FILE
//============================================================+
