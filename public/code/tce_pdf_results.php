<?php
//============================================================+
// File name   : tce_pdf_results.php
// Begin       : 2004-06-10
// Last Update : 2009-09-30
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
 * Create PDF document to display users' tests results.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2004-06-11
 * @param int $_REQUEST['testid'] test ID
 */

/**
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('../../shared/config/tce_pdf.php');
require_once('../../shared/code/tcpdf.php');

require_once('../../shared/code/tce_authorization.php');

if (isset($_REQUEST['testid'])) {
	$test_id = intval($_REQUEST['testid']);
} else {
	echo $l['m_authorization_denied'];
	exit;
}
	
$testdata = F_getTestData($test_id);
if (!F_getBoolean($testdata['test_report_to_users'])) {
	echo $l['m_authorization_denied'];
	exit;
}
$user_id = intval($_SESSION['session_user_id']);
$doc_title = unhtmlentities($l['t_result_user']);
$doc_description = F_compact_string(unhtmlentities($l['hp_result_user']));
$page_elements = 7;
$temp_order_field = '';
$qtype = array('S', 'M', 'T', 'O'); // question types

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
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, $isunicode); 

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
$page_width = $pdf->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT;
$data_cell_height = round((K_CELL_HEIGHT_RATIO * PDF_FONT_SIZE_DATA) / $pdf->getScaleFactor(), 2);
$main_cell_height = round((K_CELL_HEIGHT_RATIO * PDF_FONT_SIZE_MAIN) / $pdf->getScaleFactor(), 2);
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
		$test_begin_time = $m['test_begin_time'];
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


$sql = 'SELECT testuser_id, testuser_test_id, testuser_user_id, testuser_creation_time, testuser_comment, user_lastname, user_firstname, user_name, SUM(testlog_score) AS test_score, MAX(testlog_change_time) AS test_end_time
	FROM '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_USERS.' 
	WHERE testlog_testuser_id=testuser_id
		AND testuser_user_id=user_id
		AND testuser_test_id='.$test_id.'
		AND testuser_user_id='.$user_id.'
		AND testuser_status>0
	GROUP BY testuser_id, testuser_test_id, testuser_user_id, testuser_creation_time, testuser_comment, user_lastname, user_firstname, user_name
	LIMIT 1';
if($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		$testuser_id = $m['testuser_id'];
		//$test_id = $m['testuser_test_id'];
		//$user_id = $m['testuser_user_id'];
		$user_lastname = $m['user_lastname'];
		$user_firstname = $m['user_firstname'];
		$user_name = $m['user_name'];
		$test_start_time = $m['testuser_creation_time'];
		$test_score = $m['test_score'];	
		$testuser_comment = F_decode_tcecode($m['testuser_comment']);
		$test_end_time = $m['test_end_time'];
		
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
			
		// add a bookmark
		$pdf->Bookmark($user_lastname.' '.$user_firstname.' ('.$user_name.'), '.$test_score.' ('.round(100 * $test_score / $test_max_score).'%)', 0, 0);
	
		// calculate some sizes
		$user_elements = 4;
		$user_data_cell_width = round($page_width / $user_elements, 2);
		
		// print table headings
		$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
		
		$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_lastname'], 'LTRB', 0, 'C', 1);
		$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_firstname'], 'LTRB', 0, 'C', 1);
		$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_user'], 'LTRB', 0, 'C', 1);
		$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_score'], 'LTRB', 1, 'C', 1);
		
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
		
		$pdf->Cell($user_data_cell_width, $data_cell_height, $user_lastname, 'LTRB', 0, 'C', 0);
		$pdf->Cell($user_data_cell_width, $data_cell_height, $user_firstname, 'LTRB', 0, 'C', 0);
		$pdf->Cell($user_data_cell_width, $data_cell_height, $user_name, 'LTRB', 0, 'C', 0);
		$pdf->Cell($user_data_cell_width, $data_cell_height, $test_score." (".round(100 * $test_score / $test_max_score)."%)".$passmsg."", 'LTRB', 1, 'C', 0);
		
		$pdf->Ln(5);
		
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
		
		if (!isset($test_end_time) OR ($test_end_time <= 0)) {
			$time_diff = $test_duration_time * 60;
		} else {
			$time_diff = strtotime($test_end_time) - strtotime($test_start_time); //sec
		}
		$time_diff = gmdate("H:i:s", $time_diff);
		
		// elapsed time (time difference)
		$pdf->Cell($column_names_width, $data_cell_height, $l['w_time'].': ', 0, 0, $dirlabel, 0);
		$pdf->Cell($info_cell_width, $data_cell_height, $time_diff, 0, 1, $dirvalue, 0);
		
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
		
		$usrtestdata = F_getUserTestStat($test_id, $user_id);
		
		// right answers
		$pdf->Cell($column_names_width, $data_cell_height, $l['w_answers_right'].': ', 0, 0, $dirlabel, 0);
		$pdf->Cell($info_cell_width, $data_cell_height, $usrtestdata['right'].' ('.round(100 * $usrtestdata['right'] / $usrtestdata['all']).'%)', 0, 1, $dirvalue, 0);
		
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
		$pdf->Cell($page_width, ($boxEndY - $boxStartY + 2), '', 'LTRB', 1, 'C', 0);
		
		// --- end test info ---
		
		// print user's comments
		if (!empty($testuser_comment)) {
			$pdf->Cell($page_width, $data_cell_height, '', 0, 1, '', 0);
			$pdf->writeHTMLCell($page_width, $data_cell_height, '', '', $testuser_comment, 1, 1);
		}
		
		$pdf->Ln(5);
		
		// detailed report for single user
		$sqlq = 'SELECT * 
			FROM '.K_TABLE_QUESTIONS.','.K_TABLE_TESTS_LOGS.' 
			WHERE question_id=testlog_question_id 
			AND testlog_testuser_id='.$testuser_id.'
			ORDER BY testlog_id';
		if($rq = F_db_query($sqlq, $db)) {
			
			$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
			
			$pdf->Cell($data_cell_width_third, $data_cell_height, '#', 'LTRB', 0, 'C', 1);
			$pdf->Cell($data_cell_width, $data_cell_height, $l['w_score'], 'LTRB', 0, 'C', 1);
			$pdf->Cell($data_cell_width, $data_cell_height, $l['w_ip'], 'LTRB', 0, 'C', 1);
			$pdf->Cell($data_cell_width + $data_cell_width_third, $data_cell_height, $l['w_start'].' ['.$l['w_time_hhmmss'].']', 'LTRB', 0, 'C', 1);
			$pdf->Cell($data_cell_width + $data_cell_width_third, $data_cell_height, $l['w_end'].' ['.$l['w_time_hhmmss'].']', 'LTRB', 0, 'C', 1);
			$pdf->Cell($data_cell_width, $data_cell_height, $l['w_time'].' ['.$l['w_time_mmss'].']', 'LTRB', 0, 'C', 1);
			$pdf->Cell($data_cell_width, $data_cell_height, $l['w_reaction'].' [sec]', 'LTRB', 1, 'C', 1);
			$pdf->Ln($data_cell_height);
			
			// print table rows
			
			$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
			$itemcount = 1;
			
			while($mq = F_db_fetch_array($rq)) {
				$pdf->Cell($data_cell_width_third, $data_cell_height, $itemcount.' '.$qtype[($mq['question_type']-1)], 'LTRB', 0, $dirvalue, 0);
				$pdf->Cell($data_cell_width, $data_cell_height, $mq['testlog_score'], 'LTRB', 0, 'C', 0);
				$pdf->Cell($data_cell_width, $data_cell_height, getIpAsInt($mq['testlog_user_ip']), 'LTRB', 0, 'C', 0);
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
					$reaction_time = '';
				}
				$pdf->Cell($data_cell_width + $data_cell_width_third, $data_cell_height, $display_time, 'LTRB', 0, 'C', 0);
				$pdf->Cell($data_cell_width + $data_cell_width_third, $data_cell_height, $change_time, 'LTRB', 0, 'C', 0);
				$pdf->Cell($data_cell_width, $data_cell_height, $diff_time, 'LTRB', 0, 'C', 0);
				$pdf->Cell($data_cell_width, $data_cell_height, $reaction_time, 'LTRB', 1, 'C', 0);
				
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
					$pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + (2 * $data_cell_width_third)), $pdf->GetY(), F_decode_tcecode($mq['testlog_comment']), 1, 1);
					$pdf->SetTextColor(0, 0, 0); 
				}
				$pdf->Ln($data_cell_height);
				$itemcount++;
			}
		} else {
			F_display_db_error();
		}

		// END page data
		// ------------------------------------------------------------
	}
} else {
	F_display_db_error();
}

// Send PDF output
$pdf->Output('tcexam_result_'.$user_id.'_'.$test_id.'_'.date('Ymd', strtotime($test_end_time)).'.pdf', 'I');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
