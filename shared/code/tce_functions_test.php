<?php
//============================================================+
// File name   : tce_functions_test.php
// Begin       : 2004-05-28
// Last Update : 2009-03-08
// 
// Description : Functions to handle test generation, status
//               and user access.
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
 * Functions to handle test generation, status and user access.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2004-05-28
 */

/**
 * Returns an XHTML unordered list of user's enabled tests.
 * @return string containing an XHTML unordered list
 */
function F_getUserTests() {
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_tcecode.php');
	global $db, $l;
	$str = ''; //string to return
	// get current date-time
	$current_time = date(K_TIMESTAMP_FORMAT);
	$sql = 'SELECT *
		FROM '.K_TABLE_TESTS.'
		WHERE (
			test_id IN (
				SELECT tsubset_test_id
				FROM '.K_TABLE_TEST_SUBJSET.'
			)
			AND (test_begin_time < \''.$current_time.'\')
			AND (test_end_time > \''.$current_time.'\')
		)
		ORDER BY test_name'; // select all active tests
	if($r = F_db_query($sql, $db)) {
		while($m = F_db_fetch_array($r)) { // for each active test
			// check user's authorization
			if (F_isValidTestUser($m['test_id'],$_SESSION['session_user_ip'], $m['test_ip_range'])) {
				// the user's IP is valid, check test status
				$test_status = F_checkTestStatus($_SESSION['session_user_id'], $m['test_id'], $m['test_duration_time']);
				if (($test_status < 4) OR (F_getBoolean($m['test_results_to_users']))) {
					$str .= '<li title="'.F_tcecodeToTitle($m['test_description']).'">';
					$str .= $m['test_name'];
					$str .= ' ['.F_testInfoLink($m['test_id'], $l['w_info']).']';
				}
				// display various links by status case
				switch ($test_status) {
					case 0: { // 0 = the test generation process is started but not completed
						// print execute test link
						$str .= ' [<a href="tce_test_execute.php?testid='.$m['test_id'].'" title="'.$l['h_execute'].'">'.$l['w_execute'].'</a>]';
						break;
					}
					case 1: // 1 = the test has been successfully created
					case 2: // 2 = all questions have been displayed to the user
					case 3: { // 3 = all questions have been answered
						// continue test
						$str .= ' [<a href="tce_test_execute.php?testid='.$m['test_id'].'" title="'.$l['h_continue'].'">'.$l['w_continue'].'</a>]';
						break;
					}
					case 4: { // 4 = test locked (for timeout)
						if (F_getBoolean($m['test_results_to_users'])) {
							$str .= ' [<a href="tce_test_results.php?testid='.$m['test_id'].'" title="'.$l['h_result'].'">'.$l['w_result'].'</a>]';
						}
						if (F_getBoolean($m['test_repeatable'])) {
							$str .= ' [<a href="index.php?testid='.$m['test_id'].'&amp;repeat=1" title="'.$l['h_repeat_test'].'">'.$l['w_repeat'].'</a>]';
						}
						break;
					}
				}
				if (($test_status < 4) OR (F_getBoolean($m['test_results_to_users']))) {
					$str .= '</li>';
				}
			}
		}
	} else {
		F_display_db_error();
	}
	if (strlen($str) > 0) {
		$str = $l['w_tests_available'].":<br />\n<ul>\n".$str."</ul>\n";
	} else {
		$str = $l['m_no_test_available'];
	}
	return $str;
}

/**
 * Remove the specified test results for the current user
 * @param int $test_id Test ID
 */
function F_removeTestResults($test_id) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$test_id = intval($test_id);
	$user_id = intval($_SESSION['session_user_id']);
	$sql = 'SELECT test_id 
		FROM '.K_TABLE_TESTS.' 
		WHERE test_id='.$test_id.'
			AND test_repeatable=\'1\'
		LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$sqld = 'DELETE FROM '.K_TABLE_TEST_USER.'
				WHERE testuser_test_id='.$test_id.'
					AND testuser_user_id='.$user_id.'';
			if(!$rd = F_db_query($sqld, $db)) {
				F_display_db_error();
			}
		}
	} else {
		F_display_db_error();
	}
}

/**
 * Check if user's IP is valid over test IP range
 * @param int $user_ip user's IP address in expanded IPv6 format.
 * @param int $test_ips comma separated list of valid test IP addresses. The '*' character may be used to indicate any number in IPv4 addresses. Intervals must be specified using the '-' character.
 * @return true if IP is valid, false otherwise
 */
function F_isValidIP($user_ip, $test_ips) {
	if (empty($user_ip) OR empty($test_ips)) {
		return false;
	}
	// convert user IP to number
	$usrip = getIpAsInt($user_ip);
	// build array of valid IP masks
	$test_ip = explode(',', $test_ips);
	// check user IP against test IP masks
	while (list($key, $ipmask) = each($test_ip)) {
		if (strrpos($ipmask, '*') !== false) {
			// old range notation using IPv4 addresses and '*' character.
			$ipv4 = explode('.', $ipmask);
			$ipv4_start = array();
			$ipv4_end = array();
			foreach($ipv4 as $num) {
				if ($num == '*') {
					$ipv4_start[] = 0;
					$ipv4_end[] = 255;
				} else {
					$num = intval($num);
					if (($num >= 0) AND ($num <= 255)) {
						$ipv4_start[] = $num;
						$ipv4_end[] = $num;
					} else {
						$ipv4_start[] = 0;
						$ipv4_end[] = 255;
					}
				}
			}
			// convert to IPv6 address range
			$ipmask = getNormalizedIP(implode('.', $ipv4_start)).'-'.getNormalizedIP(implode('.', $ipv4_end));
		}
		if (strrpos($ipmask, '-') !== false) {
			// address range
			$ip_range = explode('-', $ipmask);
			if (count($ip_range) !== 2) {
				return false;
			}
			$ip_start = getIpAsInt($ip_range[0]);
			$ip_end = getIpAsInt($ip_range[1]);
			if (($usrip >= $ip_start) AND ($usrip <= $ip_end)) {
				return true;
			}
		} elseif ($usrip == getIpAsInt($ipmask)) {
			// exact address comparison
			return true;
		}
	}
	return false;
}
/**
 * Check if user is authorized to execute the specified test
 * @param int $test_id ID of the selected test
 * @param int $user_ip user's IP address.
 * @param int $test_ip test IP valid addresses. Various IP addresses may be separated using comma character. The asterisk character may be used to indicate "any number".
 * @return true if is user is authorized, false otherwise
 */
function F_isValidTestUser($test_id, $user_ip, $test_ip) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$test_id = intval($test_id);
	$user_id = intval($_SESSION['session_user_id']);
	// check user's IP
	if (!F_isValidIP($user_ip, $test_ip)) {
		return false;
	}
	// check user's group
	if (F_count_rows(K_TABLE_USERGROUP.', '.K_TABLE_TEST_GROUPS.'
		WHERE usrgrp_group_id=tstgrp_group_id
			AND tstgrp_test_id='.$test_id.'
			AND usrgrp_user_id='.$user_id.'
			LIMIT 1') > 0) {
		return true;
	}
	return false;
}
/**
 * Terminate user's test<br>
 * @param int $test_id test ID
 * @since 4.0.000 (2006-09-27)
 */
function F_terminateUserTest($test_id) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$test_id = intval($test_id);
	$user_id = intval($_SESSION['session_user_id']);
	$sql = 'UPDATE '.K_TABLE_TEST_USER.'
		SET testuser_status=4 
		WHERE testuser_test_id='.$test_id.'
			AND testuser_user_id='.$user_id.'';
	if(!$r = F_db_query($sql, $db)) {
		F_display_db_error();
	}
}

/**
 * Check and returns specific test status for the specified user.<br>
 * @param int $user_id user ID
 * @param int $test_id test ID
 * @param int $duration test duration in seconds
 * @return test status: <ul><li>0 = the test generation process is started but not completed;</li><li>1 = the test has been successfully created;</li><li>2 = all questions have been displayed to the user;</li><li>3 = all questions have been answered;</li><li>4 = test locked (for timeout);</li></ul>
 */
function F_checkTestStatus($user_id, $test_id, $duration) {
	require_once('../config/tce_config.php');
	global $db, $l;
	// get current date-time
	$current_time = date(K_TIMESTAMP_FORMAT);
	$test_status = 0;
	$user_id = intval($user_id);
	$test_id = intval($test_id);
	$duration = intval($duration);
	// get current test status for the selected user
	$sql = 'SELECT testuser_id, testuser_status, testuser_creation_time
		FROM '.K_TABLE_TEST_USER.'
		WHERE testuser_test_id='.$test_id.'
			AND testuser_user_id='.$user_id.'';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$testuser_id = $m['testuser_id'];
			$test_status = $m['testuser_status'];
			$endtime = date(K_TIMESTAMP_FORMAT, strtotime($m['testuser_creation_time']) + ($duration * K_SECONDS_IN_MINUTE));
			if (($test_status > 0) AND ($test_status < 4) AND ($current_time > $endtime)) {
				// update test mode to 4 = test locked (for timeout)
				$sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
					SET testuser_status=4 
					WHERE testuser_id='.$testuser_id.'';
				if(!$ru = F_db_query($sqlu, $db)) {
					F_display_db_error();
				} else {
					$test_status = 4;
				}
			} else {
				switch ($test_status) {
					case 0: { // 0 = the test generation process is started but not completed
						// delete incomplete test (also deletes test logs using database referential integrity)
						$sqld = 'DELETE FROM '.K_TABLE_TEST_USER.' 
							WHERE testuser_id='.$testuser_id.'';
						if(!$rd = F_db_query($sqld, $db)) {
							F_display_db_error();
						}
						break;
					}
					case 1: { // 1 = the test has been successfully created
						// check if all questions were displayed
						if (F_count_rows(K_TABLE_TESTS_LOGS, 'WHERE testlog_testuser_id='.$testuser_id.' AND testlog_display_time IS NULL') == 0) {
							// update test status to 2 = all questions have been displayed to the user
							$sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
								SET testuser_status=2 
								WHERE testuser_id='.$testuser_id.'';
							if(!$ru = F_db_query($sqlu, $db)) {
								F_display_db_error();
							} else {
								$test_status = 2;
							}
						}
						break;
					}
					case 2: { // 2 = all questions have been displayed to the user
						// check if test has been completed in time
						if (F_count_rows(K_TABLE_TESTS_LOGS, 'WHERE testlog_testuser_id='.$testuser_id.' AND testlog_change_time IS NULL') == 0) {
							// update test mode to 3 = all questions have been answered
							$sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
								SET testuser_status=3 
								WHERE testuser_id='.$testuser_id.'';
							if(!$ru = F_db_query($sqlu, $db)) {
								F_display_db_error();
							} else {
								$test_status = 3;
							}
						}
						break;
					}
				} //end switch
			} //end else
		}
	} else {
		F_display_db_error();
	}
	return $test_status;
}

/**
 * Returns XHTML link to open test info popup.
 * @param int $test_id test ID
 * @param string $link_name link caption
 * return XHTML code
 */
function F_testInfoLink($test_id, $link_name='') {
	require_once('../config/tce_config.php');
	global $db, $l;
	$str = '';
	$onclickinfo = 'infoTestWindow=window.open(\'tce_popup_test_info.php?testid='.$test_id.'\'';
	$onclickinfo .= ',\'infoTestWindow\',\'dependent';
	$onclickinfo .= ',height='.K_TEST_INFO_HEIGHT;
	$onclickinfo .= ',width='.K_TEST_INFO_WIDTH;
	$onclickinfo .= ',menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\');';
	$onclickinfo .= 'return false;';
	$str .= '<a href="tce_popup_test_info.php?testid='.$test_id.'" onclick="'.$onclickinfo.'" title="'.$l['m_new_window_link'].'">';
	if (strlen($link_name) > 0) {
		$str .= $link_name;
	} else {
		$str .= $l['w_info'];
	}
	$str .= '</a>';
	return $str;
}

/**
 * Returns an XHTML string containing specified test information.
 * @param int $test_id test ID
 * @param boolean $showip if true display enabled users' IP range
 * @return string containing an XHTML code
 */
function F_printTestInfo($test_id, $showip=false) {
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_tcecode.php');
	global $db, $l;
	$str = ''; //string to return
	$boolval = Array($l['w_no'], $l['w_yes']);
	$sql = 'SELECT * FROM '.K_TABLE_TESTS.' WHERE test_id='.$test_id.'';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			$str .= '<h1>'.htmlspecialchars($m['test_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</h1>'.K_NEWLINE;
			$str .= '<div class="tcecontentbox">'.F_decode_tcecode($m['test_description']).'<br /><br /></div>'.K_NEWLINE;
			$str .= '<div class="tceformbox">'.K_NEWLINE;
			$str .= F_twoColRow($l['w_time_begin'], $l['h_time_begin'], $m['test_begin_time']);
			$str .= F_twoColRow($l['w_time_end'], $l['h_time_end'], $m['test_end_time']);
			$str .= F_twoColRow($l['w_test_time'], $l['h_test_time'], $m['test_duration_time'].' '.$l['w_minutes']);
			$str .= F_twoColRow($l['w_score_right'], $l['h_score_right'], $m['test_score_right']);
			$str .= F_twoColRow($l['w_score_wrong'], $l['h_score_wrong'], $m['test_score_wrong']);
			$str .= F_twoColRow($l['w_score_unanswered'], $l['h_score_unanswered'], $m['test_score_unanswered']);
			$str .= F_twoColRow($l['w_max_score'], $l['w_max_score'], $m['test_max_score']);
			$str .= F_twoColRow($l['w_test_score_threshold'], $l['h_test_score_threshold'], $m['test_score_threshold']);
			$str .= F_twoColRow($l['w_results_to_users'], $l['h_results_to_users'], $boolval[intval(F_getBoolean($m['test_results_to_users']))]);
			$str .= F_twoColRow($l['w_report_to_users'], $l['h_report_to_users'], $boolval[intval(F_getBoolean($m['test_report_to_users']))]);
			$str .= F_twoColRow($l['w_repeatable'], $l['h_repeatable_test'], $boolval[intval(F_getBoolean($m['test_repeatable']))]);
			// Uncomment additional information you want display
			//$str .= F_twoColRow($l['w_random_questions_select'], $l['h_random_questions_select'], $boolval[intval(F_getBoolean($m['test_random_questions_select']))]);
			//$str .= F_twoColRow($l['w_random_questions_order'], $l['h_random_questions_order'], $boolval[intval(F_getBoolean($m['test_random_questions_order']))]);
			//$str .= F_twoColRow($l['w_random_answers_select'], $l['h_random_answers_select'], $boolval[intval(F_getBoolean($m['test_random_answers_select']))]);
			//$str .= F_twoColRow($l['w_random_answers_order'], $l['h_random_answers_order'], $boolval[intval(F_getBoolean($m['test_random_answers_order']))]);
			//$str .= F_twoColRow($l['w_comment_enabled'], $l['h_comment_enabled'], $boolval[intval(F_getBoolean($m['test_comment_enabled']))]);
			//$str .= F_twoColRow($l['w_menu_enabled'], $l['h_menu_enabled'], $boolval[intval(F_getBoolean($m['test_menu_enabled']))]);
			//$str .= F_twoColRow($l['w_noanswer_enabled'], $l['h_noanswer_enabled'], $boolval[intval(F_getBoolean($m['test_noanswer_enabled']))]);
			//$str .= F_twoColRow($l['w_mcma_radio'], $l['h_mcma_radio'], $boolval[intval(F_getBoolean($m['test_mcma_radio']))]);
			if ($showip) {
				$str .= F_twoColRow($l['w_ip_range'], $l['h_ip_range'], $m['test_ip_range']);
			}
			$str .= '<br/>';
		}
	} else {
		F_display_db_error();
	}
	$str .= '</div>';
	return $str;
}

/**
 * Returns the test data.
 * @param int $test_id test ID.
 * @return array containing test data.
 */
function F_getTestData($test_id) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$test_id = intval($test_id);
	$td = Array();
	$sql = 'SELECT *
		FROM '.K_TABLE_TESTS.'
		WHERE test_id='.$test_id.'
		LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		$td = F_db_fetch_array($r);
	} else {
		F_display_db_error();
	}
	return $td;
}

/**
 * Returns the test name.
 * @param int $test_id test ID.
 * @return string test name or empty string in case of error.
 */
function F_getTestName($test_id) {
	$test_id = intval($test_id);
	$td = F_getTestData($test_id);
	return $td['test_name'];
}

/**
 * Returns the test duration time in seconds.
 * @param int $test_id test ID
 * @return int test duration time in seconds
 */
function F_getTestDuration($test_id) {
	require_once('../config/tce_config.php');
	$test_id = intval($test_id);
	$td = F_getTestData($test_id);
	return ($td['test_duration_time'] * K_SECONDS_IN_MINUTE);
}

/**
 * Returns the user's test start time in seconds since UNIX epoch (1970-01-01 00:00:00).
 * @param int $testuser_id user's test ID
 * @return int start time in seconds
 */
function F_getTestStartTime($testuser_id) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$testuser_id = intval($testuser_id);
	$starttime = 0;
	// select test control row (if any)
	$sql = 'SELECT testuser_creation_time 
		FROM '.K_TABLE_TEST_USER.' 
		WHERE testuser_id='.$testuser_id.'';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$starttime = strtotime($m['testuser_creation_time']);
		}
	} else {
		F_display_db_error();
	}
	return $starttime;
}

/**
 * Return a formatted XHTML row to display 2 columns data.<br>
 * See CSS classes:<ul>
 * <li>div.row span.label</li>
 * <li>div.row span.formw</li>
 * </ul>
 * @param string $label string to display on the left column
 * @param string $description string to display on the title attribute of the left column field
 * @param string $value string to display on the right column
 * @return string XHTML code
 */
function F_twoColRow($label="", $description="", $value="") {
	$str = '';
	$str .= '<div class="row">';
	$str .= '<span class="label">';
	$str .= '<span title="'.$description.'">';
	$str .= $label.': ';
	$str .= '</span>';
	$str .= '</span>';
	$str .= '<span class="value">';
	$str .= $value;
	$str .= '</span>';
	$str .= '</div>'.K_NEWLINE;
	return $str;
}

/**
 * Returns true if the current user is authorized to execute the selected test.<br>
 * Generates the test if it's not already generated.
 * @param int $test_id test ID.
 * @return true if user is authorized, false otherwise.
 */
function F_executeTest($test_id) {
	require_once('../config/tce_config.php');
	global $db, $l;
	// get current date-time
	$current_time = date(K_TIMESTAMP_FORMAT);
	$test_id = intval($test_id);
	// select the specified test checking if it's valid for the current time
	$sql = 'SELECT test_id, test_ip_range, test_duration_time 
		FROM '.K_TABLE_TESTS.' 
		WHERE test_id='.$test_id.'
			AND test_begin_time < \''.$current_time.'\'
			AND test_end_time > \''.$current_time.'\'';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			// check user's authorization
			if (F_isValidTestUser($m['test_id'], $_SESSION['session_user_ip'], $m['test_ip_range'])) {
				// the user's IP is valid, check test status
				$test_status = F_checkTestStatus($_SESSION['session_user_id'], $m['test_id'], $m['test_duration_time']);
				switch ($test_status) {
					case 0: { // 0 = test is not yet created
						// create new test session for the current user
						return F_createTest($test_id, $_SESSION['session_user_id']);
						break;
					}
					case 1: // 1 = the test has been successfully created
					case 2: // 2 = all questions have been displayed to the user
					case 3: { // 3 = all questions have been answered
						return true;
						break;
					}
					case 4: { // 4 = test locked (for timeout)
						return false;
						break;
					}
				}
			}
		}
	} else {
		F_display_db_error();
	}
	return false;
}

/**
 * Checks if the current user is the right testlog_id owner.<br>
 * This function is used for security reasons.
 * @param int $test_id test ID
 * @param int $testlog_id test log ID
 * @return boolean TRUE in case of success, FALSE otherwise
 */
function F_isRightTestlogUser($test_id, $testlog_id) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$test_id = intval($test_id);
	$testlog_id = intval($testlog_id);
	// check if the current user is the right testlog_id owner
	$sql = 'SELECT testuser_user_id, testuser_test_id
		FROM '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.'
		WHERE testuser_id=testlog_testuser_id
			AND testlog_id='.$testlog_id.'';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			if(($m['testuser_user_id'] != $_SESSION['session_user_id']) OR ($m['testuser_test_id'] != $test_id)) {
				return false;
			}
		} else {
			return false;
		}
	} else {
		F_display_db_error();
	}
	return true;
}

/**
 * Return an array containing answer_id field of selected answers.<br>
 * @param int $question_id question ID
 * @param boolean $checktype if true checks for answer_isright value on WHERE clause
 * @param int $isright value (0 = false, 1 = true), if non-empty checks for answer_isright value on WHERE clause
 * @param int $ordering ordering type question (0 = false, 1 = true)
 * @param int $limit maximum number of IDs to return
 * @param int $startindex array starting index (default = 0)
 * @param boolean $randorder if true user random order
 * @return array id of selected answers
 */
function F_selectAnswers($question_id, $isright='', $ordering=false, $limit=0, $startindex=0, $randorder=true) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$question_id = intval($question_id);
	$isright = F_escape_sql($isright);
	$limit = intval($limit);
	$answers_ids = array(); // stores answers IDs
	$sql = 'SELECT answer_id, answer_position
		FROM '.K_TABLE_ANSWERS.'
		WHERE answer_question_id='.$question_id.'
		AND answer_enabled=\'1\'';
	if ($ordering) {
		$sql .= ' AND answer_position>0';
	} elseif (strlen($isright) > 0) {
		// MCSA
		$sql .= ' AND answer_isright=\''.$isright.'\'';
	}
	if ($randorder) {
		$sql .= ' ORDER BY RAND()';
	} else {
		$sql .= ' AND answer_position>0 ORDER BY answer_position';
	}
	if ($limit > 0) {
		$sql .= ' LIMIT '.$limit.'';
	}
	if($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			if ($randorder) {
				$answers_ids[$startindex++] = $m['answer_id'];
			} else {
				$answers_ids[$m['answer_position']] = $m['answer_id'];
			}
		}
	} else {
		F_display_db_error(false);
		return false;
	}
	return $answers_ids;
}

/**
 * Add specified answers on tce_tests_logs_answer table.
 * @param int $testlog_id testlog ID
 * @param array $answers_ids array of answer IDs to add
 * @return boolean true in case of success, false otherwise
 */
function F_addLogAnswers($testlog_id, $answers_ids) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$testlog_id = intval($testlog_id);
	$i = 0;
	while (list($key, $answid) = each($answers_ids)) {
		$i++;
		$sqli = 'INSERT INTO '.K_TABLE_LOG_ANSWER.' (
			logansw_testlog_id,
			logansw_answer_id,
			logansw_selected,
			logansw_order
			) VALUES (
			'.$testlog_id.',
			'.$answid.',
			-1,
			'.$i.'
			)';
		if(!$ri = F_db_query($sqli, $db)) {
			F_display_db_error(false);
			return false;
		}
	}
	return true;
}

/**
 * Returns the ID of the tce_tests_users table corresponding to a complete test of $test_id type.
 * @param int $test_id test ID
 * @return int testuser ID
 */
function F_getFirstTestUser($test_id) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$test_id = intval($test_id);
	// check if this is the first test creation
	$firsttest = 0;
	$sql = 'SELECT testuser_id
		FROM '.K_TABLE_TEST_USER.'
		WHERE testuser_test_id='.$test_id.'
			AND testuser_status>0
		LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			$firsttest = $m['testuser_id'];
		}
	} else {
		F_display_db_error(false);
	}
	return $firsttest;
}

/**
 * Creates a new tce_tests_logs table entry and returns inserted ID.
 * @param int $testuser_id ID of tce_tests_users
 * @param int $question_id question ID
 * @param int $score score for unanswered questions
 * @param int $order question display order
 * @return int testlog ID
 */
function F_newTestLog($testuser_id, $question_id, $score, $order, $num_answers=0) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$testuser_id = intval($testuser_id);
	$question_id = intval($question_id);
	$score = floatval($score);
	$sqll = 'INSERT INTO '.K_TABLE_TESTS_LOGS.' (
		testlog_testuser_id,
		testlog_question_id,
		testlog_score,
		testlog_creation_time,
		testlog_reaction_time,
		testlog_order,
		testlog_num_answers
		) VALUES (
		'.$testuser_id.',
		'.$question_id.',
		'.$score.',
		\''.date(K_TIMESTAMP_FORMAT).'\',
		0,
		'.$order.',
		'.$num_answers.'
		)';
	if(!$rl = F_db_query($sqll, $db)) {
		F_display_db_error(false);
		return false;
	}
	// get inserted ID
	return F_db_insert_id($db, K_TABLE_TESTS_LOGS, 'testlog_id');
}

/**
 * Create user's test and returns TRUE on success.
 * @param int $test_id test ID.
 * @param int $user_id user ID.
 * @return boolean TRUE in case of success, FALSE otherwise.
 */
function F_createTest($test_id, $user_id) {
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_tcecode.php');
	global $db, $l;
	$test_id = intval($test_id);
	$user_id = intval($user_id);
	$firsttest = 0; // id of the firts test of this type
	// get test data
	$testdata = F_getTestData($test_id);
	// 1. create user's test entry
	// ------------------------------
	$sql = 'INSERT INTO '.K_TABLE_TEST_USER.' (
		testuser_test_id,
		testuser_user_id,
		testuser_status,
		testuser_creation_time
		) VALUES (
		'.$test_id.',
		'.$user_id.',
		0,
		\''.date(K_TIMESTAMP_FORMAT).'\'
		)';
	if(!$r = F_db_query($sql, $db)) {
		F_display_db_error(false);
		return false;
	} else {
		// get inserted ID
		$testuser_id = F_db_insert_id($db, K_TABLE_TEST_USER, 'testuser_id');
	}
	// get ID of first user's test (if exist)
	$firsttest = F_getFirstTestUser($test_id);
	// select questions
	if (F_getBoolean($testdata['test_random_questions_select']) OR ($firsttest == 0)) {
		// selected questions IDs
		$selected_questions = '0';
		// 2. for each set of subjects
		// ------------------------------
		$sql = 'SELECT *
			FROM '.K_TABLE_TEST_SUBJSET.'
			WHERE tsubset_test_id='.$test_id.'
			ORDER BY tsubset_type, tsubset_difficulty, tsubset_answers DESC';
		if($r = F_db_query($sql, $db)) {
			$questions_data = array();
			while ($m = F_db_fetch_array($r)) {
				// 3. select questions
				// ------------------------------
				$sqlq = 'SELECT question_id, question_type, question_difficulty, question_position
					FROM '.K_TABLE_QUESTIONS.'';
				$sqlq .= ' WHERE question_subject_id IN (
						SELECT subjset_subject_id
						FROM '.K_TABLE_SUBJECT_SET.'
						WHERE subjset_tsubset_id='.$m['tsubset_id'].'';
				$sqlq .= ' )
					AND question_type='.$m['tsubset_type'].' 
					AND question_difficulty='.$m['tsubset_difficulty'].' 
					AND question_enabled=\'1\'
					AND question_id NOT IN ('.$selected_questions.')';
				if ($m['tsubset_type'] == 1) {
					// single question (MCSA)
					// get questions with the right number of answers
					$sqlq .= '  
						AND question_id IN (
							SELECT answer_question_id
							FROM '.K_TABLE_ANSWERS.'
							WHERE answer_enabled=\'1\' 
								AND answer_isright=\'1\'';
					if (!F_getBoolean($testdata['test_random_answers_order'])) {
						$sqlq .= ' AND answer_position>0';
					}
					$sqlq .= ' GROUP BY answer_question_id
							HAVING (COUNT(answer_id)>0)
							)';
					$sqlq .= '  
						AND question_id IN (
							SELECT answer_question_id
							FROM '.K_TABLE_ANSWERS.'
							WHERE answer_enabled=\'1\' 
								AND answer_isright=\'0\'';
					if (!F_getBoolean($testdata['test_random_answers_order'])) {
						$sqlq .= ' AND answer_position>0';
					}
					$sqlq .= ' GROUP BY answer_question_id
							HAVING (COUNT(answer_id)>='.($m['tsubset_answers']-1).')
							)';
				} elseif ($m['tsubset_type'] == 2) {
					// multiple question (MCMA)
					// get questions with the right number of answers
					$sqlq .= '  
						AND question_id IN (
							SELECT answer_question_id
							FROM '.K_TABLE_ANSWERS.'
							WHERE answer_enabled=\'1\'';
					if (!F_getBoolean($testdata['test_random_answers_order'])) {
						$sqlq .= ' AND answer_position>0';
					}
					$sqlq .= ' GROUP BY answer_question_id
							HAVING (COUNT(answer_id)>='.$m['tsubset_answers'].')
							)';
				} elseif ($m['tsubset_type'] == 4) {
					// ordering question
					// get questions with the right number of answers
					$sqlq .= '  
						AND question_id IN (
							SELECT answer_question_id
							FROM '.K_TABLE_ANSWERS.'
							WHERE answer_enabled=\'1\'
							AND answer_position>0
							GROUP BY answer_question_id
							HAVING (COUNT(answer_id)>1)
							)';
				}
				if (F_getBoolean($testdata['test_random_questions_select']) OR F_getBoolean($testdata['test_random_questions_order'])) {
					$sqlq .= ' ORDER BY RAND()';
				} else {
					$sqlq .= ' AND question_position>0 ORDER BY question_position';
				}
				$sqlq .= ' LIMIT '.$m['tsubset_quantity'].'';
				if($rq = F_db_query($sqlq, $db)) {
					while ($mq = F_db_fetch_array($rq)) {
						// store questions data
						$tmp_data = array(
							'id' => $mq['question_id'],
							'type' => $mq['question_type'],
							'answers' => $m['tsubset_answers'],
							'score' => ($testdata['test_score_unanswered'] * $mq['question_difficulty'])
							);
						if (F_getBoolean($testdata['test_random_questions_select']) OR F_getBoolean($testdata['test_random_questions_order'])) {
							$questions_data[] = $tmp_data;
						} else {
							$questions_data[$mq['question_position']] = $tmp_data;
						}
						$selected_questions .= ','.$mq['question_id'].'';
					} // end while select questions
				} else {
					F_display_db_error(false);
					return false;
				} // --- end 3
				
			} // end while for each set of subjects
			// 4. STORE QUESTIONS AND ANSWERS
			// ------------------------------
			if ((!F_getBoolean($testdata['test_random_questions_select'])) AND (!F_getBoolean($testdata['test_random_questions_order']))) {
				// order questions
				ksort($questions_data);
			} else {
				shuffle($questions_data);
			}
			// add questions to database
			$question_order = 0;			
			foreach ($questions_data as $key => $q) {
				$question_order++;
				$testlog_id = F_newTestLog($testuser_id, $q['id'], $q['score'], $question_order, $q['answers']);
				// Add answers
				if (!F_addQuestionAnswers($testlog_id, $q['id'], $q['type'], $q['answers'], $firsttest, $testdata)) {
					return false;
				}
			}
		} else {
			F_display_db_error(false);
			return false;
		} // --- end 2
	} else {
		// same questions for all test-takers
		// ---------------------------------------
		$sql = 'SELECT *
			FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_QUESTIONS.'
			WHERE question_id=testlog_question_id
				AND testlog_testuser_id='.$firsttest.'';
		if (F_getBoolean($testdata['test_random_questions_order'])) {
			$sql .= ' ORDER BY RAND()';
		} else {
			$sql .= ' ORDER BY testlog_order';
		}
		if($r = F_db_query($sql, $db)) {
			$question_order = 0;
			while ($m = F_db_fetch_array($r)) {
				$question_order++;
				// copy values to new user test
				$question_unanswered_score = $testdata['test_score_unanswered'] * $m['question_difficulty'];
				$testlog_id = F_newTestLog($testuser_id, $m['testlog_question_id'], $question_unanswered_score, $question_order, $m['testlog_num_answers']);
				// Add answers
				if (!F_addQuestionAnswers($testlog_id, $m['question_id'], $m['question_type'], $m['testlog_num_answers'], $firsttest, $testdata)) {
					return false;
				}
			}
		} else {
			F_display_db_error(false);
			return false;
		}
	}
	// 6. update user's test status as 1 = the test has been successfully created
	// ------------------------------
	$sql = 'UPDATE '.K_TABLE_TEST_USER.' SET 
		testuser_status=1,
		testuser_creation_time=\''.date(K_TIMESTAMP_FORMAT).'\'
		WHERE testuser_id='.$testuser_id.'';
	if(!$r = F_db_query($sql, $db)) {
		F_display_db_error(false);
		return false;
	}
	return true;
}

/**
 * Add answers to selected question.
 * @param int $testlog_id testlog ID.
 * @param int $question_id question ID.
 * @param int $question_type type of question.
 * @param int $tsubset_answers number of alternative answers to display.
 * @param int $firsttest ID of first test testuser_id.
 * @param array $testdata array of test data.
 * @return boolean TRUE in case of success, FALSE otherwise.
 */
function F_addQuestionAnswers($testlog_id, $question_id, $question_type, $num_answers, $firsttest, $testdata) {
	require_once('../config/tce_config.php');
	global $db, $l;
	if ($question_type == 3) {
		// free text question
		return true;
	}
	$randorder = F_getBoolean($testdata['test_random_answers_order']);
	// for each question
	if (F_getBoolean($testdata['test_random_questions_select']) OR F_getBoolean($testdata['test_random_answers_select']) OR ($firsttest == 0)) {
			$answers_ids = array(); // array used to store answers IDs
			switch ($question_type) {
				case 1: { // MCSA
					// select first random right answer
					$answers_ids += F_selectAnswers($question_id, 1, false, 1, 0, $randorder);
					// select remaining answers
					$answers_ids += F_selectAnswers($question_id, 0, false, ($num_answers - 1), 1, $randorder);
					break;
				}
				case 2: { // MCMA
					// select answers
					$answers_ids += F_selectAnswers($question_id, '', false, $num_answers, 0, $randorder);
					break;
				}
				case 4: { // ORDERING
					// select answers
					$randorder = true;
					$answers_ids += F_selectAnswers($question_id, '', true, 0, 0, $randorder);
					break;
				}
			}
			// randomizes the order of the answers
			if ($randorder) {
				shuffle($answers_ids);
			} else {
				ksort($answers_ids);
			}
			// add answers
			F_addLogAnswers($testlog_id, $answers_ids);
	} else {
		// same answers for all test-takers
		// --------------------------------
		$sql = 'SELECT logansw_answer_id
			FROM '.K_TABLE_LOG_ANSWER.', '.K_TABLE_TESTS_LOGS.'
			WHERE logansw_testlog_id=testlog_id
				AND testlog_testuser_id='.$firsttest.'
				AND testlog_question_id='.$question_id.'';
		if ($randorder) {
			$sql .= ' ORDER BY RAND()';
		} else {
			$sql .= ' ORDER BY logansw_order';
		}
		if($r = F_db_query($sql, $db)) {
			$answers_ids = array();
			while ($m = F_db_fetch_array($r)) {
				$answers_ids[] = $m['logansw_answer_id'];
			}
			F_addLogAnswers($testlog_id, $answers_ids);
		} else {
			F_display_db_error(false);
			return false;
		}
	}
	return true;
}

/**
 * Updates question log data (register user's answers and calculate scores).
 * @param int $test_id test ID
 * @param int $testlog_id test log ID
 * @param array $answer_id answer_id form field value
 * @param string $answer_text answer text
 * @param int $reaction_time reaction time in milliseconds
 * @return boolean TRUE in case of success, FALSE otherwise
 */
function F_updateQuestionLog($test_id, $testlog_id, $answer_id=0, $answer_text='', $reaction_time=0) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$question_id = 0; // question ID
	$question_type = 3; // question type
	$question_difficulty = 1; // question difficulty
	$oldtext = ''; // old text answer
	$answer_changed = false; // true when answer change
	$answer_score = 0; // answer total score
	$num_answers = 0; // counts alternative answers
	$test_id = intval($test_id);
	$testlog_id = intval($testlog_id);
	$answer_text = F_escape_sql($answer_text);
	// get test data
	$testdata = F_getTestData($test_id);
	// get question information
	$sql = 'SELECT *
		FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_QUESTIONS.'
		WHERE testlog_question_id=question_id
			AND testlog_id='.$testlog_id.'
		LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			// get previous answer text
			$oldtext = $m['testlog_answer_text'];
			$question_id = $m['question_id'];
			$question_type = $m['question_type'];
			$question_difficulty = $m['question_difficulty'];
		}
	} else {
		F_display_db_error();
		return false;
	}
	// calculate question score
	$question_right_score = $testdata['test_score_right'] * $question_difficulty;
	$question_wrong_score = $testdata['test_score_wrong'] * $question_difficulty;
	$question_unanswered_score = $testdata['test_score_unanswered'] * $question_difficulty;
	if ($question_type != 3) {
		$sql = 'SELECT *
			FROM '.K_TABLE_LOG_ANSWER.', '.K_TABLE_ANSWERS.'
			WHERE logansw_answer_id=answer_id
				AND logansw_testlog_id='.$testlog_id.'
			ORDER BY logansw_order';
		if($r = F_db_query($sql, $db)) {
			while (($m = F_db_fetch_array($r))) {
				$num_answers++;
				// update each answer
				$sqlu = 'UPDATE '.K_TABLE_LOG_ANSWER.' SET';
				switch ($question_type) {
					case 1: {
						// MCSA - Multiple Choice Single Answer
						if ($answer_id == 0) {
							// unanswered
							$answer_score = $question_unanswered_score;
							if ($m['logansw_selected'] != -1) {
								$answer_changed = true;
							}
							$sqlu .= ' logansw_selected=-1';
						} elseif ($answer_id == $m['logansw_answer_id']) {
							// selected
							if (F_getBoolean($m['answer_isright'])) {
								$answer_score = $question_right_score;
							} else {
								$answer_score = $question_wrong_score;
							}
							if ($m['logansw_selected'] != 1) {
								$answer_changed = true;
							}
							$sqlu .= ' logansw_selected=1';
						} else {
							// unselected
							if ($m['logansw_selected'] == 1) {
								$answer_changed = true;
							}
							$sqlu .= ' logansw_selected=0';
						}
						break;
					}
					case 2: {
						// MCMA - Multiple Choice Multiple Answer
						if (isset($answer_id[$m['logansw_answer_id']])) {
							// radiobutton or selected checkbox
							$answer_id[$m['logansw_answer_id']] = intval($answer_id[$m['logansw_answer_id']]);
							if ($answer_id[$m['logansw_answer_id']] == -1) {
								// unanswered
								$answer_score += $question_unanswered_score;
							} elseif (F_getBoolean($m['answer_isright']) AND ($answer_id[$m['logansw_answer_id']] == 1)) {
								// right (selected)
								$answer_score += $question_right_score;
							} elseif (!F_getBoolean($m['answer_isright']) AND ($answer_id[$m['logansw_answer_id']] == 0)) {
								// right (unselected)
								$answer_score += $question_right_score;
							} else {
								// wrong
								$answer_score += $question_wrong_score;
							}
							if ($m['logansw_selected'] != $answer_id[$m['logansw_answer_id']]) {
								$answer_changed = true;
							}
							$sqlu .= ' logansw_selected='.$answer_id[$m['logansw_answer_id']].'';
						} else {
							// unselected checkbox
							if (F_getBoolean($m['answer_isright'])) {
								$answer_score += $question_wrong_score;
							} else {
								$answer_score += $question_right_score;
							}
							if ($m['logansw_selected'] != 0) {
								$answer_changed = true;
							}
							$sqlu .= ' logansw_selected=0';
						}
						break;
					}
					case 4: {
						// ORDER
						if (isset($answer_id[$m['logansw_answer_id']]) AND ($answer_id[$m['logansw_answer_id']] > 0)) {
							// selected
							$answer_id[$m['logansw_answer_id']] = intval($answer_id[$m['logansw_answer_id']]);
							if ($answer_id[$m['logansw_answer_id']] == $m['answer_position']) {
								$answer_score += $question_right_score;
							} else {
								$answer_score += $question_wrong_score;
							}
							if ($answer_id[$m['logansw_answer_id']] != $m['logansw_position']) {
								$answer_changed = true;
							}
							$sqlu .= ' logansw_position='.$answer_id[$m['logansw_answer_id']].', logansw_selected=1';
						} else {
							// unanswered
							$answer_score += $question_unanswered_score;
							if ($m['logansw_position'] > 0) {
								$answer_changed = true;
							}
							$sqlu .= ' logansw_selected=-1, logansw_position=0';
						}
						break;
					}
				} // end of switch
				$sqlu .= ' WHERE logansw_testlog_id='.$testlog_id.' AND logansw_answer_id='.$m['logansw_answer_id'].'';
				if(!$ru = F_db_query($sqlu, $db)) {
					F_display_db_error();
					return false;
				}
			}
			if ($question_type > 1) {
				// normalize score
				$answer_score = round(($answer_score / $num_answers), 3);
			}
		} else {
			F_display_db_error();
			return false;
		}
	}
	// update log if answer is changed
	if ($answer_changed OR ($oldtext != $answer_text)) {
		if (strlen($answer_text) > 0) {
			$answer_score = 'NULL';
			// check exact answers score
			$sql = 'SELECT *
				FROM '.K_TABLE_ANSWERS.'
				WHERE answer_question_id='.$question_id.'
					AND answer_enabled=\'1\' 
					AND answer_isright=\'1\'';
			if($r = F_db_query($sql, $db)) {
				while ($m = F_db_fetch_array($r)) {
					if (strcasecmp(trim($answer_text), $m['answer_description']) == 0) {
						$answer_score += $question_right_score;
						break;
					}
				}
			}  else {
				F_display_db_error();
				return false;
			}
		}
		if (($question_type == 1) AND ($answer_id == 0)) {
			$change_time = '';
		} else {
			$change_time = date(K_TIMESTAMP_FORMAT);
		}
		$sqlu = 'UPDATE '.K_TABLE_TESTS_LOGS.' SET';
		$sqlu .= ' testlog_answer_text='.F_empty_to_null($answer_text).',';
		$sqlu .= ' testlog_score='.$answer_score.',';
		$sqlu .= ' testlog_change_time='.F_empty_to_null($change_time).',';
		$sqlu .= ' testlog_reaction_time='.intval($reaction_time).',';
		$sqlu .= ' testlog_user_ip=\''.getNormalizedIP($_SERVER['REMOTE_ADDR']).'\'';
		$sqlu .= ' WHERE testlog_id='.$testlog_id.'';
		if(!$ru = F_db_query($sqlu, $db)) {
			F_display_db_error();
			return false;
		}
	}
	return true;
}

/**
 * Returns a formatted XHTML form code to handle the specified question.<br>
 * Form fields names are: answer_text, answer_id<br>
 * CSS classes:<ul>
 * <li>div.tcecontentbox</li>
 * <li>div.rowl</li>
 * <li>textarea.answertext</li>
 * </ul>
 * @param int $test_id test ID
 * @param int $testlog_id test log ID
 * @param string $formname form name (form ID)
 * @return string XHTML code
 */
function F_questionForm($test_id, $testlog_id, $formname) {
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_tcecode.php');
	global $db, $l, $examtime;
	$test_id = intval($test_id);
	$testlog_id = intval($testlog_id);
	$user_id = intval($_SESSION['session_user_id']);
	$aswkeys = array();
	$str = '';
	if (!isset($test_id) OR ($test_id == 0)) {
		return;
	}
	$testdata = F_getTestData($test_id);
	$noanswer_hidden = '';
	$noanswer_disabled = '';
	if (!F_getBoolean($testdata['test_noanswer_enabled'])) {
		$noanswer_hidden = ' style="visibility:hidden;display:none;"';
		$noanswer_disabled = ' readonly="readonly" disabled="disabled"';
	}
	// select question for the first time
	if (!isset($testlog_id) OR ($testlog_id == 0)) {
		//select first question
		$sql = 'SELECT testlog_id
			FROM '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.' 
			WHERE testlog_testuser_id=testuser_id
				AND testuser_test_id='.$test_id.'
				AND testuser_user_id='.$user_id.'
			ORDER BY testlog_id
			LIMIT 1';
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				$testlog_id = $m['testlog_id'];
			} else {
				return;
			}
		} else {
			F_display_db_error();
		}	
	}
	// build selection query for question to display
	$sql = 'SELECT *
			FROM '.K_TABLE_QUESTIONS.', '.K_TABLE_TESTS_LOGS.' 
			WHERE question_id=testlog_question_id
				AND testlog_id='.$testlog_id.'
			LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			if (F_getBoolean($m['question_fullscreen'])) {
				// hide some section for fullscreen mode
				echo '<style>'.K_NEWLINE;
				echo '.header{visibility:hidden;display:none;}'.K_NEWLINE;
				echo '.infolink{visibility:hidden;display:none;}'.K_NEWLINE;
				echo 'h1{visibility:hidden;display:none;}'.K_NEWLINE;
				echo '.pagehelp{visibility:hidden;display:none;}'.K_NEWLINE;
				echo '.userbar{visibility:hidden;display:none;}'.K_NEWLINE;
				echo '.minibutton{visibility:hidden;display:none;}'.K_NEWLINE;
				echo '.navlink{visibility:hidden;display:none;}'.K_NEWLINE;
				echo '.testcomment{visibility:hidden;display:none;}'.K_NEWLINE;
				echo '#terminatetest{visibility:hidden;display:none;}'.K_NEWLINE;
				echo '</style>'.K_NEWLINE;
			}
			$str .= '<input type="hidden" name="testid" id="testid" value="'.$test_id.'" />'.K_NEWLINE;
			$str .= '<input type="hidden" name="testlogid" id="testlogid" value="'.$testlog_id.'" />'.K_NEWLINE;
			$str .= '<input type="hidden" name="testuser_id" id="testuser_id" value="'.$m['testlog_testuser_id'].'" />'.K_NEWLINE;
			// store time information for interactive timer
			$examtime = F_getTestStartTime($m['testlog_testuser_id']) + F_getTestDuration($test_id);
			$str .= '<input type="hidden" name="examtime" id="examtime" value="'.$examtime.'" />'.K_NEWLINE;
			$str .= '<a name="questionsection" id="questionsection"></a>'.K_NEWLINE;
			$str .= '<div class="tcecontentbox">'.K_NEWLINE;
			//fieldset
			//$str .= '<legend>';
			//$str .= $l['w_question'];
			//$str .= '</legend>'.K_NEWLINE;
			// display question description
			if ($m['question_type'] == 3) {
				$str .= '<label for="answertext">';
			}
			$str .= F_decode_tcecode($m['question_description']).K_NEWLINE;
			if ($m['question_type'] == 3) {
				$str .= '</label>';
			}
			$str .= '<div class="row">'.K_NEWLINE;
			$str .= '<hr/>'.K_NEWLINE;
			$str .= '</div>'.K_NEWLINE;
			$str .= '<div class="rowl">'.K_NEWLINE;
			if ($m['question_type'] == 3) {
				// TEXT - free text question
				$str .= '<textarea cols="'.K_ANSWER_TEXTAREA_COLS.'" rows="'.K_ANSWER_TEXTAREA_ROWS.'" name="answertext" id="answertext" class="answertext">';
				$str .= $m['testlog_answer_text'];
				$str .= '</textarea>'.K_NEWLINE;
			} else {
				// multiple-choice question
				$checked = false;
				if (F_getBoolean($m['question_inline_answers'])) {
					// inline display
					$str .= '<ol class="answer_inline">'.K_NEWLINE;
				} else {
					$str .= '<ol class="answer">'.K_NEWLINE;
				}
				if ($m['question_type'] == 4) {
					// get max positions for odering questions
					$max_position = F_count_rows(K_TABLE_LOG_ANSWER, 'WHERE logansw_testlog_id='.$testlog_id.'');
				}
				// display answer options
				$sqla = 'SELECT *
					FROM '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.'
					WHERE logansw_answer_id=answer_id
						AND logansw_testlog_id='.$testlog_id.'
					ORDER BY logansw_order';
				if($ra = F_db_query($sqla, $db)) {
					while($ma = F_db_fetch_array($ra)) {
						$str .= "<li>";
						switch ($m['question_type']) {
							case 1: {
								// MCSA - single-answer question
								$str .= '<input type="radio" name="answerid" id="answerid_'.$ma['answer_id'].'" value="'.$ma['answer_id'].'"';
								if (intval($ma['logansw_selected']) == 1) {
									$str .= ' checked="checked"';
									$checked = true;
								}
								if (F_getBoolean($m['question_auto_next'])) {
									$str .= " onclick=\"var submittime=new Date();document.getElementById('reaction_time').value=submittime.getTime()-document.getElementById('display_time').value;document.getElementById('autonext').value=1;document.getElementById('".$formname."').submit();\"";
								}
								$str .= ' />&nbsp;';
								$str .= '<label for="answerid_'.$ma['answer_id'].'">';
								$str .= F_decode_tcecode($ma['answer_description']);
								$str .= '</label>';
								if ($ma['answer_keyboard_key'] > 0) {
									$aswkeys[$ma['answer_keyboard_key']] = 'answerid_'.$ma['answer_id'];
								}
								break;
							} 
							case 2: {
								// MCMA - multiple-answer question
								if (F_getBoolean($testdata['test_mcma_radio'])) {
									// radiobuttons
									
									// no-answer option
									$str .= '<span style="background-color:#DDDDDD;"'.$noanswer_hidden.'>&nbsp;';
									$str .= '<label for="answerid_'.$ma['answer_id'].'u" title="'.$l['m_unanswered'].'">'.$l['w_unanswered_acronym'].'</label>';
									$str .= '<input type="radio"'.$noanswer_disabled.' name="answerid['.$ma['answer_id'].']" id="answerid_'.$ma['answer_id'].'u" value="-1" title="'.$l['m_unanswered'].'"';
									if (intval($ma['logansw_selected']) == -1) {
										$str .= ' checked="checked"';
									}
									$str .= ' />';
									$str .= '</span>&nbsp;';
									
									// false option
									$str .= '<span style="background-color:#FFBBBB;">&nbsp;';
									$str .= '<label for="answerid_'.$ma['answer_id'].'f" title="'.$l['w_false'].'">'.$l['w_false_acronym'].'</label>';
									$str .= '<input type="radio" name="answerid['.$ma['answer_id'].']" id="answerid_'.$ma['answer_id'].'f" value="0"';
									if (intval($ma['logansw_selected']) == 0) {
										$str .= ' checked="checked"';
									}
									$str .= ' />';
									$str .= '</span>&nbsp;';
									
									// true option
									$str .= '<span style="background-color:#BBFFBB;">&nbsp;';
									$str .= '<label for="answerid_'.$ma['answer_id'].'t" title="'.$l['w_true'].'">'.$l['w_true_acronym'].'</label>';
									$str .= '<input type="radio" name="answerid['.$ma['answer_id'].']" id="answerid_'.$ma['answer_id'].'t" value="1"';
									if (intval($ma['logansw_selected']) == 1) {
										$str .= ' checked="checked"';
									}
									$str .= ' />';
									$str .= '</span>&nbsp;';
									if ($ma['answer_keyboard_key'] > 0) {
										$aswkeys[] = array($ma['answer_keyboard_key'] => 'answerid_'.$ma['answer_id'].'t');
									}
									
									$str .= F_decode_tcecode($ma['answer_description']);
								} else {
									// checkbox
									$str .= '<input type="checkbox" name="answerid['.$ma['answer_id'].']" id="answerid_'.$ma['answer_id'].'" value="1"';
									if (intval($ma['logansw_selected']) == 1) {
										$str .= ' checked="checked"';
										$checked = true;
									}
									$str .= ' />&nbsp;';
									$str .= '<label for="answerid_'.$ma['answer_id'].'">';
									$str .= F_decode_tcecode($ma['answer_description']);
									$str .= '</label>';
								}
								break;
							}
							case 4: {
								// ORDER - ordering questions
								$str .= '<select name="answerid['.$ma['answer_id'].']" id="answerid_'.$ma['answer_id'].'" size="0">'.K_NEWLINE;
								if (F_getBoolean($testdata['test_noanswer_enabled'])) {
									$str .= '<option value="0">&nbsp;</option>'.K_NEWLINE;
								}
								for ($pos=1; $pos <= $max_position; $pos++) {
									$str .= '<option value="'.$pos.'"';
									if ($pos == $ma['logansw_position']) {
										$str .= ' selected="selected"';
									}
									$str .= '>'.$pos.'</option>'.K_NEWLINE;
								}
								$str .= '</select>'.K_NEWLINE;
								$str .= '<label for="answerid_'.$ma['answer_id'].'">';
								$str .= F_decode_tcecode($ma['answer_description']);
								$str .= '</label>';
								break;
							}
						} // end of switch
						$str .= "</li>\n";
					} // end of while
				} else {
					F_display_db_error();
				}
				if ($m['question_type'] == 1) {
					// display default "unanswered" option for MCSA
					$str .= '<li'.$noanswer_hidden.'>';
					$str .= '<input type="radio"'.$noanswer_disabled.' name="answerid" id="answerid_0" value="0"';
					if (!$checked) {
						$str .= ' checked="checked"';
					}
					$str .= ' />&nbsp;';
					$str .= '<label for="answerid_0">';
					$str .= $l['m_unanswered'];
					$str .= '</label>';
					$str .= '</li>'.K_NEWLINE;
				}
				
				$str .= '</ol>'.K_NEWLINE;
			} // end multiple answers
			$str .= '</div>'.K_NEWLINE;
			$str .= '</div>'.K_NEWLINE; //fieldset
			// script to handle keyboard events
			$str .= '<script type="text/javascript">'.K_NEWLINE;
			$str .= '//<![CDATA['.K_NEWLINE;
			$str .= 'function actionByChar(e){var keynum;if(window.event){keynum=e.keyCode;}else if(e.which){keynum=e.which;}switch(keynum){'.K_NEWLINE;
			foreach ($aswkeys as $key => $fieldid) {
				$str .= 'case '.$key.':{document.getElementById(\''.$fieldid.'\').checked=true;var submittime=new Date();document.getElementById(\'reaction_time\').value=submittime.getTime()-document.getElementById(\'display_time\').value;document.getElementById(\'autonext\').value=1;document.getElementById(\''.$formname.'\').submit();break;}'.K_NEWLINE;
			}
			$str .= '}}'.K_NEWLINE;
			$str .= 'document.onkeypress = actionByChar;'.K_NEWLINE;
			if ($m['question_timer'] > 0) {
				// automatic submit form after specified fime
				$str .= "setTimeout('document.getElementById(\'".$formname."\').submit();', ".($m['question_timer'] * 1000).");".K_NEWLINE;
			}
			$str .= '//]]>'.K_NEWLINE;
			$str .= '</script>'.K_NEWLINE;
			// display questions menu
			$str .= F_questionsMenu($testdata, $m['testlog_testuser_id'], $testlog_id, F_getBoolean($m['question_fullscreen']));
		}
		if (empty($m['testlog_display_time'])) {
			// mark test as displayed:
			$sqlu = 'UPDATE '.K_TABLE_TESTS_LOGS.' 
				SET testlog_display_time=\''.date(K_TIMESTAMP_FORMAT).'\' 
				WHERE testlog_id='.$testlog_id.'';
			if(!$ru = F_db_query($sqlu, $db)) {
				F_display_db_error();
			}
		}
	} else {
		F_display_db_error();
	}
	return $str;
}

/**
 * Returns a questions menu and navigator buttons.<br>
 * @param array $testdata test data
 * @param int $testuser_id user's test ID
 * @param boolean $disable if TRUE disable the questions list.
 * @return string XHTML code
 */
function F_questionsMenu($testdata, $testuser_id, $testlog_id=0, $disable=false) {
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_tcecode.php');
	global $db, $l;
	$testuser_id = intval($testuser_id);
	$testlog_id = intval($testlog_id);
	$str = '';
	$testlog_id_prev = 0; // previous question ID
	$testlog_id_next = 0; // next question ID
	$testlog_id_last = 0; // temp variable
	$sql = 'SELECT question_description, question_difficulty, testlog_id, testlog_answer_text, testlog_display_time, testlog_change_time
		FROM '.K_TABLE_QUESTIONS.', '.K_TABLE_TESTS_LOGS.' 
		WHERE question_id=testlog_question_id 
			AND testlog_testuser_id='.$testuser_id.'
		ORDER BY testlog_id';
	if($r = F_db_query($sql, $db)) {
		while($m = F_db_fetch_array($r)) {
			if ($m['testlog_id'] != $testlog_id) {
				$str .= '<li>';
				$str .= '<input type="submit" name="jumpquestion_'.$m['testlog_id'].'" id="jumpquestion_'.$m['testlog_id'].'" value="&gt;" title="'.F_tcecodeToTitle($m['question_description']).'" /> ';
				if ($testlog_id_last == $testlog_id) {
					$testlog_id_next = $m['testlog_id'];
				}
			} else {
				$str .= '<li class="selected">';
				$str .= '<input type="button" name="jumpquestion_'.$m['testlog_id'].'" id="jumpquestion_'.$m['testlog_id'].'" value="&gt;" title="'.F_tcecodeToTitle($m['question_description']).'" disabled="disabled"/> ';
				$testlog_id_prev = $testlog_id_last;
			}
			// display mark when the current question has been displayed
			$str .= '<acronym';
			if (!empty($m['testlog_display_time'])) {
				$str .= ' class="onbox"';
				$str .= ' title="'.$l['h_question_displayed'].'">+';
			} else {
				$str .= ' class="offbox"';
				$str .= ' title="'.$l['h_question_not_displayed'].'">-';
			}
			$str .= '</acronym>';
		
			$str .= '&nbsp;';
		
			// show mark when the current question has been answered
			$str .= '<acronym';
			if (!empty($m['testlog_change_time'])) {
				$str .= ' class="onbox"';
				$str .= ' title="'.$l['h_question_answered'].'">+';
			} else {
				$str .= ' class="offbox"';
				$str .= ' title="'.$l['h_question_not_answered'].'">-';
			}
			$str .= '</acronym>';
			$str .= '&nbsp;';
			// show question score
			$n_question_score = $testdata['test_score_right'] * $m['question_difficulty'];
			$str .= '<acronym class="offbox" title="'.$l['w_max_score'].': '.$n_question_score.'">';
			$str .= sprintf('% 5.1f', $n_question_score);
			$str .= '</acronym>';
			$str .= '&nbsp;';
			if ($testlog_id == 0) {
				$testlog_id = $m['testlog_id'];
				$testlog_id_last = $testlog_id;
			}
			$testlog_id_last = $m['testlog_id'];
			$str .= F_tcecodeToLine($m['question_description']);
			$str .= '</li>'.K_NEWLINE;
		}
	} else {
		F_display_db_error();
	}
	// build quick navigator links (previous - next)
	$navlink = '';
	
	// button for previous question
	$navlink .= '<input type="submit" name="prevquestion" id="prevquestion" value="&lt; '.$l['w_previous'].'" title="'.$l['w_previous'].'"';
	if (($testlog_id_prev <= 0) OR ($testlog_id_prev > $testlog_id)) {
		$navlink .= ' disabled="disabled"';
	}
	$navlink .= ' />';
	
	// button for confirm current question
	$navlink .= '<input type="submit" name="confirmanswer" id="confirmanswer" value="'.$l['w_confirm'].'" />';
	
	// button for next question
	$navlink .= '<input type="submit" name="nextquestion" id="nextquestion" value="'.$l['w_next'].' &gt;" title="'.$l['w_next'].'"';
	if ($testlog_id_next <= 0) {
		$navlink .= ' disabled="disabled"';
	}
	$navlink .= ' />'.K_NEWLINE;
	
	$navlink .= '<input type="hidden" name="prevquestionid" id="prevquestionid" value="'.$testlog_id_prev.'" />'.K_NEWLINE;
	$navlink .= '<input type="hidden" name="nextquestionid" id="nextquestionid" value="'.$testlog_id_next.'" />'.K_NEWLINE;
	$navlink .= '<input type="hidden" name="autonext" id="autonext" value="" />'.K_NEWLINE;
	$navlink = '<div class="navlink">'.$navlink.'</div>'.K_NEWLINE;
	$rstr = '';
	$rstr .= '<br />'.K_NEWLINE;
	$rstr .= $navlink;
	$rstr .= '<br />'.K_NEWLINE;
	if (F_getBoolean($testdata['test_menu_enabled']) AND (!$disable)) {
		// display questions menu
		$rstr .= '<a name="questionssection" id="questionssection"></a>'.K_NEWLINE;
		$rstr .= '<div class="tcecontentbox">'.K_NEWLINE; //fieldset
		//$rstr .= '<legend>';
		$rstr .= $l['w_questions'];
		//$rstr .= '</legend>'.K_NEWLINE;
		$rstr .= '<ol class="qlist">'."\n".$str.'</ol>'.K_NEWLINE;
		$rstr .= '</div>'.K_NEWLINE; //fieldset
		$rstr .= '<br />'.K_NEWLINE;
	}
	return $rstr;
}

/**
 * Display a textarea for user's comment.<br>
 * @param int $test_id test ID
 * @return string XHTML code
 * @since 4.0.000 (2006-10-01)
 */
function F_testComment($test_id) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$test_id = intval($test_id);
	$td = F_getTestData($test_id);
	$user_id = intval($_SESSION['session_user_id']);
	$str = '';
	// user's comment
	if (F_getBoolean($td['test_comment_enabled'])) {
		// get user's test comment
		$comment = '';
		$sql = 'SELECT testuser_comment
		FROM '.K_TABLE_TEST_USER.'
		WHERE testuser_user_id='.$user_id.'
			AND testuser_test_id='.$test_id.'
		LIMIT 1';
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				$comment = $m['testuser_comment'];
			}
		} else {
			F_display_db_error();
		}
		$str .= '<label for="testcomment">'.$l['w_comment'].'</label><br />';
		$str .= '<textarea cols="'.K_ANSWER_TEXTAREA_COLS.'" rows="4" name="testcomment" id="testcomment" class="answertext" title="'.$l['h_testcomment'].'">'.$comment.'</textarea><br />'.K_NEWLINE;
	}
	return $str;
}

/**
 * Updates user's test comment.<br>
 * @param int $test_id test ID
 * @param string $comment user's comment.
 * @return string XHTML code
 * @since 4.0.000 (2006-10-01)
 */
function F_updateTestComment($test_id, $testcomment) {
	require_once('../config/tce_config.php');
	global $db, $l;
	$test_id = intval($test_id);
	$testcomment = F_escape_sql($testcomment);
	$user_id = intval($_SESSION['session_user_id']);
	$sql = 'UPDATE '.K_TABLE_TEST_USER.'
		SET testuser_comment=\''.$testcomment.'\'
		WHERE testuser_test_id='.$test_id.'
			AND testuser_user_id='.$user_id.'';
	if(!$r = F_db_query($sql, $db)) {
		F_display_db_error();
	}
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
