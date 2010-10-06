<?php
//============================================================+
// File name   : tce_auth.php
// Begin       : 2002-09-02
// Last Update : 2010-10-06
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
//               Tecnick.com S.r.l.
//               Via della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com S.r.l.
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
 * Configuration file: define access levels for each admin page.
 * @package com.tecnick.tcexam.admin.cfg
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
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
 * Required user's level to access index page.
 */
define ('K_AUTH_INDEX', 5);

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
define ('K_AUTH_ADMIN_TCECODE', 5);

/**
 * Required user's level to access "module editor".
 */
define ('K_AUTH_ADMIN_MODULES', 5);

/**
 * Required user's level to access "subject editor".
 */
define ('K_AUTH_ADMIN_SUBJECTS', 5);

/**
 * Required user's level to access "question editor".
 */
define ('K_AUTH_ADMIN_QUESTIONS', 5);

/**
 * Required user's level to access "answer editor".
 */
define ('K_AUTH_ADMIN_ANSWERS', 5);

/**
 * Required user's level to access "test editor".
 */
define ('K_AUTH_ADMIN_TESTS', 5);

/**
 * Required user's level to access "TCExam information".
 */
define ('K_AUTH_ADMIN_INFO', 0);

/**
 * Required user's level to display online users.
 */
define ('K_AUTH_ADMIN_ONLINE_USERS', 5);

/**
 * Required user's level to upload images.
 */
define ('K_AUTH_ADMIN_UPLOAD_IMAGES', 5);

/**
 * Required user's level to manually rate free text answers.
 */
define ('K_AUTH_ADMIN_RATING', 5);

/**
 * Required user's level to display results.
 */
define ('K_AUTH_ADMIN_RESULTS', 5);

/**
 * Required user's level to import questions.
 */
define ('K_AUTH_ADMIN_IMPORT', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to import questions.
 */
define ('K_AUTH_BACKUP', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to access file manager for multimedia files.
 */
define ('K_AUTH_ADMIN_FILEMANAGER', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to create and delete directories.
 */
define ('K_AUTH_ADMIN_DIRS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to delete multimedia files.
 */
define ('K_AUTH_DELETE_MEDIAFILE', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to rename multimedia files.
 */
define ('K_AUTH_RENAME_MEDIAFILE', K_AUTH_ADMINISTRATOR);

//============================================================+
// END OF FILE
//============================================================+
