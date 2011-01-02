<?php
//============================================================+
// File name   : tce_pdf_testgen.php
// Begin       : 2004-06-13
// Last Update : 2011-01-02
//
// Description : Creates PDF documents for offline testing.
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
 * Creates PDF documents for Pen-and-Paper testing.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-06-13
 * @param $_REQUEST['testid'] (int) test ID
 * @param $_REQUEST['num'] (int) number of tests to generate
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/config/tce_pdf.php');
require_once('../../shared/code/tcpdfex.php');

// --- Initialize variables

if (isset($_REQUEST['testid']) AND ($_REQUEST['testid'] > 0)) {
	$test_id = intval($_REQUEST['testid']);
	// check user's authorization
	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		exit;
	}
} else {
	exit;
}

if (isset($_REQUEST['num'])) {
	$test_num = $_REQUEST['num'];
} else {
	$test_num = 1;
}

$doc_title = unhtmlentities($l['w_test']);
$doc_description = F_compact_string(unhtmlentities($l['h_test']));
$page_elements = 6;
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
$pdf = new TCPDFEX(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, $isunicode);

// Set backlink QR-Code
$pdf->setTCExamBackLink(K_PATH_URL.'admin/code/tce_edit_test.php?test_id='.$test_id);

// set document information
$pdf->SetCreator('TC'.'Ex'.'am'.' ver.'.K_TCEXAM_VERSION.'');
$pdf->SetAuthor(PDF_AUTHOR);
$pdf->SetTitle($doc_title);
$pdf->SetSubject($doc_description);
$pdf->SetKeywords('TCExam, '.$doc_title);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); //set image scale factor

$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

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
$testdata = F_getTestData($test_id);

// NOTE: PDF tests are always random

for ($item = 1; $item <= $test_num; $item++) {
	// generate $test_num tests

	// --- start page data ---
	$pdf->AddPage();

	// set barcode
	$pdf->setBarcode(''.$test_id.':'.$item.':'.date(K_TIMESTAMP_FORMAT));

	$pdf->SetFillColor(204, 204, 204);
	$pdf->SetLineWidth(0.1);
	$pdf->SetDrawColor(0, 0, 0);

	// print document name (title)
	$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA * K_TITLE_MAGNIFICATION);
	$pdf->Cell(0, $main_cell_height * K_TITLE_MAGNIFICATION, $doc_title, 1, 1, 'C', 1);

	$pdf->Ln(5);

	// display user info input boxes

	// calculate some sizes
	$user_elements = 4;
	$user_data_cell_width = round($page_width / $user_elements, 2);

	// print table headings
	$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);

	$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_lastname'], 1, 0, 'C', 1);
	$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_firstname'], 1, 0, 'C', 1);
	$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_code'], 1, 0, 'C', 1);
	$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_score'], 1, 1, 'C', 1);

	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

	$pdf->Cell($user_data_cell_width, (1.5 * $data_cell_height), '', 1, 0, 'C', 0);
	$pdf->Cell($user_data_cell_width, (1.5 * $data_cell_height), '', 1, 0, 'C', 0);
	$pdf->Cell($user_data_cell_width, (1.5 * $data_cell_height), '', 1, 0, 'C', 0);
	$pdf->Cell($user_data_cell_width, (1.5 * $data_cell_height), '', 1, 1, 'C', 0);

	$pdf->Ln(5);

	// --- display test info ---

	$info_cell_width = round($page_width / 4, 2);

	$boxStartY = $pdf->GetY(); // store current Y position

	// test name
	$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA * HEAD_MAGNIFICATION);
	$pdf->Cell($page_width, $data_cell_height * HEAD_MAGNIFICATION, $l['w_test'].': '.$testdata['test_name'], 1, 1, '', 1);

	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

	$infoStartY = $pdf->GetY() + 2; // store current Y position
	$pdf->SetY($infoStartY);

	// test duration
	$pdf->Cell(1.5*$data_cell_width, $data_cell_height, $l['w_test_time'].' ['.$l['w_minutes'].']: ', 0, 0, $dirlabel, 0);
	$pdf->Cell($data_cell_width, $data_cell_height, $testdata['test_duration_time'], 0, 1, $dirvalue, 0);

	// test start time (to be compiled by the user)
	$pdf->Cell(1.5*$data_cell_width, $data_cell_height, $l['w_time_begin'].': ', 0, 0, $dirlabel, 0);
	$pdf->Cell($data_cell_width, $data_cell_height, '', 0, 1, $dirvalue, 0);

	// test end time (to be compiled by the user)
	$pdf->Cell(1.5*$data_cell_width, $data_cell_height, $l['w_time_end'].': ', 0, 0, $dirlabel, 0);
	$pdf->Cell($data_cell_width, $data_cell_height, '', 0, 1, $dirvalue, 0);

	// score for right answer
	$pdf->Cell(1.5*$data_cell_width, $data_cell_height, $l['w_score_right'].': ', 0, 0, $dirlabel, 0);
	$pdf->Cell($data_cell_width, $data_cell_height, $testdata['test_score_right'], 0, 1, $dirvalue, 0);

	// score for wrong answer
	$pdf->Cell(1.5*$data_cell_width, $data_cell_height, $l['w_score_wrong'].': ', 0, 0, $dirlabel, 0);
	$pdf->Cell($data_cell_width, $data_cell_height, $testdata['test_score_wrong'], 0, 1, $dirvalue, 0);

	// score for missing answer
	$pdf->Cell(1.5*$data_cell_width, $data_cell_height, $l['w_score_unanswered'].': ', 0, 0, $dirlabel, 0);
	$pdf->Cell($data_cell_width, $data_cell_height, $testdata['test_score_unanswered'], 0, 1, $dirvalue, 0);

	// max score
	$pdf->Cell(1.5*$data_cell_width, $data_cell_height, $l['w_max_score'].': ', 0, 0, $dirlabel, 0);
	$pdf->Cell($data_cell_width, $data_cell_height, $testdata['test_max_score'], 0, 1, $dirvalue, 0);

	// minimum required score to pass the exam
	if ($testdata['test_score_threshold'] > 0) {
		$pdf->Cell(1.5*$data_cell_width, $data_cell_height, $l['w_test_score_threshold'].': ', 0, 0, $dirlabel, 0);
		$pdf->Cell($data_cell_width, $data_cell_height, $testdata['test_score_threshold'], 0, 1, $dirvalue, 0);
	}

	$boxEndY = $pdf->GetY();

	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

	// test description
	$pdf->writeHTMLCell(0, ($boxEndY - $infoStartY + 4), (PDF_MARGIN_LEFT + ($info_cell_width * 2)), $infoStartY - 2, F_decode_tcecode($testdata['test_description']), 1, 1);

	// print box around test info
	$pdf->SetY($boxStartY);
	$pdf->Cell($page_width, ($boxEndY - $boxStartY + 2), '', 1, 1, 'C', 0);

	// --- end test info ---

	$pdf->Ln(5);

	/*
	$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
	$pdf->Cell($data_cell_width_third, $data_cell_height, "#", 1, 0, 'C', 1);
	$pdf->Cell($data_cell_width_third, $data_cell_height, $l['w_score'], 1, 0, 'C', 1);
	$pdf->Cell(0, $data_cell_height, $l['w_question'], 1, 1, 'C', 1);
	$pdf->Ln($data_cell_height);
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
	*/

	$itemcount = 1; // count questions

	// selected questions IDs
	$selected_questions = '0';

	// 1. for each set of subjects
	// ------------------------------
	$sql = 'SELECT *
		FROM '.K_TABLE_TEST_SUBJSET.'
		WHERE tsubset_test_id='.$test_id.'
		ORDER BY tsubset_type, tsubset_difficulty, tsubset_answers DESC';
	if($r = F_db_query($sql, $db)) {
		$questions_data = array();
		while ($m = F_db_fetch_array($r)) {
			// 2. select questions
			// ------------------------------
			$sqlq = 'SELECT question_id, question_type, question_difficulty, question_position, question_description
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
			if (K_DATABASE_TYPE == 'ORACLE') {
				$sqlq = 'SELECT * FROM ('.$sqlq.') WHERE rownum <= '.$m['tsubset_quantity'].'';
			} else {
				$sqlq .= ' LIMIT '.$m['tsubset_quantity'].'';
			}
			if($rq = F_db_query($sqlq, $db)) {
				while ($mq = F_db_fetch_array($rq)) {
					// store questions data
					$tmp_data = array(
						'id' => $mq['question_id'],
						'type' => $mq['question_type'],
						'answers' => $m['tsubset_answers'],
						'score' => ($testdata['test_score_unanswered'] * $mq['question_difficulty']),
						'difficulty' => $mq['question_difficulty'],
						'description' => $mq['question_description']
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
			} // --- end 2
		} // end while for each set of subjects
		// 3. SORT QUESTIONS
		// ------------------------------
		if ((!F_getBoolean($testdata['test_random_questions_select'])) AND (!F_getBoolean($testdata['test_random_questions_order']))) {
			// order questions
			ksort($questions_data);
		} else {
			shuffle($questions_data);
		}
		// 4. PRINT QUESTIONS
		// ------------------------------
		$question_order = 0;
		foreach ($questions_data as $key => $q) {
			$question_order++;

			// start transaction
			$pdf->startTransaction();
			$block_page = $pdf->getPage();
			$print_block = 2; // 2 tries max
			while ($print_block > 0) {

				// ------------------------------
				// add question number
				$pdf->Cell($data_cell_width_third, $data_cell_height, ''.$itemcount.' '.$qtype[($q['type']-1)].'', 1, 0, 'R', 0);
				// add max points
				$pdf->Cell($data_cell_width_third, $data_cell_height, ''.($q['difficulty'] * $testdata['test_score_right']).'', 1, 0, 'R', 0);
				// add question description
				$pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + (2 * $data_cell_width_third)), $pdf->GetY(), F_decode_tcecode($q['description']), 1, 1);
				// ------------------------------

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

			$itemcount++;

			if ($q['type'] == 3) {
				// print space for user text answer
				$restspace = $pdf->getPageHeight() - $pdf->GetY() - $pdf->getBreakMargin();
				$pdf->Cell(2*$data_cell_width_third, $data_cell_height, '', 0, 0, 'R', 0);

				// get the list of short answers
				$shortanswers = '';
				$sqlsa = 'SELECT answer_description
					FROM '.K_TABLE_ANSWERS.'
					WHERE answer_question_id='.$q['id'].'
						AND answer_enabled=\'1\'
						AND answer_isright=\'1\'';
				if($rsa = F_db_query($sqlsa, $db)) {
					while($msa = F_db_fetch_array($rsa)) {
						$shortanswers .= ''.$msa['answer_description'].' ; ';
					}
				} else {
					F_display_db_error();
				}

				// print correct answer in hidden white color
				/* to display the correct results, from PDF viewer, go to "Accessibility" ->
				   "Page Display preferences", check "Replace Document Colors",
				   uncheck "Only change the color of black text or line art" */
				$pdf->SetTextColor(255, 255, 255, false);
				if ($restspace > PDF_TEXTANSWER_HEIGHT) {
					$pdf->Cell(0, PDF_TEXTANSWER_HEIGHT, $shortanswers, 1, 1, 'C', 0);
				} else {
					// split text area across two pages
					$pdf->Cell(0, $restspace, '', 'LTR', 1, 'C', 0);
					$pdf->Cell(2*$data_cell_width_third, $data_cell_height, '', 0, 0, 'R', 0);
					$pdf->Cell(0, (PDF_TEXTANSWER_HEIGHT - $restspace), $shortanswers, 'LRB', 1, 'C', 0);
				}
				$pdf->SetTextColor(0, 0, 0, false);
				$pdf->Ln($data_cell_height);
			} else {
				$randorder = F_getBoolean($testdata['test_random_answers_order']);
				// for each question
				$answers_ids = array(); // array used to store answers IDs
				switch ($q['type']) {
					case 1: { // MCSA
						// select first right answer
						$answers_ids += F_selectAnswers($q['id'], 1, false, 1, 0, $randorder);
						// select remaining answers
						$answers_ids += F_selectAnswers($q['id'], 0, false, ($q['answers'] - 1), 1, $randorder);
						break;
					}
					case 2: { // MCMA
						// select answers
						$answers_ids += F_selectAnswers($q['id'], '', false, $q['answers'], 0, $randorder);
						break;
					}
					case 4: { // ORDERING
						// select answers
						$randorder = true;
						$answers_ids += F_selectAnswers($q['id'], '', true, 0, 0, $randorder);
						break;
					}
				}
				// randomizes the order of the answers
				if ($randorder) {
					shuffle($answers_ids);
				} else {
					ksort($answers_ids);
				}
				// print answers
				// add answers
				$answ_id = 0;
				// display multiple answers
				while (list($key, $answer_id) = each($answers_ids)) {
					$answ_id++;
					// display each answer option
					$sqla = 'SELECT *
						FROM '.K_TABLE_ANSWERS.'
						WHERE answer_id='.$answer_id.'
						LIMIT 1';
					if($ra = F_db_query($sqla, $db)) {
						if($ma = F_db_fetch_array($ra)) {
							$rightanswer = '';
							if ($mq['question_type'] == 4) {
								$rightanswer = $ma['answer_position'];
							} elseif (F_getBoolean($ma['answer_isright'])) {
								$rightanswer = 'X';
							}

							// start transaction
							$pdf->startTransaction();
							$block_page = $pdf->getPage();
							$print_block = 2; // 2 tries max
							while ($print_block > 0) {

								// ------------------------------
								$pdf->Cell(2*$data_cell_width_third, $data_cell_height, '', 0, 0, 'C', 0);
								// print correct answer in hidden white color
								/* to display the correct results, from PDF viewer, go to "Accessibility" ->
								   "Page Display preferences", check "Replace Document Colors",
								   uncheck "Only change the color of black text or line art" */
								$pdf->SetTextColor(255, 255, 255, false);
								$pdf->Cell($data_cell_width_third, $data_cell_height, $rightanswer, 1, 0, 'C', 0);
								$pdf->SetTextColor(0, 0, 0, false);
								$pdf->Cell($data_cell_width_third, $data_cell_height, $answ_id, 1, 0, 'R', 0);
								$pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + $data_cell_width + $data_cell_width_third), $pdf->GetY(), F_decode_tcecode($ma['answer_description']), 1, 1);
								// ------------------------------

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

						}
					} else {
						F_display_db_error();
					}
				}
				$pdf->Ln($data_cell_height);
			} // not-text question
		} // end while type of questions
	} else {
		F_display_db_error();
	}
} //end for test_num

$pdf->lastpage(true);
$pdf->SetAutoPageBreak(false);
$pdf->SetFont('helvetica', '', 5);
$pdf->SetTextColor(0,127,255);
$msg = "\x50\x6f\x77\x65\x72\x65\x64\x20\x62\x79\x20\x54\x43\x45\x78\x61\x6d\x20\x28\x77\x77\x77\x2e\x74\x63\x65\x78\x61\x6d\x2e\x6f\x72\x67\x29";
$lnk = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x74\x63\x65\x78\x61\x6d\x2e\x6f\x72\x67";
$pdf->SetXY(15, $pdf->getPageHeight(), true);
$pdf->Cell(0, 0, $msg, 0, 0, 'R', 0, $lnk, 0, false, 'B', 'B');

//Close and outputs PDF document
$pdf->Output('tcexam_test_'.$test_id.'_'.date('YmdHis').'.pdf', 'D');

//============================================================+
// END OF FILE
//============================================================+
