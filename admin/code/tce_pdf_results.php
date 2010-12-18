<?php
//============================================================+
// File name   : tce_pdf_results.php
// Begin       : 2004-06-10
// Last Update : 2010-12-06
//
// Description : Create PDF document to display test results
//               summary for all users.
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
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com S.r.l.
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
 * Create PDF document to display users' tests results.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-06-11
 * @param $_REQUEST['mode'] (int) document mode: 1=all users results, 2=questions stats, 3=detailed report for single user; 4=detailed report for all users; 5=detailed report for all users with only TEXT questions.
 * @param $_REQUEST['testid'] (int) test ID
 * @param $_REQUEST['userid'] (int) user ID
 * @param $_REQUEST['orderfield'] (string) ORDER BY portion of SQL selection query
 */

/**
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('../../shared/config/tce_pdf.php');
require_once('../../shared/code/tcpdfex.php');
require_once('tce_functions_statistics.php');
require_once('tce_functions_user_select.php');

if(!isset($_REQUEST['mode'])) {
	$_REQUEST['mode'] = '';
}

$numberfont = 'courier';

if (isset($_REQUEST['testid']) AND ($_REQUEST['testid'] > 0)) {
	$test_id = intval($_REQUEST['testid']);
	if (!isset($_REQUEST['email'])) {
		if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
			exit;
		}
	}
} else {
	exit;
}
if (isset($_REQUEST['groupid']) AND ($_REQUEST['groupid'] > 0)) {
	$group_id = intval($_REQUEST['groupid']);
	if (!isset($_REQUEST['email'])) {
		if (!F_isAuthorizedEditorForGroup($group_id)) {
			F_print_error('ERROR', $l['m_authorization_denied']);
			exit;
		}
	}
} else {
	$group_id = 0;
}
if (isset($_REQUEST['userid']) AND ($_REQUEST['userid'] > 1)) {
	$user_id = intval($_REQUEST['userid']);
	if (!isset($_REQUEST['email'])) {
		if (!F_isAuthorizedEditorForUser($user_id)) {
			F_print_error('ERROR', $l['m_authorization_denied']);
			exit;
		}
	}
} else {
	$user_id = 0;
}

if (isset($_REQUEST['email']) AND ($_REQUEST['email'] != md5(date('Y').K_RANDOM_SECURITY.$test_id.$user_id))) {
	F_print_error('ERROR', $l['m_authorization_denied']);
	exit;
}

switch ($_REQUEST['mode']) {
	case 1: {
		// all users results
		$doc_title = unhtmlentities($l['t_result_all_users']);
		$doc_description = F_compact_string(unhtmlentities($l['hp_result_alluser']));
		$page_elements = 9;
		$temp_order_field = 'total_score, user_lastname, user_firstname';
		break;
	}
	case 2: {
		// questions stats
		$doc_title = unhtmlentities($l['t_result_questions']);
		$doc_description = F_compact_string(unhtmlentities($l['hp_result_questions']));
		$page_elements = 9;
		$temp_order_field = 'recurrence DESC, average_score DESC';
		break;
	}
	case 3: // detailed report for specific user
	case 4: // detailed report for all users
	case 5: { // detailed report for all users with only open questions
		$doc_title = unhtmlentities($l['t_result_user']);
		$doc_description = F_compact_string(unhtmlentities($l['hp_result_user']));
		$page_elements = 7;
		$temp_order_field = '';
		if (isset($_REQUEST['userid']) AND $_REQUEST['userid']) {
			$user_id = $_REQUEST['userid'];
		}
		$qtype = array('S', 'M', 'T', 'O'); // question types
		break;
	}
	default: {
		echo $l['m_authorization_denied'];
		exit;
	}
}

// set sql select limit
if ($_REQUEST['mode'] == 4) {
	$sql_limit = '';
} else {
	$sql_limit = ' LIMIT 1';
}

// order fields for SQL query
if(isset($_REQUEST['orderfield'])) {
	$full_order_field = urldecode($_REQUEST['orderfield']);
} else {
	$full_order_field = $temp_order_field;
}

// --- create pdf document

if ($l['a_meta_dir'] == 'rtl') {
	$dirlabel = 'L';
	$dirvalue = 'R';
} else {
	$dirlabel = 'R';
	$dirvalue = 'L';
}

$isunicode = (strcasecmp($l['a_meta_charset'], 'UTF-8') == 0);
//create new PDF document (document units are set by default to millimeters)
$pdf = new TCPDFEX(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, $isunicode);

// Set backlink QR-Code
if ($_REQUEST['mode'] == 1) {
	$pdf->setTCExamBackLink(K_PATH_URL.'admin/code/tce_show_result_allusers.php?test_id='.$test_id.'&group_id='.$group_id);
} elseif ($_REQUEST['mode'] == 2) {
	$pdf->setTCExamBackLink(K_PATH_URL.'admin/code/tce_show_result_questions.php?test_id='.$test_id);
}

// set document information
$pdf->SetCreator('TCExam ver.'.K_TCEXAM_VERSION.'');
$pdf->SetAuthor(PDF_AUTHOR);
$pdf->SetTitle($doc_title);
$pdf->SetSubject($doc_description);
$pdf->SetKeywords('TCExam, '.$doc_title);

$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->setLanguageArray($l); //set language items

//initialize document
$pdf->AliasNbPages();

if (defined('K_DIGSIG_ENABLE') AND K_DIGSIG_ENABLE) {
	// set document signature
	$pdf->setSignature(K_DIGSIG_CERTIFICATE, K_DIGSIG_PRIVATE_KEY, K_DIGSIG_PASSWORD, K_DIGSIG_EXTRA_CERTS, K_DIGSIG_CERT_TYPE, array('Name'=>K_DIGSIG_NAME, 'Location'=>K_DIGSIG_LOCATION, 'Reason'=>K_DIGSIG_REASON, 'ContactInfo'=>K_DIGSIG_CONTACT));
}

// calculate some sizes
$cell_height_ratio = (K_CELL_HEIGHT_RATIO + 0.1);
$page_width = $pdf->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT;
$data_cell_height = round(($cell_height_ratio * PDF_FONT_SIZE_DATA) / $pdf->getScaleFactor(), 2);
$main_cell_height = round(($cell_height_ratio * PDF_FONT_SIZE_MAIN) / $pdf->getScaleFactor(), 2);
$data_cell_width = round($page_width / $page_elements, 2);
$data_cell_width_third = round($data_cell_width / 3, 2);
$data_cell_width_half = round($data_cell_width / 2, 2);


// get test data
$sql = 'SELECT *
	FROM '.K_TABLE_TESTS.'
	WHERE test_id='.$test_id.'';
if($r = F_db_query($sql, $db)) {
	if($m = F_db_fetch_array($r)) {
		$test_id = $m['test_id'];
		$test_name = $m['test_name'];
		$test_description = $m['test_description'];
		$test_start_time = $m['test_begin_time'];
		$test_end_time = $m['test_end_time'];
		$test_duration_time = $m['test_duration_time'];
		$test_ip_range = $m['test_ip_range'];
		$test_score_right = $m['test_score_right'];
		$test_score_wrong = $m['test_score_wrong'];
		$test_score_unanswered = $m['test_score_unanswered'];
		$test_max_score = $m['test_max_score'];
		$test_score_threshold = $m['test_score_threshold'];
		/*
		// Additional test information that could be retrieved if needed
		$test_results_to_users = F_getBoolean($m['test_results_to_users']);
		$test_report_to_users = F_getBoolean($m['test_report_to_users']);
		$test_random_questions_select = F_getBoolean($m['test_random_questions_select']);
		$test_random_questions_order = F_getBoolean($m['test_random_questions_order']);
		$test_random_answers_select = F_getBoolean($m['test_random_answers_select']);
		$test_random_answers_order= F_getBoolean($m['test_random_answers_order']);
		$test_comment_enabled = F_getBoolean($m['test_comment_enabled']);
		$test_menu_enabled = F_getBoolean($m['test_menu_enabled']);
		$test_noanswer_enabled = F_getBoolean($m['test_noanswer_enabled']);
		$test_mcma_radio = F_getBoolean($m['test_mcma_radio']);
		*/
	}
} else {
	F_display_db_error();
}

if (($_REQUEST['mode'] == 3) AND ($user_id > 0)) { // detailed report for single user
	$sql = 'SELECT testuser_id, testuser_test_id, testuser_user_id, testuser_creation_time, user_lastname, user_firstname, user_name, SUM(testlog_score) AS test_score, MAX(testlog_change_time) AS test_end_time
		FROM '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_USERS.'
		WHERE testlog_testuser_id=testuser_id
			AND testuser_user_id=user_id
			AND testuser_test_id='.$test_id.'
			AND testuser_user_id='.$user_id.'
			AND testuser_status>0
		GROUP BY testuser_id, testuser_test_id, testuser_user_id, testuser_creation_time, user_lastname, user_firstname, user_name '.$sql_limit.'';
} else { // report for multiple users
	$sql = 'SELECT testuser_id, testuser_test_id, testuser_user_id, testuser_creation_time, user_lastname, user_firstname, user_name, SUM(testlog_score) AS test_score, MAX(testlog_change_time) AS test_end_time
		FROM '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_USERS.'
		WHERE testlog_testuser_id=testuser_id
			AND testuser_user_id=user_id
			AND testuser_test_id='.$test_id.'
			AND testuser_status>0';
	if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
		$sql .= ' AND (user_level<'.$_SESSION['session_user_level'].' OR user_id='.$_SESSION['session_user_id'].')';
	}
	if ($group_id > 0) {
		$sql .= ' AND testuser_user_id IN (SELECT usrgrp_user_id FROM '.K_TABLE_USERGROUP.' WHERE usrgrp_group_id='.$group_id.')';
	}
	$sql .= ' GROUP BY testuser_id, testuser_test_id, testuser_user_id, testuser_creation_time, user_lastname, user_firstname, user_name
		ORDER BY testuser_test_id, user_lastname, user_firstname
		'.$sql_limit.'';
}

if($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		$testuser_id = $m['testuser_id'];
		$test_id = $m['testuser_test_id'];
		$user_id = $m['testuser_user_id'];
		$user_lastname = $m['user_lastname'];
		$user_firstname = $m['user_firstname'];
		$user_name = $m['user_name'];
		$test_score = $m['test_score'];
		$testuser_comment = '';
		$sqluc = 'SELECT testuser_comment FROM '.K_TABLE_TEST_USER.' WHERE testuser_id='.$testuser_id.'';
		if($ruc = F_db_query($sqluc, $db)) {
			if($muc = F_db_fetch_array($ruc)) {
				$testuser_comment = F_decode_tcecode($muc['testuser_comment']);
			}
		} else {
			F_display_db_error();
		}
		if ($_REQUEST['mode'] > 2) {
			$test_start_time = $m['testuser_creation_time'];
			$test_end_time = $m['test_end_time'];
			// Set backlink QR-Code
			$pdf->setTCExamBackLink(K_PATH_URL.'admin/code/tce_show_result_user.php?testuser_id='.$testuser_id.'&test_id='.$test_id.'&user_id='.$user_id);
		}
		// ------------------------------------------------------------
		// --- start page data ---

		$pdf->AddPage();

		// set barcode
		$pdf->setBarcode($test_id.':'.$user_id.':'.$test_start_time);

		$pdf->SetFillColor(204, 204, 204);
		$pdf->SetLineWidth(0.1);
		$pdf->SetDrawColor(0, 0, 0);

		// print document name (title)
		$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA * K_TITLE_MAGNIFICATION);
		$pdf->Cell(0, $main_cell_height * K_TITLE_MAGNIFICATION, $doc_title, 1, 1, 'C', 1);

		$pdf->Ln(5);

		// display user info
		if ($_REQUEST['mode'] >= 3) {

			// add a bookmark
			$pdf->Bookmark($user_lastname.' '.$user_firstname.' ('.$user_name.'), '.$test_score.' '.F_formatPdfPercentage($test_score / $test_max_score), 0, 0);

			// calculate some sizes
			$user_elements = 4;
			$user_data_cell_width = round($page_width / $user_elements, 2);

			// print table headings
			$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);

			$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_lastname'], 1, 0, 'C', 1);
			$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_firstname'], 1, 0, 'C', 1);
			$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_user'], 1, 0, 'C', 1);
			$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_score'], 1, 1, 'C', 1);

			$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

			// minimum required score to pass the exam
			$passmsg = '';
			if ($test_score_threshold > 0) {
				if ($test_score >= $test_score_threshold) {
					$passmsg = ' - '.$l['w_passed'];
				} else {
					$passmsg = ' - '.$l['w_not_passed'];
				}
			}

			$pdf->Cell($user_data_cell_width, $data_cell_height, $user_lastname, 1, 0, 'C', 0);
			$pdf->Cell($user_data_cell_width, $data_cell_height, $user_firstname, 1, 0, 'C', 0);
			$pdf->Cell($user_data_cell_width, $data_cell_height, $user_name, 1, 0, 'C', 0);
			$pdf->Cell($user_data_cell_width, $data_cell_height, $test_score.' '.F_formatPdfPercentage($test_score / $test_max_score).''.$passmsg, 1, 1, 'C', 0);

			$pdf->Ln(5);
		}

		// --- display test info ---

		$info_cell_width = round($page_width / 4, 2);

		$boxStartY = $pdf->GetY(); // store current Y position

		// test name
		$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA * HEAD_MAGNIFICATION);
		$pdf->Cell($page_width, $data_cell_height * HEAD_MAGNIFICATION, $l['w_test'].': '.$test_name, 1, 1, '', 1);

		$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

		$infoStartY = $pdf->GetY() + 2; // store current Y position
		$pdf->SetY($infoStartY);

		$column_names_width = round($info_cell_width * 1.2, 2);

		// test start time
		$pdf->Cell($column_names_width, $data_cell_height, $l['w_time_begin'].': ', 0, 0, $dirlabel, 0);
		$pdf->Cell($info_cell_width, $data_cell_height, $test_start_time, 0, 1, $dirvalue, 0);

		// test end time
		$pdf->Cell($column_names_width, $data_cell_height, $l['w_time_end'].': ', 0, 0, $dirlabel, 0);
		$pdf->Cell($info_cell_width, $data_cell_height, $test_end_time, 0, 1, $dirvalue, 0);

		if ($_REQUEST['mode'] > 2) {
			if (!isset($test_end_time) OR ($test_end_time <= 0)) {
				$time_diff = $test_duration_time * 60;
			} else {
				$time_diff = strtotime($test_end_time) - strtotime($test_start_time); //sec
			}
			$time_diff = gmdate('H:i:s', $time_diff);
			// elapsed time (time difference)
			$pdf->Cell($column_names_width, $data_cell_height, $l['w_time'].': ', 0, 0, $dirlabel, 0);
			$pdf->Cell($info_cell_width, $data_cell_height, $time_diff, 0, 1, $dirvalue, 0);
		}
		// test duration
		$pdf->Cell($column_names_width, $data_cell_height, $l['w_test_time'].' ['.$l['w_minutes'].']: ', 0, 0, $dirlabel, 0);
		$pdf->Cell($info_cell_width, $data_cell_height, $test_duration_time, 0, 1, $dirvalue, 0);

		// authorized IPs
		//$pdf->Cell($column_names_width, $data_cell_height, $l['w_ip_range'].': ', 0, 0, $dirlabel, 0);
		//$pdf->Cell($info_cell_width, $data_cell_height, $test_ip_range, 0, 1, $dirvalue, 0);

		// score for right answer
		$pdf->Cell($column_names_width, $data_cell_height, $l['w_score_right'].': ', 0, 0, $dirlabel, 0);
		$pdf->Cell($info_cell_width, $data_cell_height, $test_score_right, 0, 1, $dirvalue, 0);

		// score for wrong answer
		$pdf->Cell($column_names_width, $data_cell_height, $l['w_score_wrong'].': ', 0, 0, $dirlabel, 0);
		$pdf->Cell($info_cell_width, $data_cell_height, $test_score_wrong, 0, 1, $dirvalue, 0);

		// score for missing answer
		$pdf->Cell($column_names_width, $data_cell_height, $l['w_score_unanswered'].': ', 0, 0, $dirlabel, 0);
		$pdf->Cell($info_cell_width, $data_cell_height, $test_score_unanswered, 0, 1, $dirvalue, 0);

		// max score
		$pdf->Cell($column_names_width, $data_cell_height, $l['w_max_score'].': ', 0, 0, $dirlabel, 0);
		$pdf->Cell($info_cell_width, $data_cell_height, $test_max_score, 0, 1, $dirvalue, 0);

		if ($test_score_threshold > 0) {
			$pdf->Cell($column_names_width, $data_cell_height, $l['w_test_score_threshold'].': ', 0, 0, $dirlabel, 0);
			$pdf->Cell($info_cell_width, $data_cell_height, $test_score_threshold, 0, 1, $dirvalue, 0);
		}

		if ($_REQUEST['mode'] > 2) {
			$usrtestdata = F_getUserTestStat($test_id, $user_id);
			// right answers
			$pdf->Cell($column_names_width, $data_cell_height, $l['w_answers_right'].': ', 0, 0, $dirlabel, 0);
			$pdf->Cell($info_cell_width, $data_cell_height, $usrtestdata['right'].' '.F_formatPdfPercentage($usrtestdata['right'] / $usrtestdata['all']), 0, 1, $dirvalue, 0);
		}
		/*
		// Additional test information that could be printed if needed
		$test_results_to_users
		$test_report_to_users
		$test_random_questions_select
		$test_random_questions_order
		$test_random_answers_select
		$test_random_answers_order
		$test_comment_enabled
		$test_menu_enabled
		$test_noanswer_enabled
		$test_mcma_radio
		*/

		$boxEndY = $pdf->GetY();

		$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

		// test description
		$pdf->writeHTMLCell(0, ($boxEndY - $infoStartY + 4), (PDF_MARGIN_LEFT + ($info_cell_width * 2)), $infoStartY - 2, $test_description, 1, 0);

		$boxEndY = max($boxEndY, $pdf->GetY());

		// print box around test info
		$pdf->SetY($boxStartY);
		$pdf->Cell($page_width, ($boxEndY - $boxStartY + 2), '', 1, 1, 'C', 0);

		// --- end test info ---

		// print user's comments
		if (!empty($testuser_comment)) {
			$pdf->Cell($page_width, $data_cell_height, '', 0, 1, '', 0);
			$pdf->writeHTMLCell($page_width, $data_cell_height, '', '', $testuser_comment, 1, 1);
		}

		$pdf->Ln(5);

		// display different things by case
		switch ($_REQUEST['mode']) {
			case 1: {
				// all users results

				// print table headings
				$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
				$pdf->Cell($data_cell_width_third, $data_cell_height, '#', 1, 0, 'C', 1);
				$pdf->Cell(3 * $data_cell_width_third, $data_cell_height, $l['w_score'], 1, 0, 'C', 1);
				$pdf->Cell((2 * $data_cell_width) - (0.5 * $data_cell_width_third), $data_cell_height, $l['w_lastname'], 1, 0, 'C', 1);
				$pdf->Cell((2 * $data_cell_width) - (0.5 * $data_cell_width_third), $data_cell_height, $l['w_firstname'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width, $data_cell_height, $l['w_user'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $l['w_answers_right_th'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $l['w_answers_wrong_th'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $l['w_questions_unanswered_th'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $l['w_questions_undisplayed_th'], 1, 1, 'C', 1);
				// print table rows

				$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

				$statsdata = array();
				$statsdata['score'] = array();
				$statsdata['right'] = array();
				$statsdata['wrong'] = array();
				$statsdata['unanswered'] = array();
				$statsdata['undisplayed'] = array();
				$statsdata['unrated'] = array();

				$sqlr = 'SELECT testuser_id, user_id, user_lastname, user_firstname, user_name, SUM(testlog_score) AS total_score, MAX(testlog_change_time) AS test_end_time
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
					ORDER BY '.$full_order_field.'';
				if($rr = F_db_query($sqlr, $db)) {
					$itemcount = 0;
					$passed = 0;
					$pdf->SetFillColor(128, 255, 128);
					while($mr = F_db_fetch_array($rr)) {
						$itemcount++;
						$usrtestdata = F_getUserTestStat($test_id, $mr['user_id']);
						$pdf->SetFont($numberfont, '', 6);
						$pfill = 0;
						if ($usrtestdata['score_threshold'] > 0) {
							if ($usrtestdata['score'] >= $usrtestdata['score_threshold']) {
								$pfill = 1;
								$passed++;
							} else {
								$pfill = 0;
							}
						}
						$pdf->Cell($data_cell_width_third, $data_cell_height, $itemcount, 1, 0, 'R', $pfill);
						$pdf->Cell(3 * $data_cell_width_third, $data_cell_height, sprintf('%.3f', round($mr['total_score'], 3)).' '.F_formatPdfPercentage($usrtestdata['score'] / $usrtestdata['max_score']), 1, 0, 'R', 0);
						$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
						$pdf->Cell((2 * $data_cell_width) - (0.5 * $data_cell_width_third), $data_cell_height, $mr['user_lastname'], 1, 0, '', 0);
						$pdf->Cell((2 * $data_cell_width) - (0.5 * $data_cell_width_third), $data_cell_height, $mr['user_firstname'], 1, 0, '', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, $mr['user_name'], 1, 0, '', 0);
						$pdf->SetFont($numberfont, '', 6);
						$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $usrtestdata['right'].' '.F_formatPdfPercentage($usrtestdata['right'] / $usrtestdata['all']), 1, 0, 'R', 0);
						$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $usrtestdata['wrong'].' '.F_formatPdfPercentage($usrtestdata['wrong'] / $usrtestdata['all']), 1, 0, 'R', 0);
						$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $usrtestdata['unanswered'].' '.F_formatPdfPercentage($usrtestdata['unanswered'] / $usrtestdata['all']), 1, 0, 'R', 0);
						$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $usrtestdata['undisplayed'].' '.F_formatPdfPercentage($usrtestdata['undisplayed'] / $usrtestdata['all']), 1, 1, 'R', 0);
						// collects data for descriptive statistics
						$statsdata['score'][] = $mr['total_score'];
						$statsdata['right'][] = $usrtestdata['right'];
						$statsdata['wrong'][] = $usrtestdata['wrong'];
						$statsdata['unanswered'][] = $usrtestdata['unanswered'];
						$statsdata['undisplayed'][] = $usrtestdata['undisplayed'];
						$statsdata['unrated'][] = $usrtestdata['unrated'];
					}
					$pdf->SetFillColor(204, 204, 204);
				} else {
					F_display_db_error();
				}
				// calculate statistics
				$stats = F_getArrayStatistics($statsdata);
				$excludestat = array('sum', 'variance');
				$calcpercent = array('mean', 'median', 'mode', 'minimum', 'maximum', 'range', 'standard_deviation');
				$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
				$pdf->Ln();
				$pdf->Cell($page_width, $data_cell_height, $l['w_statistics'], 1, 1, 'C', 1);

				if (($usrtestdata['score_threshold'] > 0) AND ($stats['number']['score'] > 0)) {
					$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
					$pdf->Cell(2 * $data_cell_width, $data_cell_height, $l['w_passed'], 1, 0, $dirlabel, 0);
					$pdf->SetFont($numberfont, '', 6);
					$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, ''.$passed.' '.F_formatPdfPercentage($passed / $stats['number']['score']).'', 1, 0, 'R', 0);
					$pdf->Cell($data_cell_width * 28 / 5, $data_cell_height, '', 1, 1, '', 0);
					$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
				}
				// columns headers
				$pdf->Cell(2 * $data_cell_width, $data_cell_height, '', 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $l['w_score'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $l['w_answers_right_th'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $l['w_answers_wrong_th'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $l['w_questions_unanswered_th'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $l['w_questions_undisplayed_th'], 1, 1, 'C', 1);

				$pdf->SetFont($numberfont, '', 6);
				foreach ($stats as $row => $columns) {
					if (!in_array($row, $excludestat)) {
						$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
						$pdf->Cell(2 * $data_cell_width, $data_cell_height, $l['w_'.$row], 1, 0, $dirlabel, 0);
						$pdf->SetFont($numberfont, '', 6);
						$cstr = ''.round($columns['score'], 3).'';
						if (in_array($row, $calcpercent)) {
							$cstr .= ' '.F_formatPdfPercentage($columns['score'] / $usrtestdata['max_score']);
						}
						$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $cstr, 1, 0, 'R', 0);
						$cstr = ''.round($columns['right'], 3).'';
						if (in_array($row, $calcpercent)) {
							$cstr .= ' '.F_formatPdfPercentage($columns['right'] / $usrtestdata['all']);
						}
						$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $cstr, 1, 0, 'R', 0);
						$cstr = ''.round($columns['wrong'], 3).'';
						if (in_array($row, $calcpercent)) {
							$cstr .= ' '.F_formatPdfPercentage($columns['wrong'] / $usrtestdata['all']);
						}
						$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $cstr, 1, 0, 'R', 0);
						$cstr = ''.round($columns['unanswered'], 3).'';
						if (in_array($row, $calcpercent)) {
							$cstr .= ' '.F_formatPdfPercentage($columns['unanswered'] / $usrtestdata['all']);
						}
						$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $cstr, 1, 0, 'R', 0);
						$cstr = ''.round($columns['undisplayed'], 3).'';
						if (in_array($row, $calcpercent)) {
							$cstr .= ' '.F_formatPdfPercentage($columns['undisplayed'] / $usrtestdata['all']);
						}
						$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $cstr, 1, 1, 'R', 0);
					}
				}
				break;
			}
			case 2: {
				// questions stats

				// get test data
				$testdata = F_getTestData($test_id);

				// get total number of questions for the selected test
				$num_questions = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.'');

				// print table headings

				$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);

				$pdf->Cell(2 * $data_cell_width_third, $data_cell_height, '#', 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width, $data_cell_height, $l['w_recurrence'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width, $data_cell_height, $l['w_score'], 1, 0, 'C', 1);
				$pdf->Cell(4 * $data_cell_width_third, $data_cell_height, $l['w_answer_time'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width, $data_cell_height, $l['w_answers_right_th'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width, $data_cell_height, $l['w_answers_wrong_th'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width, $data_cell_height, $l['w_questions_unanswered_th'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width, $data_cell_height, $l['w_questions_undisplayed_th'], 1, 0, 'C', 1);
				$pdf->Cell($data_cell_width, $data_cell_height, $l['w_questions_unrated_th'], 1, 1, 'C', 1);
				$pdf->Ln(2);

				// print table rows

				$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

				// output questions stats
				$sqlr = 'SELECT
						question_id,
						COUNT(question_id) AS recurrence,
						AVG(testlog_score) AS average_score,
						AVG(testlog_change_time - testlog_display_time) AS average_time,
						min(question_difficulty) AS question_difficulty
					FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_QUESTIONS.'
					WHERE testlog_testuser_id=testuser_id
						AND testlog_question_id=question_id
						AND testuser_test_id='.$test_id.'
					GROUP BY question_id
					ORDER BY '.$full_order_field.'';
				if($rr = F_db_query($sqlr, $db)) {
					$itemcount = 1;
					while($mr = F_db_fetch_array($rr)) {
						// get the question max score
						$question_max_score = $testdata['test_score_right'] * $mr['question_difficulty'];

						$qsttestdata = F_getQuestionTestStat($test_id, $mr['question_id']);

						$pdf->SetFont($numberfont, 'B', 6);
						$pdf->Cell(2 * $data_cell_width_third, $data_cell_height, $itemcount, 1, 0, 'R', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, $mr['recurrence'].' '.F_formatPdfPercentage($mr['recurrence'] / $num_questions), 1, 0, 'R', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, number_format($mr['average_score'], 3, '.', '').' '.F_formatPdfPercentage($mr['average_score'] / $question_max_score), 1, 0, 'R', 0);
						if (stripos($mr['average_time'], ':') !== FALSE) {
							// PostgreSQL returns formatted time, while MySQL returns the number of seconds
							$mr['average_time'] = strtotime($mr['average_time']);
						}
						$pdf->Cell(4 * $data_cell_width_third, $data_cell_height, date('i:s', $mr['average_time']), 1, 0, 'R', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, $qsttestdata['right'].' '.F_formatPdfPercentage($qsttestdata['right'] / $qsttestdata['num']), 1, 0, 'R', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, $qsttestdata['wrong'].' '.F_formatPdfPercentage($qsttestdata['wrong'] / $qsttestdata['num']), 1, 0, 'R', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, $qsttestdata['unanswered'].' '.F_formatPdfPercentage($qsttestdata['unanswered'] / $qsttestdata['num']), 1, 0, 'R', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, $qsttestdata['undisplayed'].' '.F_formatPdfPercentage($qsttestdata['undisplayed'] / $qsttestdata['num']), 1, 0, 'R', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, $qsttestdata['unrated'].' '.F_formatPdfPercentage($qsttestdata['unrated'] / $qsttestdata['num']), 1, 1, 'R', 0);

						$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
						$question_description = '';
						$sqlq = 'SELECT question_description FROM '.K_TABLE_QUESTIONS.' WHERE question_id='.$mr['question_id'].'';
						if($rq = F_db_query($sqlq, $db)) {
							if($mq = F_db_fetch_array($rq)) {
								$question_description = $mq['question_description'];
							}
						} else {
							F_display_db_error();
						}
						$pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + (2 * $data_cell_width_third)), $pdf->GetY(), F_decode_tcecode($question_description), 1, 1);

						//$pdf->Ln(2);

						$itemcount++;

						// answers statistics

						$sqla = 'SELECT *
							FROM '.K_TABLE_ANSWERS.'
							WHERE answer_question_id='.$mr['question_id'].'
							ORDER BY answer_id';
						if($ra = F_db_query($sqla, $db)) {
							$answcount = 1;
							while($ma = F_db_fetch_array($ra)) {

								$num_all_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].'');

								$num_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].'');

								$right_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND ((answer_isright=\'0\' AND logansw_selected=0) OR (answer_isright=\'1\' AND logansw_selected=1) OR (answer_position IS NOT NULL AND logansw_position IS NOT NULL AND answer_position=logansw_position))');

								$wrong_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND ((answer_isright=\'0\' AND logansw_selected=1) OR (answer_isright=\'1\' AND logansw_selected=0) OR (answer_position IS NOT NULL AND answer_position!=logansw_position))');

								$unanswered = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND logansw_selected=-1');

								$pdf->SetFont($numberfont, '', 6);

								$pdf->Cell(2 * $data_cell_width_third, $data_cell_height, '', '', 0, 'R', 0);
								$pdf->Cell($data_cell_width_third, $data_cell_height, $answcount, 1, 0, 'R', 0);

								$perc = 0;
								if ($num_all_answers > 0 ) {
									$perc = ($num_answers / $num_all_answers);
								}
								$pdf->Cell($data_cell_width - $data_cell_width_third, $data_cell_height, $num_answers.' '.F_formatPdfPercentage($perc), 1, 0, 'R', 0);
								$pdf->Cell(2 * $data_cell_width + $data_cell_width_third, $data_cell_height, '', 1, 0, 'C', 0);

								$perc = 0;
								if ($num_answers > 0 ) {
									$perc = ($right_answers / $num_answers);
								}
								$pdf->Cell($data_cell_width, $data_cell_height, $right_answers.' '.F_formatPdfPercentage($perc), 1, 0, 'R', 0);

								$perc = 0;
								if ($num_answers > 0 ) {
									$perc = ($wrong_answers / $num_answers);
								}
								$pdf->Cell($data_cell_width, $data_cell_height, $wrong_answers.' '.F_formatPdfPercentage($perc), 1, 0, 'R', 0);

								$perc = 0;
								if ($num_answers > 0 ) {
									$perc = ($unanswered / $num_answers);
								}
								$pdf->Cell($data_cell_width, $data_cell_height, $unanswered.' '.F_formatPdfPercentage($perc), 1, 0, 'R', 0);

								$pdf->Cell(2 * $data_cell_width, $data_cell_height, '', 1, 1, 'C', 0);

								$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
								$pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + (3 * $data_cell_width_third)), $pdf->GetY(), F_decode_tcecode($ma['answer_description']), 1, 1);

								$answcount++;
							}
						} else {
							F_display_db_error();
						}
						$pdf->Ln(2);
					}
				} else {
					F_display_db_error();
				}
				break;
			}
			case 3: // detailed report for single user
			case 4: // detailed report for all users
			case 5: { // detailed report for all users with only open questions
				$topicresults = array(); // per-topic results
				// get test basic score
				$test_basic_score = 1;
				$sqlbs = 'SELECT test_score_right	FROM '.K_TABLE_TESTS.' WHERE test_id='.$test_id.'';
				if($rbs = F_db_query($sqlbs, $db)) {
					if($mbs = F_db_fetch_array($rbs)) {
						$test_basic_score = $mbs['test_score_right'];
					}
				} else {
					F_display_db_error();
				}

				$sqlq = 'SELECT *
					FROM '.K_TABLE_QUESTIONS.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_SUBJECTS.', '.K_TABLE_MODULES.'
					WHERE question_id=testlog_question_id
						AND testlog_testuser_id='.$testuser_id.'
						AND question_subject_id=subject_id
						AND subject_module_id=module_id';
				if ($_REQUEST['mode'] == 5) {
					// display only TEXT questions
					$sqlq .= ' AND question_type=3';
				}
				$sqlq .= ' ORDER BY testlog_id';
				if($rq = F_db_query($sqlq, $db)) {

					$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);

					$pdf->Cell($data_cell_width_third, $data_cell_height, '#', 1, 0, 'C', 1);
					$pdf->Cell($data_cell_width, $data_cell_height, $l['w_score'], 1, 0, 'C', 1);
					$pdf->Cell($data_cell_width, $data_cell_height, $l['w_ip'], 1, 0, 'C', 1);
					$pdf->Cell($data_cell_width + $data_cell_width_third, $data_cell_height, $l['w_start'].' ['.$l['w_time_hhmmss'].']', 1, 0, 'C', 1);
					$pdf->Cell($data_cell_width + $data_cell_width_third, $data_cell_height, $l['w_end'].' ['.$l['w_time_hhmmss'].']', 1, 0, 'C', 1);
					$pdf->Cell($data_cell_width, $data_cell_height, $l['w_time'].' ['.$l['w_time_mmss'].']', 1, 0, 'C', 1);
					$pdf->Cell($data_cell_width, $data_cell_height, $l['w_reaction'].' [sec]', 1, 1, 'C', 1);
					$pdf->Ln($data_cell_height);

					// print table rows

					$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
					$itemcount = 1;

					while($mq = F_db_fetch_array($rq)) {

						// create per-topic results array
						if (!array_key_exists($mq['module_id'], $topicresults)) {
							$topicresults[$mq['module_id']] = array();
							$topicresults[$mq['module_id']]['name'] = $mq['module_name'];
							$topicresults[$mq['module_id']]['num'] = 0;
							$topicresults[$mq['module_id']]['right'] = 0;
							$topicresults[$mq['module_id']]['wrong'] = 0;
							$topicresults[$mq['module_id']]['unanswered'] = 0;
							$topicresults[$mq['module_id']]['undisplayed'] = 0;
							$topicresults[$mq['module_id']]['unrated'] = 0;
							$topicresults[$mq['module_id']]['score'] = 0;
							$topicresults[$mq['module_id']]['maxscore'] = 0;
							$topicresults[$mq['module_id']]['subjects'] = array();
						}
						if (!array_key_exists($mq['subject_id'], $topicresults[$mq['module_id']]['subjects'])) {
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']] = array();
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['name'] = $mq['subject_name'];
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['num'] = 0;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['right'] = 0;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['wrong'] = 0;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['unanswered'] = 0;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['undisplayed'] = 0;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['unrated'] = 0;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['score'] = 0;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['maxscore'] = 0;
						}
						$question_max_score = ($mq['question_difficulty'] * $test_basic_score);
						// total number of questions
						$topicresults[$mq['module_id']]['num'] += 1;
						$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['num'] += 1;
						// number of right answers
						if ($mq['testlog_score'] > ($question_max_score / 2)) {
							$topicresults[$mq['module_id']]['right'] += 1;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['right'] += 1;
						} else {
							// number of wrong answers
							$topicresults[$mq['module_id']]['wrong'] += 1;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['wrong'] += 1;
						}
						// total number of unanswered questions
						if (strlen($mq['testlog_change_time']) <= 0) {
							$topicresults[$mq['module_id']]['unanswered'] += 1;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['unanswered'] += 1;
						}
						// total number of undisplayed questions
						if (strlen($mq['testlog_display_time']) <= 0) {
							$topicresults[$mq['module_id']]['undisplayed'] += 1;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['undisplayed'] += 1;
						}
						// number of free-text unrated questions
						if (strlen($mq['testlog_score']) <= 0) {
							$topicresults[$mq['module_id']]['unrated'] += 1;
							$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['unrated'] += 1;
						}
						// score
						$topicresults[$mq['module_id']]['score'] += $mq['testlog_score'];
						$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['score'] += $mq['testlog_score'];
						// max score
						$topicresults[$mq['module_id']]['maxscore'] += $question_max_score;
						$topicresults[$mq['module_id']]['subjects'][$mq['subject_id']]['maxscore'] += $question_max_score;

						$pdf->Cell($data_cell_width_third, $data_cell_height, $itemcount.' '.$qtype[($mq['question_type']-1)], 1, 0, 'R', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, $mq['testlog_score'], 1, 0, 'C', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, getIpAsString($mq['testlog_user_ip']), 1, 0, 'C', 0);
						if (isset($mq['testlog_display_time']) AND (strlen($mq['testlog_display_time']) > 0)) {
							$display_time =  substr($mq['testlog_display_time'], 11, 8);
						} else {
							$display_time =  '--:--:--';
						}
						if (isset($mq['testlog_change_time']) AND (strlen($mq['testlog_change_time']) > 0)) {
							$change_time = substr($mq['testlog_change_time'], 11, 8);
						} else {
							$change_time = '--:--:--';
						}
						if (isset($mq['testlog_display_time']) AND isset($mq['testlog_change_time'])) {
							$diff_time = date('i:s', (strtotime($mq['testlog_change_time']) - strtotime($mq['testlog_display_time'])));
						} else {
							$diff_time = '--:--';
						}
						if (isset($mq['testlog_reaction_time']) AND (strlen($mq['testlog_reaction_time']) > 0)) {
							$reaction_time =  ($mq['testlog_reaction_time'] / 1000);
						} else {
							$reaction_time =  '';
						}
						$pdf->Cell($data_cell_width + $data_cell_width_third, $data_cell_height, $display_time, 1, 0, 'C', 0);
						$pdf->Cell($data_cell_width + $data_cell_width_third, $data_cell_height, $change_time, 1, 0, 'C', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, $diff_time, 1, 0, 'C', 0);
						$pdf->Cell($data_cell_width, $data_cell_height, $reaction_time, 1, 1, 'C', 0);

						$pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + $data_cell_width_third), $pdf->GetY(), F_decode_tcecode($mq['question_description']), 1, 1);
						if (K_ENABLE_QUESTION_EXPLANATION AND !empty($mq['question_explanation'])) {
							$pdf->Cell($data_cell_width_third, $data_cell_height, '', 0, 0, 'C', 0);
							$pdf->SetFont('', 'BIU');
							$pdf->Cell(0, $data_cell_height, $l['w_explanation'], 'LTR', 1, '', 0, '', 0);
							$pdf->SetFont('', '');
							$pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + $data_cell_width_third), $pdf->GetY(), F_decode_tcecode($mq['question_explanation']), 'LRB', 1, '', '');
						}

						if ($mq['question_type'] == 3) {
							// free-text question - print user text answer
							$pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + (2 * $data_cell_width_third)), $pdf->GetY(), F_decode_tcecode($mq['testlog_answer_text']), 1, 1);
						} else {
							// display each answer option
							$sqla = 'SELECT *
								FROM '.K_TABLE_LOG_ANSWER.', '.K_TABLE_ANSWERS.'
								WHERE logansw_answer_id=answer_id
									AND logansw_testlog_id=\''.$mq['testlog_id'].'\'
								ORDER BY logansw_order';
							if($ra = F_db_query($sqla, $db)) {
								$idx = 0; // count items
								while($ma = F_db_fetch_array($ra)) {
									$posfill = 0;
									$idx++;
									$pdf->Cell($data_cell_width_third, $data_cell_height, '', 0, 0, 'C', 0);
									if ($mq['question_type'] == 4) {
										if ($ma['logansw_position'] > 0) {
											if ($ma['logansw_position'] == $ma['answer_position']) {
												$posfill = 1;
												$pdf->Cell($data_cell_width_third, $data_cell_height, $ma['logansw_position'], 1, 0, 'C', 1);
											} else {
												$pdf->Cell($data_cell_width_third, $data_cell_height, $ma['logansw_position'], 1, 0, 'C', 0);
											}
										} else {
											$pdf->Cell($data_cell_width_third, $data_cell_height, ' ', 1, 0, 'C', 0);
										}
									} elseif ($ma['logansw_selected'] > 0) {
										// selected
										if (F_getBoolean($ma['answer_isright'])) {
											$pdf->Cell($data_cell_width_third, $data_cell_height, '+', 1, 0, 'C', 1);
										} else {
											$pdf->Cell($data_cell_width_third, $data_cell_height, '-', 1, 0, 'C', 1);
										}
									} elseif ($mq['question_type'] == 1) {
										// MCSA
										$pdf->Cell($data_cell_width_third, $data_cell_height, ' ', 1, 0, 'C', 0);
									} else {
										if ($ma['logansw_selected'] == 0) {
											// unselected
											if (F_getBoolean($ma['answer_isright'])) {
												$pdf->Cell($data_cell_width_third, $data_cell_height, '-', 1, 0, 'C', 0);
											} else {
												$pdf->Cell($data_cell_width_third, $data_cell_height, '+', 1, 0, 'C', 0);
											}
										} else {
											// no answer
											$pdf->Cell($data_cell_width_third, $data_cell_height, ' ', 1, 0, 'C', 0);
										}
									}
									if ($mq['question_type'] == 4) {
											$pdf->Cell($data_cell_width_third, $data_cell_height, $ma['answer_position'], 1, 0, 'C', $posfill);
									} elseif (F_getBoolean($ma['answer_isright'])) {
										$pdf->Cell($data_cell_width_third, $data_cell_height, $idx, 1, 0, 'C', 1);
									} else {
										$pdf->Cell($data_cell_width_third, $data_cell_height, $idx, 1, 0, 'C', 0);
									}
									$pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + $data_cell_width), $pdf->GetY(), F_decode_tcecode($ma['answer_description']), 'LRTB', 1);
									if (K_ENABLE_ANSWER_EXPLANATION AND !empty($ma['answer_explanation'])) {
										$pdf->Cell((3 * $data_cell_width_third), $data_cell_height, '', 0, 0, 'C', 0);
										$pdf->SetFont('', 'BIU');
										$pdf->Cell(0, $data_cell_height, $l['w_explanation'], 'LTR', 1, '', 0, '', 0);
										$pdf->SetFont('', '');
										$pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + (3 * $data_cell_width_third)), $pdf->GetY(), F_decode_tcecode($ma['answer_explanation']), 'LRB', 1, '', '');
									}
								}
							} else {
								F_display_db_error();
							}
						} // end multiple answers
						if (strlen($mq['testlog_comment']) > 0) {
							// teacher / supervisor comment
							$pdf->SetTextColor(255, 0, 0);
							$pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + (2 * $data_cell_width_third)), $pdf->GetY(), F_decode_tcecode($mq['testlog_comment']), 'LRTB', 1);
							$pdf->SetTextColor(0, 0, 0);
						}
						$pdf->Ln($data_cell_height);
						$itemcount++;
					} // end of while (for each question)
				} else {
					F_display_db_error();
				}

				// start transaction
				$pdf->startTransaction();
				$block_page = $pdf->getPage();
				$print_block = 2; // 2 tries max
				while ($print_block > 0) {

					// print per-topic results
					$pdf->Ln($data_cell_height);
					$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);

					$pdf->Cell(0, $data_cell_height, $l['w_subjects'], 0, 1, 'C', 1);
					$pdf->Ln($data_cell_height);

					$pdf->Cell($data_cell_width, $data_cell_height, $l['w_score'], 1, 0, 'C', 1);
					$pdf->Cell($data_cell_width, $data_cell_height, $l['w_answers_right'], 1, 0, 'C', 1);
					$pdf->Cell($data_cell_width * 5, $data_cell_height, $l['w_module'], 1, 1, $dirvalue, 1);
					$pdf->Ln(0.5);
					$pdf->Cell($data_cell_width, $data_cell_height, '', 0, 0, 'C', 0);
					$pdf->Cell($data_cell_width, $data_cell_height, $l['w_score'], 1, 0, 'C', 1);
					$pdf->Cell($data_cell_width, $data_cell_height, $l['w_answers_right'], 1, 0, 'C', 1);
					$pdf->Cell($data_cell_width * 4, $data_cell_height, $l['w_subject'], 1, 1, $dirvalue, 1);
					$pdf->Ln($data_cell_height);

					foreach ($topicresults as $res_module) {
						$pdf->SetFont($numberfont, 'B', 6);
						$score_percent = ($res_module['score'] / $res_module['maxscore']);
						$str = $res_module['score'].' / '.$res_module['maxscore'].' '.F_formatPdfPercentage($score_percent);
						$pdf->Cell($data_cell_width, $data_cell_height, $str, 1, 0, 'R', 0);

						$score_percent = ($res_module['right'] / $res_module['num']);
						$str = $res_module['right'].' / '.$res_module['num'].' '.F_formatPdfPercentage($score_percent);
						$pdf->Cell($data_cell_width, $data_cell_height, $str, 1, 0, 'R', 0);

						$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
						$pdf->Cell($data_cell_width * 5, $data_cell_height, $res_module['name'], 1, 1, $dirvalue, 0);
						$pdf->Ln(0.5);
						foreach ($res_module['subjects'] as $res_subject) {
							$pdf->SetFont($numberfont, '', 6);
							$pdf->Cell($data_cell_width, $data_cell_height, '', 0, 0, 'C', 0);

							$score_percent = ($res_subject['score'] / $res_subject['maxscore']);
							$str = $res_subject['score'].' / '.$res_subject['maxscore'].' '.F_formatPdfPercentage($score_percent);
							$pdf->Cell($data_cell_width, $data_cell_height, $str, 1, 0, 'R', 0);

							$score_percent = ($res_subject['right'] / $res_subject['num']);
							$str = $res_subject['right'].' / '.$res_subject['num'].' '.F_formatPdfPercentage($score_percent);
							$pdf->Cell($data_cell_width, $data_cell_height, $str, 1, 0, 'R', 0);

							$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
							$pdf->Cell($data_cell_width * 4, $data_cell_height, $res_subject['name'], 1, 1, $dirvalue, 0);
							$pdf->Ln(0.5);
						}
					}

					// do not split BLOCKS in multiple pages
					if ($pdf->getPage() == $block_page) {
						$print_block = 0;
					} else {
						// rolls back to the last (re)start
						$pdf = $pdf->rollbackTransaction();
						$pdf->AddPage();
						$block_page = $pdf->getPage();
						--$print_block;
					}
				} // end while print_block

				break;
			} // end of case 3, 4 and 5
		}
		// END page data
		// ------------------------------------------------------------
	}
} else {
	F_display_db_error();
}

$pdf->lastpage(true);
$pdf->SetAutoPageBreak(false);
$pdf->SetFont('helvetica', '', 5);
$pdf->SetTextColor(0,127,255);
$msg = "\x50\x6f\x77\x65\x72\x65\x64\x20\x62\x79\x20\x54\x43\x45\x78\x61\x6d\x20\x28\x77\x77\x77\x2e\x74\x63\x65\x78\x61\x6d\x2e\x6f\x72\x67\x29";
$lnk = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x74\x63\x65\x78\x61\x6d\x2e\x6f\x72\x67";
$pdf->SetXY(15, $pdf->getPageHeight(), true);
$pdf->Cell(0, 0, $msg, 0, 0, 'R', 0, $lnk, 0, false, 'B', 'B');

// set PDF file name
$pdf_filename = 'tcexam_results_'.date('YmdHi', strtotime($test_start_time)).'_test_'.$test_id;
switch ($_REQUEST['mode']) {
	case 1: {
		// all users results
		$pdf_filename .= '_allusers_report';
		break;
	}
	case 2: {
		// questions stats
		$pdf_filename .= '_questions_stats';
		break;
	}
	case 3: {// detailed report for specific user
		$pdf_filename .= '_user_'.$user_id.'';
		break;
	}
	case 4: {// detailed report for all users
		$pdf_filename .= '_allusers_details';
		break;
	}
	case 5: { // detailed report for all users with only open questions
		$pdf_filename .= '_allusers_openquestions';
		break;
	}
}
$pdf_filename .= '.pdf';

// Send PDF output
$pdf->Output($pdf_filename, 'D');

//============================================================+
// END OF FILE
//============================================================+
