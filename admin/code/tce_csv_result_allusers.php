<?php
//============================================================+
// File name   : tce_csv_result_allusers.php
// Begin       : 2006-03-30
// Last Update : 2009-09-30
//
// Description : Functions to export users' results using
//               CSV file format (tab delimited text).
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
 * Display all test results in CSV format.
 * (Tab Delimited Text File)
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2006-03-30
 */

/**
 */

if (isset($_REQUEST['testid']) AND ($_REQUEST['testid'] > 0)) {
	$test_id = intval($_REQUEST['testid']);
} else {
	exit;
}

if (isset($_REQUEST['groupid']) AND ($_REQUEST['groupid'] > 0)) {
	$group_id = intval($_REQUEST['groupid']);
} else {
	$group_id = 0;
}

if(!isset($_REQUEST['order_field']) OR empty($_REQUEST['order_field'])) {
	$order_field = 'total_score, user_lastname, user_firstname';
} else {
	$order_field = urldecode($_REQUEST['order_field']);
}

// send headers
header('Content-Description: TXT File Transfer');
header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
// force download dialog
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream', false);
header('Content-Type: application/download', false);
header('Content-Type: text/csv', false);
// use the Content-Disposition header to supply a recommended filename
header('Content-Disposition: attachment; filename=tcexam_test_results_'.$test_id.'_'.date('YmdHis').'.txt;');
header('Content-Transfer-Encoding: binary');

echo F_csv_export_result_allusers($test_id, $group_id, $order_field);

/**
 * Export all test results to CSV.
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2006-03-30
 * @param int $test_id Test ID
 * @param int $group_id Group ID
 * @param string $order_field ORDER BY portion of the SQL query
 * @return CSV data
 */
function F_csv_export_result_allusers($test_id, $group_id=0, $order_field="") {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_authorization.php');
	require_once('../../shared/code/tce_functions_test_stats.php');

	$test_id = intval($test_id);
	$group_id = intval($group_id);
	$order_field = F_escape_sql($order_field);

	$csv = ''; // CSV data to be returned

	// check user's authorization
	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		return $csv;
	}

	// print column names
	$csv .= '#';
	$csv .= K_TAB.$l['w_score'];
	$csv .= K_TAB.$l['w_lastname'];
	$csv .= K_TAB.$l['w_firstname'];
	$csv .= K_TAB.$l['w_user'];
	$csv .= K_TAB.$l['w_answers_right'];
	$csv .= K_TAB.$l['w_answers_wrong'];
	$csv .= K_TAB.$l['w_questions_unanswered'];
	$csv .= K_TAB.$l['w_questions_undisplayed'];
	$csv .= K_TAB.$l['w_questions_unrated'];
	$csv .= K_TAB.$l['w_comment'];

	// output users stats
	$sqlr = 'SELECT testuser_id, user_id, user_lastname, user_firstname, user_name, SUM(testlog_score) AS total_score
		FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_USERS.'
		WHERE testlog_testuser_id=testuser_id
			AND testuser_user_id=user_id
			AND testuser_test_id='.$test_id.'';
	if ($group_id > 0) {
		$sqlr .= ' AND testuser_user_id IN (
				SELECT usrgrp_user_id
				FROM '.K_TABLE_USERGROUP.'
				WHERE usrgrp_group_id='.$group_id.'
			)';
	}
	$sqlr .= ' GROUP BY testuser_id, user_id, user_lastname, user_firstname, user_name
		ORDER BY '.$order_field.'';
	if($rr = F_db_query($sqlr, $db)) {
		$itemcount = 1;
		while($mr = F_db_fetch_array($rr)) {
			$csv .= K_NEWLINE.$itemcount;
			$csv .= K_TAB.$mr['total_score'];
			$csv .= K_TAB.$mr['user_lastname'];
			$csv .= K_TAB.$mr['user_firstname'];
			$csv .= K_TAB.$mr['user_name'];
			$usrtestdata = F_getUserTestStat($test_id, $mr['user_id']);
			$csv .= K_TAB.$usrtestdata['right'];
			$csv .= K_TAB.$usrtestdata['wrong'];
			$csv .= K_TAB.$usrtestdata['unanswered'];
			$csv .= K_TAB.$usrtestdata['undisplayed'];
			$csv .= K_TAB.$usrtestdata['unrated'];
			$csv .= K_TAB.F_compact_string(htmlspecialchars($usrtestdata['comment'], ENT_NOQUOTES, $l['a_meta_charset']));
			$itemcount++;
		}
	} else {
		F_display_db_error();
	}

	return $csv;
}

//============================================================+
// END OF FILE
//============================================================+
