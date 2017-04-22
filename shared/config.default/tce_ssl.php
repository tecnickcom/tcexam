<?php
//============================================================+
// File name   : tce_ssl.php
// Begin       : 2013-03-27
// Last Update : 2013-03-27
//
// Description : Configuration file for SSL Authentication
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
//    Copyright (C) 2013-2013 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Configuration file for SSL Authentication
 * If support for SSL Authentication is enabled, TCExam trusts this mechanism and replicates any authenticated user into the TCExam database.
 * @package com.tecnick.tcexam.shared.cfg
 * @author Nicola Asuni
 * @since 2013-03-27
 */

/**
 * If true trust SSL Auth
 */
define('K_SSL_ENABLED', false);

/**
 * Default user level
 */
define('K_SSL_USER_LEVEL', 1);

/**
 * Default user group ID
 * This is the TCExam group id to which the accounts belong.
 * You can also set 0 for all available groups or a string containing a comma-separated list of group IDs.
 */
define('K_SSL_USER_GROUP_ID', 1);

//============================================================+
// END OF FILE
//============================================================+
