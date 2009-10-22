<?php
//============================================================+
// File name   : tce_db_config.php
// Begin       : 2001-09-02
// Last Update : 2009-10-09
// 
// Description : Database congiguration file.
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
 * Database congiguration file.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-09-02
 */

/**
 * database type (MYSQL, POSTGRESQL, ORACLE)
 */
define ('K_DATABASE_TYPE', '');

/**
 * database Host name (eg: localhost)
 */
define ('K_DATABASE_HOST', '');

/**
 * database port (eg: 3306, 5432, 1521)
 */
define ('K_DATABASE_PORT', '');

/**
 * database name (TCExam)
 */
define ('K_DATABASE_NAME', '');

/**
 * database user name
 */
define ('K_DATABASE_USER_NAME', '');

/**
 * database user password
 */
define ('K_DATABASE_USER_PASSWORD', '');

/**
 * prefix for database tables names
 */
define ('K_TABLE_PREFIX', 'tce_');

// --- database tables names (do not change) ---

/**
 */

define ('K_TABLE_SESSIONS', K_TABLE_PREFIX.'sessions');
define ('K_TABLE_USERS', K_TABLE_PREFIX.'users');
define ('K_TABLE_MODULES', K_TABLE_PREFIX.'modules');
define ('K_TABLE_SUBJECTS', K_TABLE_PREFIX.'subjects');
define ('K_TABLE_QUESTIONS', K_TABLE_PREFIX.'questions');
define ('K_TABLE_ANSWERS', K_TABLE_PREFIX.'answers');
define ('K_TABLE_TESTS', K_TABLE_PREFIX.'tests');
define ('K_TABLE_TEST_USER', K_TABLE_PREFIX.'tests_users');
define ('K_TABLE_TEST_SUBJSET', K_TABLE_PREFIX.'test_subject_set');
define ('K_TABLE_SUBJECT_SET', K_TABLE_PREFIX.'test_subjects');
define ('K_TABLE_TESTS_LOGS', K_TABLE_PREFIX.'tests_logs');
define ('K_TABLE_LOG_ANSWER', K_TABLE_PREFIX.'tests_logs_answers');
define ('K_TABLE_GROUPS', K_TABLE_PREFIX.'user_groups');
define ('K_TABLE_USERGROUP', K_TABLE_PREFIX.'usrgroups');
define ('K_TABLE_TEST_GROUPS', K_TABLE_PREFIX.'testgroups');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
