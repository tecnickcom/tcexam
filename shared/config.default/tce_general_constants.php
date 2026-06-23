<?php

//============================================================+
// File name   : tce_general_constants.php
// Begin       : 2002-03-01
// Last Update : 2023-11-30
//
// Description : Configuration file for general constants.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Configuration file for general constants.
 * @package com.tecnick.tcexam.shared.cfg
 * @author Nicola Asuni
 * @since 2002-03-01
 */

/**
 * String used as a seed for some security code generation.
 * SECURITY: this value MUST be unique and secret for every installation. The installer
 * (install/tce_functions_install.php) replaces the placeholder below with a random value.
 * If it is left at the shipped placeholder (or the historical default), the security
 * sensitive result-access token check fails closed - see F_isRandomSecurityConfigured().
 */
define('K_RANDOM_SECURITY', 'CHANGE_THIS_K_RANDOM_SECURITY');

/**
 * Maximum number of tests per year (last 365 days).
 * false = unlimited
 */
define('K_MAX_TESTS_YEAR', false);

/**
 * Maximum number of tests per month (last 30 days).
 * false = unlimited
 */
define('K_MAX_TESTS_MONTH', false);

/**
 * Maximum number of tests per day (last 24 hours).
 * false = unlimited
 */
define('K_MAX_TESTS_DAY', false);

/**
 * Set to false to disable test counting.
 */
define('K_REMAINING_TESTS', false);

// ---------------------------------------------------------------------
// DO NOT ALTER THE FOLLOWING CONSTANTS
// ---------------------------------------------------------------------

/**
 * New line character.
 */
define('K_NEWLINE', "\n");

/**
 * Tabulation character.
 */
define('K_TAB', "\t");

/**
 * Number of seconds in one minute.
 */
define('K_SECONDS_IN_MINUTE', 60);

/**
 * Number of seconds in one hour.
 */
define('K_SECONDS_IN_HOUR', 60 * K_SECONDS_IN_MINUTE);

/**
 * Number of seconds in one day.
 */
define('K_SECONDS_IN_DAY', 24 * K_SECONDS_IN_HOUR);

/**
 * Number of seconds in one week.
 */
define('K_SECONDS_IN_WEEK', 7 * K_SECONDS_IN_DAY);

/**
 * Number of seconds in one month.
 */
define('K_SECONDS_IN_MONTH', 30 * K_SECONDS_IN_DAY);

/**
 * Number of seconds in one year.
 */
define('K_SECONDS_IN_YEAR', 365 * K_SECONDS_IN_DAY);

/**
 * String used for security feature, do not alter.
 */
define('K_KEY_SECURITY', 'VENFeGFtIChjKSAyMDA0LTIwMjAgTmljb2xhIEFzdW5pIC0gVGVjbmljay5jb20gLSB0Y2V4YW0uY29t');
