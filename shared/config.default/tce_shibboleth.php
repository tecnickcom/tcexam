<?php
//============================================================+
// File name   : tce_shibboleth.php
// Begin       : 2012-05-25
// Last Update : 2013-01-20
//
// Description : Configuration file for Shibboleth Single-Sign-On Authentication
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
//    Copyright (C) 2012-2013 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Configuration file for Shibboleth Single-Sign-On Authentication
 * If support for Shibboleth Authentication is enabled, TCExam trusts this mechanism and replicates any authenticated user into the TCExam database.
 * @package com.tecnick.tcexam.shared.cfg
 * @author Nicola Asuni
 * @since 2012-05-25
 */

/**
 * If true trust Shibboleth Auth
 */
define('K_SHIBBOLETH_ENABLED', false);

/**
 * Default user level
 */
define('K_SHIBBOLETH_USER_LEVEL', 1);

/**
 * Default user group ID
 * This is the TCExam group id to which the accounts belong.
 * You can also set 0 for all available groups or a string containing a comma-separated list of group IDs.
 */
define('K_SHIBBOLETH_USER_GROUP_ID', 1);

/**
 * URL of the Shibboleth login page
 */
define('K_SHIBBOLETH_LOGIN', '');

//============================================================+
// END OF FILE
//============================================================+
