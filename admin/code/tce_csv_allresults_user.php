<?php
//============================================================+
// File name   : tce_csv_allresults_user.php
// Begin       : 2008-12-26
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
 * Display all user's results in CSV format.
 * (Tab Delimited Text File)
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2008-12-26
 * @param int $_REQUEST['user_id'] user ID
 * @param int $_REQUEST['startdate'] start date
 * @param int $_REQUEST['enddate'] end date
 * @param string $_REQUEST['orderfield'] ORDER BY portion of SQL selection query
 */

/**
 */

if (isset($_REQUEST['user_id']) AND ($_REQUEST['user_id'] > 0)) {
	$user_id = intval($_REQUEST['user_id']);
} else {
	exit;
}
if (isset($_REQUEST['startdate']) AND ($_REQUEST['startdate'] > 0)) {
	$startdate = urldecode($_REQUEST['startdate']);
} else {
	$startdate = date('Y').'-01-01 00:00:00';
}
if (isset($_REQUEST['enddate']) AND ($_REQUEST['enddate'] > 0)) {
	$enddate = urldecode($_REQUEST['enddate']);
} else {
	$enddate = date('Y').'-01-01 00:00:00';
}
if(!isset($_REQUEST['order_field']) OR empty($_REQUEST['order_field'])) {
	$order_field = 'testuser_creation_time';
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
header('Content-Disposition: attachment; filename=tcexam_user_results_'.$user_id.'_'.date('YmdHis').'.txt;');
header('Content-Transfer-Encoding: binary');

echo F_csv_export_allresults_user($user_id, $startdate, $enddate, $order_field);

/**
 * Export all user's test results to CSV.
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2008-12-26
 * @param int $user_id user ID
 * @param string $startdate start date in yyyy-mm-dd hh:mm:ss format
 * @param string $enddate end date in yyyy-mm-dd hh:mm:ss format
 * @param string $order_field ORDER BY portion of the SQL query
 * @return CSV data
 */
function F_csv_export_allresults_user($user_id, $startdate, $enddate, $order_field='') {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_authorization.php');
	require_once('../../shared/code/tce_functions_test_stats.php');

	$user_id = intval($user_id);
	$startdate = F_escape_sql($startdate);
	$enddate = F_escape_sql($enddate);
	$order_field = F_escape_sql($order_field);

	$csv = ''; // CSV data to be returned

	// print column names
	$csv .= '#';
	$csv .= K_TAB.$l['w_time'];
	$csv .= K_TAB.$l['w_test'];
	$csv .= K_TAB.$l['w_score'];
	$csv .= K_TAB.$l['w_answers_right'];
	$csv .= K_TAB.$l['w_answers_wrong'];
	$csv .= K_TAB.$l['w_questions_unanswered'];
	$csv .= K_TAB.$l['w_questions_undisplayed'];
	$csv .= K_TAB.$l['w_questions_unrated'];
	$csv .= K_TAB.$l['w_comment'];

	// output users stats
	$sqlr = 'SELECT testuser_id, test_id, test_name, testuser_creation_time, testuser_status, SUM(testlog_score) AS total_score
		FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS.'
		WHERE testuser_status>0
			AND testuser_creation_time>=\''.$startdate.'\'
			AND testuser_creation_time<=\''.$enddate.'\'
			AND testuser_user_id='.$user_id.'
			AND testlog_testuser_id=testuser_id
			AND testuser_test_id=test_id
		GROUP BY testuser_id, test_id, test_name, testuser_creation_time, testuser_status
		ORDER BY '.$order_field.'';
	if($rr = F_db_query($sqlr, $db)) {
		$itemcount = 1;
		while($mr = F_db_fetch_array($rr)) {
			$csv .= K_NEWLINE.$itemcount;
			$csv .= K_TAB.$mr['testuser_creation_time'];
			$csv .= K_TAB.$mr['test_name'];
			$csv .= K_TAB.$mr['total_score'];
			$usrtestdata = F_getUserTestStat($mr['test_id'], $user_id);
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
?>
