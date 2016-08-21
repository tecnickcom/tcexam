<?php
//============================================================+
// File name   : tce_auth.php
// Begin       : 2002-09-02
// Last Update : 2013-07-05
//
// Description : Define access levels for each admin page
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
//    Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Configuration file: define access levels for each admin page.
 * @package com.tecnick.tcexam.admin.cfg
 * @author Nicola Asuni
 * @since 2002-09-02
 */

// ************************************************************
// SECURITY WARNING :
// SET THIS FILE AS READ ONLY AFTER MODIFICATIONS
// ************************************************************

/**
 * Administrator level.
 */
define('K_AUTH_ADMINISTRATOR', 10);

/**
 * Operator level.
 */
define('K_AUTH_OPERATOR', 5);

/**
 * Required user's level to access index page.
 */
define('K_AUTH_INDEX', K_AUTH_OPERATOR);

/**
 * Required user's level to access "user editor".
 */
define('K_AUTH_ADMIN_USERS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to delete users.
 */
define('K_AUTH_DELETE_USERS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to export users.
 */
define('K_AUTH_EXPORT_USERS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to import users.
 */
define('K_AUTH_IMPORT_USERS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to access "group editor".
 */
define('K_AUTH_ADMIN_GROUPS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to delete user groups.
 */
define('K_AUTH_DELETE_GROUPS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to move users from one group to another.
 */
define('K_AUTH_MOVE_GROUPS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to access "tcecode editor".
 */
define('K_AUTH_ADMIN_TCECODE', K_AUTH_OPERATOR);

/**
 * Required user's level to access "module editor".
 */
define('K_AUTH_ADMIN_MODULES', K_AUTH_OPERATOR);

/**
 * Required user's level to access "subject editor".
 */
define('K_AUTH_ADMIN_SUBJECTS', K_AUTH_OPERATOR);

/**
 * Required user's level to access "question editor".
 */
define('K_AUTH_ADMIN_QUESTIONS', K_AUTH_OPERATOR);

/**
 * Required user's level to access "answer editor".
 */
define('K_AUTH_ADMIN_ANSWERS', K_AUTH_OPERATOR);

/**
 * Required user's level to access "test editor".
 */
define('K_AUTH_ADMIN_TESTS', K_AUTH_OPERATOR);

/**
 * Required user's level to access "TCExam information".
 */
define('K_AUTH_ADMIN_INFO', 0);

/**
 * Required user's level to display online users.
 */
define('K_AUTH_ADMIN_ONLINE_USERS', K_AUTH_OPERATOR);

/**
 * Required user's level to upload images.
 */
define('K_AUTH_ADMIN_UPLOAD_IMAGES', K_AUTH_OPERATOR);

/**
 * Required user's level to manually rate free text answers.
 */
define('K_AUTH_ADMIN_RATING', K_AUTH_OPERATOR);

/**
 * Required user's level to display results.
 */
define('K_AUTH_ADMIN_RESULTS', K_AUTH_OPERATOR);

/**
 * Required user's level to import questions.
 */
define('K_AUTH_ADMIN_IMPORT', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to import OMR answers.
 */
define('K_AUTH_ADMIN_OMR_IMPORT', K_AUTH_OPERATOR);

/**
 * Required user's level to import questions.
 */
define('K_AUTH_BACKUP', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to access file manager for multimedia files.
 */
define('K_AUTH_ADMIN_FILEMANAGER', K_AUTH_OPERATOR);

/**
 * Required user's level to create and delete directories.
 */
define('K_AUTH_ADMIN_DIRS', K_AUTH_OPERATOR);

/**
 * Required user's level to delete multimedia files.
 */
define('K_AUTH_DELETE_MEDIAFILE', K_AUTH_OPERATOR);

/**
 * Required user's level to rename multimedia files.
 */
define('K_AUTH_RENAME_MEDIAFILE', K_AUTH_OPERATOR);

/**
 * Required user's level to edit SSL certificates.
 */
define('K_AUTH_ADMIN_SSLCERT', K_AUTH_OPERATOR);

/**
 * Minimum page level for which a valid client SSL certificate is required.
 * Use false or a level above 10 to disable the control.
 * Use 0 to enable for all area.
 * Use 10 to enable just for the ADMIN pages.
 */
define('K_AUTH_SSL_LEVEL', false);

/**
 * Comma separated lit of SSL certificates IDs required to
 * access pages with K_AUTH_SSL_LEVEL level or more.
 */
define('K_AUTH_SSLIDS', '');

//============================================================+
// END OF FILE
//============================================================+
