<?php
//============================================================+
// File name   : tce_xml_user_results.php
// Begin       : 2008-12-26
// Last Update : 2011-05-24
//
// Description : Export all user's results in XML.
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
//    Copyright (C) 2004-2011  Nicola Asuni - Tecnick.com LTD
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
 * Export all user's results in XML.
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

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('../code/tce_functions_statistics.php');

if (isset($_REQUEST['user_id']) AND ($_REQUEST['user_id'] > 0)) {
	$user_id = intval($_REQUEST['user_id']);
} else {
	exit;
}
if (isset($_REQUEST['startdate']) AND ($_REQUEST['startdate'] > 0)) {
	$startdate = urldecode($_REQUEST['startdate']);
	$startdate_time = strtotime($startdate);
	$startdate = date(K_TIMESTAMP_FORMAT, $startdate_time);
} else {
	$startdate = date('Y').'-01-01 00:00:00';
}
if (isset($_REQUEST['enddate']) AND ($_REQUEST['enddate'] > 0)) {
	$enddate = urldecode($_REQUEST['enddate']);
	$enddate_time = strtotime($enddate);
	$enddate = date(K_TIMESTAMP_FORMAT, $enddate_time);
} else {
	$enddate = date('Y').'-01-01 00:00:00';
}
if (isset($_REQUEST['order_field']) AND !empty($_REQUEST['order_field']) AND (in_array($_REQUEST['order_field'], array('testuser_creation_time', 'total_score')))) {
	$order_field = $_REQUEST['order_field'];
} else {
	$order_field = 'testuser_creation_time';
}

// define symbols for answers list
$qtype = array('S', 'M', 'T', 'O'); // question types
$type = array('single', 'multiple', 'text', 'ordering');
$boolean = array('false', 'true');

// send XML headers
header('Content-Description: XML File Transfer');
header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
// force download dialog
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream', false);
header('Content-Type: application/download', false);
header('Content-Type: application/xml', false);
// use the Content-Disposition header to supply a recommended filename
header('Content-Disposition: attachment; filename=tcexam_user_results_'.$user_id.'_'.date('YmdHis').'.xml;');
header('Content-Transfer-Encoding: binary');

require_once('../../shared/code/tce_authorization.php');
require_once('tce_functions_user_select.php');

if (!F_isAuthorizedEditorForUser($user_id)) {
	exit;
}

$xml = ''; // XML data to be returned

$xml .= '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'.K_NEWLINE;
$xml .= '<tcexamuserresults version="'.K_TCEXAM_VERSION.'">'.K_NEWLINE;
$xml .=  K_TAB.'<header';
$xml .= ' lang="'.K_USER_LANG.'"';
$xml .= ' date="'.date(K_TIMESTAMP_FORMAT).'">'.K_NEWLINE;
$xml .= K_TAB.K_TAB.'<user_id>'.$user_id.'</user_id>'.K_NEWLINE;
$sql = 'SELECT user_name, user_lastname, user_firstname FROM '.K_TABLE_USERS.' WHERE user_id='.$user_id.'';
if ($r = F_db_query($sql, $db)) {
	if ($m = F_db_fetch_array($r)) {
		$xml .= K_TAB.K_TAB.'<user_name>'.$m['user_name'].'</user_name>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.'<user_lastname>'.$m['user_lastname'].'</user_lastname>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.'<user_firstname>'.$m['user_firstname'].'</user_firstname>'.K_NEWLINE;
	}
} else {
	F_display_db_error();
}
$xml .= K_TAB.K_TAB.'<date_from>'.$startdate.'</date_from>'.K_NEWLINE;
$xml .= K_TAB.K_TAB.'<date_to>'.$enddate.'</date_to>'.K_NEWLINE;
$xml .= K_TAB.'</header>'.K_NEWLINE;
$xml .=  K_TAB.'<body>'.K_NEWLINE;

$statsdata = array();
$statsdata['score'] = array();
$statsdata['right'] = array();
$statsdata['wrong'] = array();
$statsdata['unanswered'] = array();
$statsdata['undisplayed'] = array();
$statsdata['unrated'] = array();

$sql = 'SELECT
		testuser_id,
		test_id,
		test_name,
		testuser_creation_time,
		testuser_status,
		SUM(testlog_score) AS total_score,
		MAX(testlog_change_time) AS testuser_end_time
	FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS.'
	WHERE testuser_status>0
		AND testuser_creation_time>=\''.F_escape_sql($startdate).'\'
		AND testuser_creation_time<=\''.F_escape_sql($enddate).'\'
		AND testuser_user_id='.$user_id.'
		AND testlog_testuser_id=testuser_id
		AND testuser_test_id=test_id';
if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
	$sql .= ' AND test_user_id IN ('.F_getAuthorizedUsers($_SESSION['session_user_id']).')';
}
$sql .= ' GROUP BY testuser_id, test_id, test_name, testuser_creation_time, testuser_status ORDER BY '.$order_field.'';
if($r = F_db_query($sql, $db)) {
	$passed = 0;
	while($m = F_db_fetch_array($r)) {
		$testuser_id = $m['testuser_id'];
		$usrtestdata = F_getUserTestStat($m['test_id'], $user_id);
		$halfscore = ($usrtestdata['max_score'] / 2);
		$xml .= K_TAB.K_TAB.'<test id=\''.$m['test_id'].'\'>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<start_time>'.$m['testuser_creation_time'].'</start_time>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<end_time>'.$m['testuser_end_time'].'</end_time>'.K_NEWLINE;
		$time_diff = strtotime($m['testuser_end_time']) - strtotime($m['testuser_creation_time']); //sec
		$time_diff = gmdate('H:i:s', $time_diff);
		$xml .= K_TAB.K_TAB.K_TAB.'<time>'.$time_diff.'</time>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<name>'.F_text_to_xml($m['test_name']).'</name>'.K_NEWLINE;
		if ($usrtestdata['score_threshold'] > 0) {
			if ($usrtestdata['score'] >= $usrtestdata['score_threshold']) {
				$xml .= K_TAB.K_TAB.K_TAB.'<passed>true</passed>'.K_NEWLINE;
				$passed++;
			} else {
				$xml .= K_TAB.K_TAB.K_TAB.'<passed>false</passed>'.K_NEWLINE;
			}
		} elseif ($usrtestdata['score'] > $halfscore) {
			$passed++;
		}
		$xml .= K_TAB.K_TAB.K_TAB.'<score>'.round($m['total_score'], 3).'</score>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<score_percent>'.round(100 * $usrtestdata['score'] / $usrtestdata['max_score']).'</score_percent>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<right>'.$usrtestdata['right'].'</right>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<right_percent>'.round(100 * $usrtestdata['right'] / $usrtestdata['all']).'</right_percent>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<wrong>'.$usrtestdata['wrong'].'</wrong>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<wrong_percent>'.round(100 * $usrtestdata['wrong'] / $usrtestdata['all']).'</wrong_percent>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<unanswered>'.$usrtestdata['unanswered'].'</unanswered>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<unanswered_percent>'.round(100 * $usrtestdata['unanswered'] / $usrtestdata['all']).'</unanswered_percent>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<undisplayed>'.$usrtestdata['undisplayed'].'</undisplayed>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<undisplayed_percent>'.round(100 * $usrtestdata['undisplayed'] / $usrtestdata['all']).'</undisplayed_percent>'.K_NEWLINE;
		if ($m['testuser_status'] == 4) {
			$status = $l['w_locked'];
		} else {
			$status = $l['w_unlocked'];
		}
		$xml .= K_TAB.K_TAB.K_TAB.'<status>'.$status.'</status>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<comment>'.F_text_to_xml($usrtestdata['comment']).'</comment>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.'</test>'.K_NEWLINE;

		// collects data for descriptive statistics
		$statsdata['score'][] = $m['total_score'] / $usrtestdata['max_score'];
		$statsdata['right'][] = $usrtestdata['right'] / $usrtestdata['all'];
		$statsdata['wrong'][] = $usrtestdata['wrong'] / $usrtestdata['all'];
		$statsdata['unanswered'][] = $usrtestdata['unanswered'] / $usrtestdata['all'];
		$statsdata['undisplayed'][] = $usrtestdata['undisplayed'] / $usrtestdata['all'];
		$statsdata['unrated'][] = $usrtestdata['unrated'] / $usrtestdata['all'];
	}
} else {
	F_display_db_error();
}

// calculate statistics
$stats = F_getArrayStatistics($statsdata);
$excludestat = array('sum', 'variance');
$calcpercent = array('mean', 'median', 'mode', 'minimum', 'maximum', 'range', 'standard_deviation');

$xml .= K_TAB.K_TAB.'<teststatistics>'.K_NEWLINE;
$xml .= K_TAB.K_TAB.K_TAB.'<passed>'.$passed.'</passed>'.K_NEWLINE;
$xml .= K_TAB.K_TAB.K_TAB.'<passed_percent>'.round(100 * ($passed / $stats['number']['score'])).'</passed_percent>'.K_NEWLINE;
foreach ($stats as $row => $columns) {
	if (!in_array($row, $excludestat)) {
		$xml .= K_TAB.K_TAB.K_TAB.'<'.$row.'>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<score>'.round($columns['score'], 3).'</score>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<right>'.round($columns['right'], 3).'</right>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<wrong>'.round($columns['wrong'], 3).'</wrong>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unanswered>'.round($columns['unanswered'], 3).'</unanswered>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<undisplayed>'.round($columns['undisplayed'], 3).'</undisplayed>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unrated>'.round($columns['unrated'], 3).'</unrated>'.K_NEWLINE;
		if (in_array($row, $calcpercent)) {
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<score_percent>'.round(100 * ($columns['score'] / $usrtestdata['max_score'])).'</score_percent>'.K_NEWLINE;
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<right_percent>'.round(100 * ($columns['right'] / $usrtestdata['all'])).'</right_percent>'.K_NEWLINE;
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<wrong_percent>'.round(100 * ($columns['wrong'] / $usrtestdata['all'])).'</wrong_percent>'.K_NEWLINE;
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unanswered_percent>'.round(100 * ($columns['unanswered'] / $usrtestdata['all'])).'</unanswered_percent>'.K_NEWLINE;
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<undisplayed_percent>'.round(100 * ($columns['undisplayed'] / $usrtestdata['all'])).'</undisplayed_percent>'.K_NEWLINE;
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unrated_percent>'.round(100 * ($columns['unrated'] / $usrtestdata['all'])).'</unrated_percent>'.K_NEWLINE;
		}
		$xml .= K_TAB.K_TAB.K_TAB.'</'.$row.'>'.K_NEWLINE;
	}
}
$xml .= K_TAB.K_TAB.'</teststatistics>'.K_NEWLINE;

$xml .= K_TAB.'</body>'.K_NEWLINE;
$xml .= '</tcexamuserresults>'.K_NEWLINE;
echo $xml;

//============================================================+
// END OF FILE
//============================================================+
