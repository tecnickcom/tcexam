<?php
//============================================================+
// File name   : tce_functions_auth_sql.php
// Begin       : 2006-03-11
// Last Update : 2009-02-12
// 
// Description : Functions to select topics.
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
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//    
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//     
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Functions to select topics.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2006-03-11
 */

/**
 * Returns a SQL string to select subjects accounting for user authorizations.
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2006-03-12
 * @param string $andwhere additional WHERE statements (e.g.: "subject_enabled='1'")
 * @return string sql statement
 */
function F_select_subjects_sql($andwhere='') {
	global $l;
	require_once('../config/tce_config.php');
	$sql = 'SELECT * FROM '.K_TABLE_SUBJECTS.'';
	if ($_SESSION['session_user_level'] >= K_AUTH_ADMINISTRATOR) {
		if (!empty($andwhere)) {
			$sql .= ' WHERE '.$andwhere;
		}
	} else {
		$sql .= ' WHERE subject_user_id IN ('.F_getAuthorizedUsers($_SESSION['session_user_id']).')';
		if (!empty($andwhere)) {
			$sql .= ' AND '.$andwhere;
		}
	}
	$sql .= ' ORDER BY subject_name';
	return $sql;
}

/**
 * Returns a SQL string to select modules and subjects accounting for user authorizations.
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2008-11-28
 * @param string $andwhere additional WHERE statements (e.g.: "subject_enabled='1'")
 * @return string sql statement
 */
function F_select_module_subjects_sql($andwhere='') {
	global $l;
	require_once('../config/tce_config.php');
	$sql = 'SELECT * FROM '.K_TABLE_MODULES.','.K_TABLE_SUBJECTS.'';
	$sql .= ' WHERE module_id=subject_module_id';
	if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
		$sql .= ' AND subject_user_id IN ('.F_getAuthorizedUsers($_SESSION['session_user_id']).')';	
	}
	if (!empty($andwhere)) {
		$sql .= ' AND '.$andwhere;
	}
	$sql .= ' ORDER BY module_name,subject_name';
	return $sql;
}

/**
 * Returns a SQL string to select tests accounting for user authorizations.
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2006-03-12
 * @return string sql statement
 */
function F_select_tests_sql() {
	global $l;
	require_once('../config/tce_config.php');
	$sql = 'SELECT * FROM '.K_TABLE_TESTS.'';
	if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
		$sql .= ' WHERE test_user_id IN ('.F_getAuthorizedUsers($_SESSION['session_user_id']).')';
	}	
	$sql .= ' ORDER BY test_begin_time DESC, test_name';
	return $sql;
}

/**
 * Returns a SQL string to select executed tests accounting for user authorizations.
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2006-06-26
 * @return string sql statement
 */
function F_select_executed_tests_sql() {
	global $l;
	require_once('../config/tce_config.php');
	$sql = 'SELECT *
		FROM '.K_TABLE_TESTS.'
		WHERE test_id IN (
			SELECT testuser_test_id
			FROM '.K_TABLE_TEST_USER.' 
			WHERE testuser_status>0
		)';
	if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
		$sql .= ' AND test_user_id IN ('.F_getAuthorizedUsers($_SESSION['session_user_id']).')';
	}
	$sql .= ' ORDER BY test_begin_time DESC, test_name';
	return $sql;
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
