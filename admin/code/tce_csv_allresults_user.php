<?php
//============================================================+
// File name   : tce_csv_allresults_user.php
// Begin       : 2008-12-26
// Last Update : 2011-05-20
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
//    Copyright (C) 2004-2011  Nicola Asuni - Tecnick.com S.r.l.
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
 * Display all user's results in CSV format.
 * (Tab Delimited Text File)
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2008-12-26
 * @param $_REQUEST['user_id'] (int) user ID
 * @param $_REQUEST['startdate'] (int) start date
 * @param $_REQUEST['enddate'] (int) end date
 * @param $_REQUEST['orderfield'] (string) ORDER BY portion of SQL selection query
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
 * @since 2008-12-26
 * @param $user_id (int) user ID
 * @param $startdate (string) start date in yyyy-mm-dd hh:mm:ss format
 * @param $enddate (string) end date in yyyy-mm-dd hh:mm:ss format
 * @param $order_field (string) ORDER BY portion of the SQL query
 * @return CSV data
 */
function F_csv_export_allresults_user($user_id, $startdate, $enddate, $order_field='') {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_authorization.php');
	require_once('../../shared/code/tce_functions_test_stats.php');
	require_once('tce_functions_user_select.php');
	require_once('../code/tce_functions_statistics.php');

	$user_id = intval($user_id);
	$startdate = F_escape_sql($startdate);
	$enddate = F_escape_sql($enddate);
	$order_field = F_escape_sql($order_field);

	if (!F_isAuthorizedEditorForUser($user_id)) {
		return '';
	}

	// statistical data
	$statsdata = array();
	$statsdata['score'] = array();
	$statsdata['right'] = array();
	$statsdata['wrong'] = array();
	$statsdata['unanswered'] = array();
	$statsdata['undisplayed'] = array();
	$statsdata['unrated'] = array();

	$csv = ''; // CSV data to be returned

	// general data
	$csv .= 'TCExam User Results'.K_NEWLINE.K_NEWLINE;
	$csv .= 'version'.K_TAB.K_TCEXAM_VERSION.K_NEWLINE;
	$csv .= 'lang'.K_TAB.K_USER_LANG.K_NEWLINE;
	$csv .= 'date'.K_TAB.date(K_TIMESTAMP_FORMAT).K_NEWLINE;
	$csv .= 'user_id'.K_TAB.$user_id.K_NEWLINE;
	$sql = 'SELECT user_name, user_lastname, user_firstname FROM '.K_TABLE_USERS.' WHERE user_id='.$user_id.'';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			$csv .= $l['w_user'].K_TAB.$m['user_name'].K_NEWLINE;
			$csv .= $l['w_lastname'].K_TAB.$m['user_lastname'].K_NEWLINE;
			$csv .= $l['w_firstname'].K_TAB.$m['user_firstname'].K_NEWLINE;
		}
	} else {
		F_display_db_error();
	}
	$csv .= $l['w_time_begin'].K_TAB.$startdate.K_NEWLINE;
	$csv .= $l['w_time_end'].K_TAB.$enddate.K_NEWLINE;

	$csv .= K_NEWLINE.K_NEWLINE; // separator

	// print column names
	$csv .= '#';
	$csv .= K_TAB.$l['w_time_begin'];
	$csv .= K_TAB.$l['w_time_end'];
	$csv .= K_TAB.$l['w_time'];
	$csv .= K_TAB.$l['w_test'];
	$csv .= K_TAB.$l['w_passed'];
	$csv .= K_TAB.$l['w_score'];
	$csv .= K_TAB.$l['w_answers_right'];
	$csv .= K_TAB.$l['w_answers_wrong'];
	$csv .= K_TAB.$l['w_questions_unanswered'];
	$csv .= K_TAB.$l['w_questions_undisplayed'];
	$csv .= K_TAB.$l['w_questions_unrated'];
	$csv .= K_TAB.$l['w_status'];
	$csv .= K_TAB.$l['w_comment'];

	$passed = 0;

	// output users stats
	$sqlr = 'SELECT
		testuser_id,
		test_id,
		test_name,
		test_duration_time,
		testuser_creation_time,
		testuser_status,
		SUM(testlog_score) AS total_score,
		MAX(testlog_change_time) AS testuser_end_time
		FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS.'
		WHERE testuser_status>0
			AND testuser_creation_time>=\''.$startdate.'\'
			AND testuser_creation_time<=\''.$enddate.'\'
			AND testuser_user_id='.$user_id.'
			AND testlog_testuser_id=testuser_id
			AND testuser_test_id=test_id';
	if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
		$sqlr .= ' AND test_user_id IN ('.F_getAuthorizedUsers($_SESSION['session_user_id']).')';
	}
	$sqlr .= ' GROUP BY testuser_id, test_id, test_name, test_duration_time, testuser_creation_time, testuser_status ORDER BY '.$order_field.'';
	if($rr = F_db_query($sqlr, $db)) {
		$itemcount = 0;
		while($mr = F_db_fetch_array($rr)) {
			$itemcount++;
			$usrtestdata = F_getUserTestStat($mr['test_id'], $user_id);
			$halfscore = ($usrtestdata['max_score'] / 2);
			$csv .= K_NEWLINE.$itemcount;
			$csv .= K_TAB.$mr['testuser_creation_time'];
			$csv .= K_TAB.$mr['testuser_end_time'];
			$time_diff = strtotime($mr['testuser_end_time']) - strtotime($mr['testuser_creation_time']); //sec
			$time_diff = gmdate('H:i:s', $time_diff);
			$csv .= K_TAB.$time_diff;
			$csv .= K_TAB.$mr['test_name'];
			if ($usrtestdata['score_threshold'] > 0) {
				if ($usrtestdata['score'] >= $usrtestdata['score_threshold']) {
					$csv .= K_TAB.'true';
					$passed++;
				} else {
					$csv .= K_TAB.'false';
				}
			} else {
				$csv .= K_TAB;
				if ($usrtestdata['score'] > $halfscore) {
					$passed++;
				}
			}
			$csv .= K_TAB.$mr['total_score'];
			$csv .= K_TAB.$usrtestdata['right'];
			$csv .= K_TAB.$usrtestdata['wrong'];
			$csv .= K_TAB.$usrtestdata['unanswered'];
			$csv .= K_TAB.$usrtestdata['undisplayed'];
			$csv .= K_TAB.$usrtestdata['unrated'];
			if ($mr['testuser_status'] == 4) {
				$csv .= K_TAB.$l['w_locked'];
			} else {
				$csv .= K_TAB.$l['w_unlocked'];
			}
			$csv .= K_TAB.F_compact_string(htmlspecialchars($usrtestdata['comment'], ENT_NOQUOTES, $l['a_meta_charset']));

			// collects data for descriptive statistics
			$statsdata['score'][] = $mr['total_score'] / $usrtestdata['max_score'];
			$statsdata['right'][] = $usrtestdata['right'] / $usrtestdata['all'];
			$statsdata['wrong'][] = $usrtestdata['wrong'] / $usrtestdata['all'];
			$statsdata['unanswered'][] = $usrtestdata['unanswered'] / $usrtestdata['all'];
			$statsdata['undisplayed'][] = $usrtestdata['undisplayed'] / $usrtestdata['all'];
			$statsdata['unrated'][] = $usrtestdata['unrated'] / $usrtestdata['all'];
		}
	} else {
		F_display_db_error();
	}

	$csv .= K_NEWLINE; // separator

	// calculate statistics
	$stats = F_getArrayStatistics($statsdata);
	$excludestat = array('sum', 'variance');
	$calcpercent = array('mean', 'median', 'mode', 'minimum', 'maximum', 'range', 'standard_deviation');

	$csv .= K_TAB.K_TAB.K_TAB.K_TAB.'passed_total'.K_TAB.$passed.K_NEWLINE;
	$csv .= K_TAB.K_TAB.K_TAB.K_TAB.'passed_percent [%]'.K_TAB.round(100 * ($passed / $itemcount)).K_NEWLINE;

	$csv .= K_NEWLINE; // separator

	$csv .= $l['w_statistics'].K_NEWLINE; // separator

	// headers
	$csv .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB;
	$csv .= $l['w_score'].K_TAB;
	$csv .= $l['w_answers_right_th'].K_TAB;
	$csv .= $l['w_answers_wrong_th'].K_TAB;
	$csv .= $l['w_questions_unanswered_th'].K_TAB;
	$csv .= $l['w_questions_undisplayed_th'].K_TAB;
	$csv .= $l['w_questions_unrated'].K_NEWLINE;

	foreach ($stats as $row => $columns) {
		if (!in_array($row, $excludestat)) {
			$csv .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.$l['w_'.$row].K_TAB;
			$csv .= round($columns['score'], 3).K_TAB;
			$csv .= round($columns['right'], 3).K_TAB;
			$csv .= round($columns['wrong'], 3).K_TAB;
			$csv .= round($columns['unanswered'], 3).K_TAB;
			$csv .= round($columns['undisplayed'], 3).K_TAB;
			$csv .= round($columns['unrated'], 3).K_NEWLINE;
			if (in_array($row, $calcpercent)) {
				$csv .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.$row.' [%]'.K_TAB;
				$csv .= round(100 * ($columns['score'] / $usrtestdata['max_score'])).K_TAB;
				$csv .= round(100 * ($columns['right'] / $usrtestdata['all'])).K_TAB;
				$csv .= round(100 * ($columns['wrong'] / $usrtestdata['all'])).K_TAB;
				$csv .= round(100 * ($columns['unanswered'] / $usrtestdata['all'])).K_TAB;
				$csv .= round(100 * ($columns['undisplayed'] / $usrtestdata['all'])).K_TAB;
				$csv .= round(100 * ($columns['unrated'] / $usrtestdata['all'])).K_NEWLINE;
			}
		}
	}

	return $csv;
}

//============================================================+
// END OF FILE
//============================================================+
