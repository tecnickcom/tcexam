<?php
//============================================================+
// File name   : tce_functions_auth_sql.php
// Begin       : 2006-03-11
// Last Update : 2012-12-19
//
// Description : Functions to select topics.
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
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Functions to select topics.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2006-03-11
 */

/**
 * Returns a SQL string to select modules accounting for user authorizations.
 * @author Nicola Asuni
 * @since 2010-06-16
 * @param $andwhere (string) additional WHERE statements (e.g.: "module_enabled='1'")
 * @return string sql statement
 */
function F_select_modules_sql($andwhere = '')
{
    global $l;
    require_once('../config/tce_config.php');
    $sql = 'SELECT * FROM '.K_TABLE_MODULES.'';
    if ($_SESSION['session_user_level'] >= K_AUTH_ADMINISTRATOR) {
        if (!empty($andwhere)) {
            $sql .= ' WHERE '.$andwhere;
        }
    } else {
        $sql .= ' WHERE module_user_id IN ('.F_getAuthorizedUsers($_SESSION['session_user_id']).')';
        if (!empty($andwhere)) {
            $sql .= ' AND '.$andwhere;
        }
    }
    $sql .= ' ORDER BY module_name';
    return $sql;
}

/**
 * Returns a SQL string to select subjects accounting for user authorizations.
 * @author Nicola Asuni
 * @since 2006-03-12
 * @param $andwhere (string) additional WHERE statements (e.g.: "subject_enabled='1'")
 * @return string sql statement
 */
function F_select_subjects_sql($andwhere = '')
{
    return F_select_module_subjects_sql($andwhere);
}

/**
 * Returns a SQL string to select modules and subjects accounting for user authorizations.
 * @author Nicola Asuni
 * @since 2008-11-28
 * @param $andwhere (string) additional WHERE statements (e.g.: "subject_enabled='1'")
 * @return string sql statement
 */
function F_select_module_subjects_sql($andwhere = '')
{
    global $l;
    require_once('../config/tce_config.php');
    $sql = 'SELECT * FROM '.K_TABLE_MODULES.','.K_TABLE_SUBJECTS.'';
    $sql .= ' WHERE module_id=subject_module_id';
    if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
        $authorized_users = F_getAuthorizedUsers($_SESSION['session_user_id']);
        $sql .= ' AND (module_user_id IN ('.$authorized_users.') OR subject_user_id IN ('.$authorized_users.'))';
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
 * @since 2006-03-12
 * @return string sql statement
 */
function F_select_tests_sql()
{
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
 * @since 2006-06-26
 * @return string sql statement
 */
function F_select_executed_tests_sql()
{
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
