<?php
//============================================================+
// File name   : tce_cas.php
// Begin       : 2009-02-06
// Last Update : 2013-03-27
//
// Description : Configuration file for CAS
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
//    Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Configuration file for CAS (Central Authentication Service)
 * CAS is a centralize authentication service for the web where the user does not need to give their
 * login credentials (password) to the target website. It was developed at Yale and
 * is now maintained by the Java Architectures Special Interest Group and was sometimes
 * referred to as "Yale CAS" but now known as "JA-SIG CAS".
 * For more information see: http://www.ja-sig.org/products/cas/
 * WARNING: TCExam trusts CAS mechanism and replicates any authenticated user into the TCExam database. Passwords are set to the username string, therefore, TCExam authentication is not secure for replicated users if CAS Authentication is turned off again.
 * @package com.tecnick.tcexam.shared.cfg
 * @author Tim Gebhardt
 * @since 2009-02-06
 */

/**
 * If true enable CAS
 */
define('K_CAS_ENABLED', false);

/**
 * Version of CAS protocol to use.
 * Defaults to CAS_VERSION_2_0
 */
define('K_CAS_VERSION', '2.0');

/**
 * CAS server hostname.
 */
define('K_CAS_HOST', '');

/**
 * The port to connect to.
 * Defaults to 443
 */
define('K_CAS_PORT', 443);

/**
 * The location on the webserver where the CAS application is hosted.
 * Some setups don't place the CAS application in the webserver's root
 * directory and we can specify where to find the CAS application.
 * Default is ''.
 */
define('K_CAS_PATH', '/cas');

/**
 * Default user level
 */
define('K_CAS_USER_LEVEL', 1);

/**
 * Default user group ID
 * This is the TCExam group ID to which the CAS accounts belongs.
 * You can also set 0 for all available groups or a string containing a comma-separated list of group IDs.
 */
define('K_CAS_USER_GROUP_ID', 1);

//============================================================+
// END OF FILE
//============================================================+
