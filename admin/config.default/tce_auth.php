<?php
//============================================================+
// File name   : tce_auth.php
// Begin       : 2002-09-02
// Last Update : 2013-04-12
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
//               Manor Coach House, Church Hill
//               Aldershot, Hants, GU12 4RQ
//               UK
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com LTD
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
define ('K_AUTH_ADMINISTRATOR', 10);

/**
 * Operator level.
 */
define ('K_AUTH_OPERATOR', 5);

/**
 * Required user's level to access index page.
 */
define ('K_AUTH_INDEX', K_AUTH_OPERATOR);

/**
 * Required user's level to access "user editor".
 */
define ('K_AUTH_ADMIN_USERS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to delete users.
 */
define ('K_AUTH_DELETE_USERS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to export users.
 */
define ('K_AUTH_EXPORT_USERS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to import users.
 */
define ('K_AUTH_IMPORT_USERS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to access "group editor".
 */
define ('K_AUTH_ADMIN_GROUPS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to delete user groups.
 */
define ('K_AUTH_DELETE_GROUPS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to move users from one group to another.
 */
define ('K_AUTH_MOVE_GROUPS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to access "tcecode editor".
 */
define ('K_AUTH_ADMIN_TCECODE', K_AUTH_OPERATOR);

/**
 * Required user's level to access "module editor".
 */
define ('K_AUTH_ADMIN_MODULES', K_AUTH_OPERATOR);

/**
 * Required user's level to access "subject editor".
 */
define ('K_AUTH_ADMIN_SUBJECTS', K_AUTH_OPERATOR);

/**
 * Required user's level to access "question editor".
 */
define ('K_AUTH_ADMIN_QUESTIONS', K_AUTH_OPERATOR);

/**
 * Required user's level to access "answer editor".
 */
define ('K_AUTH_ADMIN_ANSWERS', K_AUTH_OPERATOR);

/**
 * Required user's level to access "test editor".
 */
define ('K_AUTH_ADMIN_TESTS', K_AUTH_OPERATOR);

/**
 * Required user's level to access "TCExam information".
 */
define ('K_AUTH_ADMIN_INFO', 0);

/**
 * Required user's level to display online users.
 */
define ('K_AUTH_ADMIN_ONLINE_USERS', K_AUTH_OPERATOR);

/**
 * Required user's level to upload images.
 */
define ('K_AUTH_ADMIN_UPLOAD_IMAGES', K_AUTH_OPERATOR);

/**
 * Required user's level to manually rate free text answers.
 */
define ('K_AUTH_ADMIN_RATING', K_AUTH_OPERATOR);

/**
 * Required user's level to display results.
 */
define ('K_AUTH_ADMIN_RESULTS', K_AUTH_OPERATOR);

/**
 * Required user's level to import questions.
 */
define ('K_AUTH_ADMIN_IMPORT', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to import OMR answers.
 */
define ('K_AUTH_ADMIN_OMR_IMPORT', K_AUTH_OPERATOR);

/**
 * Required user's level to import questions.
 */
define ('K_AUTH_BACKUP', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to access file manager for multimedia files.
 */
define ('K_AUTH_ADMIN_FILEMANAGER', K_AUTH_OPERATOR);

/**
 * Required user's level to create and delete directories.
 */
define ('K_AUTH_ADMIN_DIRS', K_AUTH_OPERATOR);

/**
 * Required user's level to delete multimedia files.
 */
define ('K_AUTH_DELETE_MEDIAFILE', K_AUTH_OPERATOR);

/**
 * Required user's level to rename multimedia files.
 */
define ('K_AUTH_RENAME_MEDIAFILE', K_AUTH_OPERATOR);

//============================================================+
// END OF FILE
//============================================================+
