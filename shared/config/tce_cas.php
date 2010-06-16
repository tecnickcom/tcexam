<?php
//============================================================+
// File name   : tce_cas.php
// Begin       : 2009-02-06
// Last Update : 2009-09-30
//
// Description : Configuration file for CAS
//
// Author: Tim Gebhardt
//
// (c) Copyright:
//               Tim Gebhardt
//               DePaul University
//               tgebhar@cdm.depaul.edu
//
// License:
//    Copyright (C) 2009   Tim Gebhardt - DePaul University
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
 * Configuration file for CAS (Central Authentication Service)
 * CAS is a centralize authentication service for the web where the user does not need to give their
 * login credentials (password) to the target website. It was developed at Yale and
 * is now maintained by the Java Architectures Special Interest Group and was sometimes
 * referred to as "Yale CAS" but now known as "JA-SIG CAS".
 * For more information see: http://www.ja-sig.org/products/cas/
 * @package com.tecnick.tcexam.shared.cfg
 * @author Tim Gebhardt
 * @copyright Copyright © 2009, Tim Gebhardt - DePaul University - tgebhar@cdm.depaul.edu
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.cdm.depaul.edu
 * @since 2009-02-06
 */

/**
 * If true enable CAS
 */
define ('K_CAS_ENABLED', false);

/**
 * Version of CAS protocol to use.
 * Defaults to CAS_VERSION_2_0
 */
define ('K_CAS_VERSION', '2.0');

/**
 * CAS server hostname.
 */
define ('K_CAS_HOST', '');

/**
 * The port to connect to.
 * Defaults to 443
 */
define ('K_CAS_PORT', 443);

/**
 * The location on the webserver where the CAS application is hosted.
 * Some setups don't place the CAS application in the webserver's root
 * directory and we can specify where to find the CAS application.
 * Default is ''.
 */
define ('K_CAS_PATH', '/cas');

/**
 * Default user level
 */
define ('K_CAS_USER_LEVEL', 1);

/**
 * Default user group ID
 * This is the TCExam group id to which the CAS accounts belongs.
 */
define ('K_CAS_USER_GROUP_ID', 1);

//============================================================+
// END OF FILE
//============================================================+
?>
