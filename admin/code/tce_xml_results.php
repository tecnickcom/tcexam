<?php
//============================================================+
// File name   : tce_xml_results.php
// Begin       : 2008-06-06
// Last Update : 2009-10-10
// 
// Description : Export all users' results in XML.
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
 * Export all users' results in XML.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2004-06-11
 * @param int $_REQUEST['testid'] test ID
 * @param int $_REQUEST['groupid'] group ID
 * @param string $_REQUEST['orderfield'] ORDER BY portion of SQL selection query
 */

/**
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('../code/tce_functions_statistics.php');

if (isset($_REQUEST['testid']) AND ($_REQUEST['testid'] > 0)) {
	$test_id = intval($_REQUEST['testid']);
	// check user's authorization
	require_once('../../shared/code/tce_authorization.php');
	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		exit;
	}
} else {
	exit;
}

if (isset($_REQUEST['groupid']) AND ($_REQUEST['groupid'] > 0)) {
	$group_id = intval($_REQUEST['groupid']);
} else {
	$group_id = 0;
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
header('Content-Disposition: attachment; filename=tcexam_test_results_'.$test_id.'_'.date('YmdHis').'.xml;');
header('Content-Transfer-Encoding: binary');

$xml = ''; // XML data to be returned

$xml .= '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'.K_NEWLINE;
$xml .= '<tcexamresults version="'.K_TCEXAM_VERSION.'">'.K_NEWLINE;
$xml .=  K_TAB.'<header';
$xml .= ' lang="'.K_USER_LANG.'"';
$xml .= ' date="'.date(K_TIMESTAMP_FORMAT).'">'.K_NEWLINE;
$xml .= K_TAB.'</header>'.K_NEWLINE;
$xml .=  K_TAB.'<body>'.K_NEWLINE;

// get test data
$sql = 'SELECT * 
	FROM '.K_TABLE_TESTS.' 
	WHERE test_id='.$test_id.'
	LIMIT 1';
if($r = F_db_query($sql, $db)) {
	if($m = F_db_fetch_array($r)) {
		// test data
		$xml .= K_TAB.K_TAB.'<test id="'.$test_id.'">'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<name>'.F_text_to_xml($m['test_name']).'</name>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<description>'.F_text_to_xml($m['test_description']).'</description>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<begin_time>'.$m['test_begin_time'].'</begin_time>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<end_time>'.$m['test_end_time'].'</end_time>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<duration_time>'.$m['test_duration_time'].'</duration_time>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<ip_range>'.$m['test_ip_range'].'</ip_range>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<score_right>'.$m['test_score_right'].'</score_right>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<score_wrong>'.$m['test_score_wrong'].'</score_wrong>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<score_unanswered>'.$m['test_score_unanswered'].'</score_unanswered>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<score_threshold>'.$m['test_score_threshold'].'</score_threshold>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<max_score>'.$m['test_max_score'].'</max_score>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<random_questions_select>'.$boolean[intval(F_getBoolean($m['test_random_questions_select']))].'</random_questions_select>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<random_questions_order>'.$boolean[intval(F_getBoolean($m['test_random_questions_order']))].'</random_questions_order>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<random_answers_select>'.$boolean[intval(F_getBoolean($m['test_random_answers_select']))].'</random_answers_select>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<random_answers_order>'.$boolean[intval(F_getBoolean($m['test_random_answers_order']))].'</random_answers_order>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<results_to_users>'.$boolean[intval(F_getBoolean($m['test_results_to_users']))].'</results_to_users>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<report_to_users>'.$boolean[intval(F_getBoolean($m['test_report_to_users']))].'</report_to_users>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<comment_enabled>'.$boolean[intval(F_getBoolean($m['test_comment_enabled']))].'</comment_enabled>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<menu_enabled>'.$boolean[intval(F_getBoolean($m['test_menu_enabled']))].'</menu_enabled>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<noanswer_enabled>'.$boolean[intval(F_getBoolean($m['test_noanswer_enabled']))].'</noanswer_enabled>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.'<mcma_radio>'.$boolean[intval(F_getBoolean($m['test_mcma_radio']))].'</mcma_radio>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.'</test>'.K_NEWLINE;
	}
} else {
	F_display_db_error();
}

$statsdata = array();
$statsdata['score'] = array();
$statsdata['right'] = array();
$statsdata['wrong'] = array();
$statsdata['unanswered'] = array();
$statsdata['undisplayed'] = array();
$statsdata['unrated'] = array();

$sql = 'SELECT testuser_id, user_id, SUM(testlog_score) AS total_score, MAX(testlog_change_time) AS test_end_time, testuser_creation_time
	FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_USERS.' 
	WHERE testlog_testuser_id=testuser_id
		AND testuser_user_id=user_id 
		AND testuser_test_id='.$test_id.'';
if ($group_id > 0) {
	$sql .= ' AND testuser_user_id IN (
			SELECT usrgrp_user_id
			FROM '.K_TABLE_USERGROUP.' 
			WHERE usrgrp_group_id='.$group_id.'
		)';
}
$sql .= ' GROUP BY testuser_id, user_id, testuser_creation_time';
if($r = F_db_query($sql, $db)) {
	$passed = 0;
	while($m = F_db_fetch_array($r)) {
		$testuser_id = $m['testuser_id'];
		$user_id = $m['user_id'];
		$xml .= K_TAB.K_TAB.'<user id="'.$user_id.'">'.K_NEWLINE;			
		$sqla = 'SELECT * 
			FROM '.K_TABLE_USERS.'
			WHERE user_id='.$user_id.'
			LIMIT 1';
		if($ra = F_db_query($sqla, $db)) {
			$xml .= K_TAB.K_TAB.K_TAB.'<userdata>'.K_NEWLINE;
			if($ma = F_db_fetch_array($ra)) {		
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<name>'.F_text_to_xml($ma['user_name']).'</name>'.K_NEWLINE;		
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<email>'.$ma['user_email'].'</email>'.K_NEWLINE;				
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<regdate>'.$ma['user_regdate'].'</regdate>'.K_NEWLINE;				
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<ip>'.$ma['user_ip'].'</ip>'.K_NEWLINE;				
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<firstname>'.F_text_to_xml($ma['user_firstname']).'</firstname>'.K_NEWLINE;				
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<lastname>'.F_text_to_xml($ma['user_lastname']).'</lastname>'.K_NEWLINE;				
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<birthdate>'.substr($ma['user_birthdate'],0,10).'</birthdate>'.K_NEWLINE;				
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<birthplace>'.F_text_to_xml($ma['user_birthplace']).'</birthplace>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<regnumber>'.F_text_to_xml($ma['user_regnumber']).'</regnumber>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<ssn>'.F_text_to_xml($ma['user_ssn']).'</ssn>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<level>'.$ma['user_level'].'</level>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<verifycode>'.$ma['user_verifycode'].'</verifycode>'.K_NEWLINE;
			}
			$xml .= K_TAB.K_TAB.K_TAB.'</userdata>'.K_NEWLINE;
		}
		$usrtestdata = F_getUserTestStat($test_id, $user_id);
		$xml .= K_TAB.K_TAB.K_TAB.'<stats>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<score>'.round($m['total_score'],1).'</score>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<score_percent>'.round(100 * $usrtestdata['score'] / $usrtestdata['max_score']).'</score_percent>'.K_NEWLINE;					
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<right>'.$usrtestdata['right'].'</right>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<right_percent>'.round(100 * $usrtestdata['right'] / $usrtestdata['all']).'</right_percent>'.K_NEWLINE;						
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<wrong>'.$usrtestdata['wrong'].'</wrong>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<wrong_percent>'.round(100 * $usrtestdata['wrong'] / $usrtestdata['all']).'</wrong_percent>'.K_NEWLINE;						
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unanswered>'.$usrtestdata['unanswered'].'</unanswered>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unanswered_percent>'.round(100 * $usrtestdata['unanswered'] / $usrtestdata['all']).'</unanswered_percent>'.K_NEWLINE;				
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<undisplayed>'.$usrtestdata['undisplayed'].'</undisplayed>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<undisplayed_percent>'.round(100 * $usrtestdata['undisplayed'] / $usrtestdata['all']).'</undisplayed_percent>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<start_time>'.$m['testuser_creation_time'].'</start_time>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<end_time>'.$m['test_end_time'].'</end_time>'.K_NEWLINE;
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<comment>'.F_text_to_xml($usrtestdata['comment']).'</comment>'.K_NEWLINE;
		if ($usrtestdata['score_threshold'] > 0) {
			if ($usrtestdata['score'] >= $usrtestdata['score_threshold']) {
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<passed>true</passed>'.K_NEWLINE;
				$passed++;
			} else {
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<passed>false</passed>'.K_NEWLINE;
			}
		}
		$xml .= K_TAB.K_TAB.K_TAB.'</stats>'.K_NEWLINE;
		
		$xml .= K_TAB.K_TAB.K_TAB.'<details>'.K_NEWLINE;
		
		// collects data for descriptive statistics
		$statsdata['score'][] = $m['total_score'];
		$statsdata['right'][] = $usrtestdata['right'];
		$statsdata['wrong'][] = $usrtestdata['wrong'];
		$statsdata['unanswered'][] = $usrtestdata['unanswered'];
		$statsdata['undisplayed'][] = $usrtestdata['undisplayed'];
		$statsdata['unrated'][] = $usrtestdata['unrated'];
		
		// detailed test report
		$sqlq = 'SELECT * 
			FROM '.K_TABLE_QUESTIONS.', '.K_TABLE_TESTS_LOGS.' 
			WHERE question_id=testlog_question_id 
			AND testlog_testuser_id='.$testuser_id.'
			ORDER BY testlog_id';
		if($rq = F_db_query($sqlq, $db)) {
			$itemcount = 1;
			while($mq = F_db_fetch_array($rq)) {
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<question num="'.$itemcount.'" id="'.$mq['question_id'].'">'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<subject_id>'.$mq['question_subject_id'].'</subject_id>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<type>'.$qtype[($mq['question_type']-1)].'</type>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<difficulty>'.$mq['question_difficulty'].'</difficulty>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<position>'.$mq['question_position'].'</position>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<timer>'.$mq['question_timer'].'</timer>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<fullscreen>'.$boolean[intval(F_getBoolean($mq['question_fullscreen']))].'</fullscreen>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<inline_answers>'.$boolean[intval(F_getBoolean($mq['question_inline_answers']))].'</inline_answers>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<auto_next>'.$boolean[intval(F_getBoolean($mq['question_auto_next']))].'</auto_next>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<score>'.$mq['testlog_score'].'</score>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<ip>'.$mq['testlog_user_ip'].'</ip>'.K_NEWLINE;
				if (isset($mq['testlog_display_time']) AND (strlen($mq['testlog_display_time']) > 0)) {
					$display_time =  substr($mq['testlog_display_time'], 11, 8);
				} else {
					$display_time = '';
				}
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<display_time>'.$display_time.'</display_time>'.K_NEWLINE;
				if (isset($mq['testlog_change_time']) AND (strlen($mq['testlog_change_time']) > 0)) {
					$change_time = substr($mq['testlog_change_time'], 11, 8);
				} else {
					$change_time = '';
				}
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<change_time>'.$change_time.'</change_time>'.K_NEWLINE;
				if (isset($mq['testlog_display_time']) AND isset($mq['testlog_change_time'])) {
					$diff_time = date('i:s', (strtotime($mq['testlog_change_time']) - strtotime($mq['testlog_display_time'])));
				} else {
					$diff_time = '';
				}
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<diff_time>'.$diff_time.'</diff_time>'.K_NEWLINE;
				if (isset($mq['testlog_reaction_time']) AND ($mq['testlog_reaction_time'] > 0)) {
					$reaction_time =  intval($mq['testlog_reaction_time']);
				} else {
					$reaction_time = '';
				}
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<reaction_time>'.$reaction_time.'</reaction_time>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<description>'.F_text_to_xml($mq['question_description']).'</description>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<explanation>'.F_text_to_xml($mq['question_explanation']).'</explanation>'.K_NEWLINE;
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<answers>'.K_NEWLINE;
				if ($mq['question_type'] == 3) {
					// free-text question
					// print user text answer
					$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<text>'.$mq['testlog_answer_text'].'</text>'.K_NEWLINE;
				} else {
					// display each answer option
					$sqla = 'SELECT *
						FROM '.K_TABLE_LOG_ANSWER.', '.K_TABLE_ANSWERS.'
						WHERE logansw_answer_id=answer_id
							AND logansw_testlog_id=\''.$mq['testlog_id'].'\'
						ORDER BY logansw_order';
					if($ra = F_db_query($sqla, $db)) {
						$idx = 1; // count answer items
						while($ma = F_db_fetch_array($ra)) {
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<answer num="'.$idx.'" id="'.$ma['answer_id'].'">'.K_NEWLINE;
							if ($mq['question_type'] == 4) {	
								$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<position>'.$ma['answer_position'].'</position>'.K_NEWLINE;
								$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<answposition>'.$ma['logansw_position'].'</answposition>'.K_NEWLINE;
							} else {
								$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<isright>'.$boolean[intval(F_getBoolean($ma['answer_isright']))].'</isright>'.K_NEWLINE;
								$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<selected>'.intval($ma['logansw_selected']).'</selected>'.K_NEWLINE;
							}
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<key>'.F_text_to_xml(chr($ma['answer_keyboard_key'])).'</key>'.K_NEWLINE;
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<description>'.F_text_to_xml($ma['answer_description']).'</description>'.K_NEWLINE;
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<explanation>'.F_text_to_xml($ma['answer_explanation']).'</explanation>'.K_NEWLINE;
							$idx++;
							$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'</answer>'.K_NEWLINE;
						}
					} else {
						F_display_db_error();
					}
				} // end multiple answers
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'</answers>'.K_NEWLINE;
				// export teacher/supervisor comment to the question
				if (isset($mq['testlog_comment']) AND (!empty($mq['testlog_comment']))) {
					$xml .= K_TAB.K_TAB.K_TAB.K_TAB.K_TAB.'<qcomment>'.F_text_to_xml($mq['testlog_comment']).'</qcomment>'.K_NEWLINE;
				}
				$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'</question>'.K_NEWLINE;
				$itemcount++;
			} // end questions
			
			$xml .= K_TAB.K_TAB.K_TAB.'</details>'.K_NEWLINE;
		} else {
			F_display_db_error();
		}
	$xml .= K_TAB.K_TAB.'</user>'.K_NEWLINE;
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
		if (in_array($row, $calcpercent)) {
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<score_percent>'.round(100 * ($columns['score'] / $usrtestdata['max_score'])).'</score_percent>'.K_NEWLINE;
		}
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<right>'.round($columns['right'], 3).'</right>'.K_NEWLINE;
		if (in_array($row, $calcpercent)) {
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<right_percent>'.round(100 * ($columns['right'] / $usrtestdata['all'])).'</right_percent>'.K_NEWLINE;
		}
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<wrong>'.round($columns['wrong'], 3).'</wrong>'.K_NEWLINE;
		if (in_array($row, $calcpercent)) {
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<wrong_percent>'.round(100 * ($columns['wrong'] / $usrtestdata['all'])).'</wrong_percent>'.K_NEWLINE;
		}
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unanswered>'.round($columns['unanswered'], 3).'</unanswered>'.K_NEWLINE;
		if (in_array($row, $calcpercent)) {
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unanswered_percent>'.round(100 * ($columns['unanswered'] / $usrtestdata['all'])).'</unanswered_percent>'.K_NEWLINE;
		}
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<undisplayed>'.round($columns['undisplayed'], 3).'</undisplayed>'.K_NEWLINE;
		if (in_array($row, $calcpercent)) {
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<undisplayed_percent>'.round(100 * ($columns['undisplayed'] / $usrtestdata['all'])).'</undisplayed_percent>'.K_NEWLINE;
		}
		$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unrated>'.round($columns['unrated'], 3).'</unrated>'.K_NEWLINE;
		if (in_array($row, $calcpercent)) {
			$xml .= K_TAB.K_TAB.K_TAB.K_TAB.'<unrated_percent>'.round(100 * ($columns['unrated'] / $usrtestdata['all'])).'</unrated_percent>'.K_NEWLINE;
		}
		$xml .= K_TAB.K_TAB.K_TAB.'</'.$row.'>'.K_NEWLINE;
	}
}
$xml .= K_TAB.K_TAB.'</teststatistics>'.K_NEWLINE;

$xml .= K_TAB.'</body>'.K_NEWLINE;
$xml .= '</tcexamresults>'.K_NEWLINE;
echo $xml;

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
