<?php
//============================================================+
// File name   : tce_pdf_testgen.php
// Begin       : 2004-06-13
// Last Update : 2013-05-31
//
// Description : Creates PDF documents for offline testing.
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
 * Creates PDF documents for Pen-and-Paper testing.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-06-13
 * @param $_REQUEST['test_id'] (int) test ID
 * @param $_REQUEST['num'] (int) number of tests to generate
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('tce_functions_omr.php');
require_once('../../shared/config/tce_pdf.php');
require_once('../../shared/code/tcpdfex.php');

// --- Initialize variables
if (isset($_REQUEST['test_id']) and ($_REQUEST['test_id'] > 0)) {
    $test_id = intval($_REQUEST['test_id']);
    // check user's authorization
    if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
        exit;
    }
} else {
    exit;
}

if (isset($_REQUEST['num'])) {
    $test_num = intval($_REQUEST['num']);
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

// set header backlink QR-Code
$header_backlink = K_PATH_URL.'admin/code/tce_edit_test.php?test_id='.$test_id;
$pdf->setTCExamBackLink($header_backlink);

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
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->setLanguageArray($l); //set language items

if (defined('K_DIGSIG_ENABLE') and K_DIGSIG_ENABLE) {
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

// set style for QR-Code (used for test data)
$qrstyle = array(
    'border' => false,
    'vpadding' => 0,
    'hpadding' => 0,
    'fgcolor' => array(0,0,0),
    'bgcolor' => false,
    'position' => 'C',
    'module_width' => 2,
    'module_height' => 2
);

// define some constants used to build the OMR grid
$grid_color = array(255, 0, 0);
$grid_bg_color = array(255,205,205);
$circle_bg_color = array(255,255,255);
$line_width = 0.177; // about half point
$circle_radius = ($line_width * 11);
$circle_width = (2 * $circle_radius) + $line_width;
$circle_shift = $circle_width + $line_width;
$circle_half_width = ($circle_width / 2);
$align_mark_color = array(0,0,0);
$align_mark_width = ($line_width * 7);
$align_mark_lenght = ($line_width * 22);
$align_mark_shift = ($line_width * 8);
$row_height = $circle_width + (8 * $line_width);
$line_style = array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 0, 'color' => $grid_color);
// define barcode style
$bstyle = array(
    'position' => '',
    'align' => 'C',
    'stretch' => false,
    'fitwidth' => false,
    'cellfitalign' => '',
    'border' => false,
    'hpadding' => 0,
    'vpadding' => 0,
    'fgcolor' => array(0,0,0),
    'bgcolor' => false,
    'text' => false
);

// get test data
$testdata = F_getTestData($test_id);
$test_random_questions_select = F_getBoolean($testdata['test_random_questions_select']);
$test_random_questions_order = F_getBoolean($testdata['test_random_questions_order']);
$test_questions_order_mode = intval($testdata['test_questions_order_mode']);
$test_random_answers_select = F_getBoolean($testdata['test_random_answers_select']);
$test_random_answers_order = F_getBoolean($testdata['test_random_answers_order']);
$test_answers_order_mode = intval($testdata['test_answers_order_mode']);
$random_questions = ($test_random_questions_select or $test_random_questions_order);
$sql_answer_position = '';
if (!$test_random_answers_order and ($test_answers_order_mode == 0)) {
    $sql_answer_position = ' AND answer_position>0';
}
$sql_questions_order_by = '';
switch ($test_questions_order_mode) {
    case 0: { // position
        $sql_questions_order_by = ' AND question_position>0 ORDER BY question_position';
        break;
    }
    case 1: { // alphabetic
        $sql_questions_order_by = ' ORDER BY question_description';
        break;
    }
    case 2: { // ID
        $sql_questions_order_by = ' ORDER BY question_id';
        break;
    }
    case 3: { // type
        $sql_questions_order_by = ' ORDER BY question_type';
        break;
    }
    case 4: { // subject ID
        $sql_questions_order_by = ' ORDER BY question_subject_id';
        break;
    }
}

// NOTE: PDF tests are always random

for ($item = 1; $item <= $test_num; $item++) {
    // generate $test_num tests

    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    
    // data to be printed as QR-Code to be later used as input from scanner/image
    $barcode_test_data = array();
    $barcode_test_data[0] = $test_id;

    // --- start page data ---
    $pdf->AddPage();

    $test_ref = $test_id.':'.$item.':'.date(K_TIMESTAMP_FORMAT);

    // set barcode
    $pdf->setBarcode($test_ref);

    $pdf->SetFillColor(204, 204, 204);
    $pdf->SetLineWidth(0.1);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetTextColor(0, 0, 0);

    // print document name (title)
    $pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA * K_TITLE_MAGNIFICATION);
    $pdf->Cell(0, $main_cell_height * K_TITLE_MAGNIFICATION, $doc_title, 1, 1, 'C', 1);

    $pdf->SetTextColor(255, 0, 0);
    $pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
    $pdf->Cell(0, 5, '['.$test_ref.']', 0, 1, 'C', false, '', 0, false, 'T', 'M');
    $pdf->SetTextColor(0, 0, 0);

    //$pdf->Ln(5);

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
    
    // IDs of MCSA questions with more than one correct answer
    $right_answers_mcsa_questions_ids = '';
    // IDs of MCSA questions with more than one wrong answer
    $wrong_answers_mcsa_questions_ids = array();
    // IDs of MCMA questions with more than one answer
    $answers_mcma_questions_ids = array();
    // IDs of ORDER questions with more than one ordering answer
    $answers_order_questions_ids = '';

    // count questions
    $itemcount = 1;

    // *-*-*-*-*

    // selected questions IDs
    $selected_questions = '0';
    // 2. for each set of subjects
    // ------------------------------
    $sql = 'SELECT *
		FROM '.K_TABLE_TEST_SUBJSET.'
		WHERE tsubset_test_id='.$test_id.'
		ORDER BY tsubset_type, tsubset_difficulty, tsubset_answers DESC';
    if ($r = F_db_query($sql, $db)) {
        $questions_data = array();
        while ($m = F_db_fetch_array($r)) {
            // 3. select the subjects IDs
            $selected_subjects = '0';
            $sqlt = 'SELECT subjset_subject_id FROM '.K_TABLE_SUBJECT_SET.' WHERE subjset_tsubset_id='.$m['tsubset_id'];
            if ($rt = F_db_query($sqlt, $db)) {
                while ($mt = F_db_fetch_array($rt)) {
                    $selected_subjects .= ','.$mt['subjset_subject_id'];
                }
            }
            // 4. select questions
            // ------------------------------
            $sqlq = 'SELECT question_id, question_type, question_difficulty, question_position, question_description
				FROM '.K_TABLE_QUESTIONS.'';
            $sqlq .= ' WHERE question_subject_id IN ('.$selected_subjects.')
				AND question_difficulty='.$m['tsubset_difficulty'].'
				AND question_enabled=\'1\'
				AND question_id NOT IN ('.$selected_questions.')';
            if ($m['tsubset_type'] > 0) {
                $sqlq .= ' AND question_type='.$m['tsubset_type'];
            }
            if ($m['tsubset_type'] == 1) {
                // (MCSA : Multiple Choice Single Answer) ----------
                // get questions with the right number of answers
                if (empty($right_answers_mcsa_questions_ids)) {
                    $right_answers_mcsa_questions_ids = '0';
                    $sqlt = 'SELECT DISTINCT answer_question_id FROM '.K_TABLE_ANSWERS.' WHERE answer_enabled=\'1\' AND answer_isright=\'1\''.$sql_answer_position.'';
                    if ($rt = F_db_query($sqlt, $db)) {
                        while ($mt = F_db_fetch_array($rt)) {
                            $right_answers_mcsa_questions_ids .= ','.$mt['answer_question_id'];
                        }
                    }
                }
                $sqlq .= ' AND question_id IN ('.$right_answers_mcsa_questions_ids.')';
                if ($m['tsubset_answers'] > 0) {
                    if (!isset($wrong_answers_mcsa_questions_ids['\''.$m['tsubset_answers'].'\''])) {
                        $wrong_answers_mcsa_questions_ids['\''.$m['tsubset_answers'].'\''] = '0';
                        $sqlt = 'SELECT answer_question_id FROM '.K_TABLE_ANSWERS.' WHERE answer_enabled=\'1\' AND answer_isright=\'0\''.$sql_answer_position.' GROUP BY answer_question_id HAVING (COUNT(answer_id)>='.($m['tsubset_answers']-1).')';
                        if ($rt = F_db_query($sqlt, $db)) {
                            while ($mt = F_db_fetch_array($rt)) {
                                $wrong_answers_mcsa_questions_ids['\''.$m['tsubset_answers'].'\''] .= ','.$mt['answer_question_id'];
                            }
                        }
                    }
                    $sqlq .= ' AND question_id IN ('.$wrong_answers_mcsa_questions_ids['\''.$m['tsubset_answers'].'\''].')';
                }
            } elseif ($m['tsubset_type'] == 2) {
                // (MCMA : Multiple Choice Multiple Answers) -------
                // get questions with the right number of answers
                if ($m['tsubset_answers'] > 0) {
                    if (!isset($answers_mcma_questions_ids['\''.$m['tsubset_answers'].'\''])) {
                        $answers_mcma_questions_ids['\''.$m['tsubset_answers'].'\''] = '0';
                        $sqlt = 'SELECT answer_question_id FROM '.K_TABLE_ANSWERS.' WHERE answer_enabled=\'1\''.$sql_answer_position.' GROUP BY answer_question_id HAVING (COUNT(answer_id)>='.$m['tsubset_answers'].')';
                        if ($rt = F_db_query($sqlt, $db)) {
                            while ($mt = F_db_fetch_array($rt)) {
                                $answers_mcma_questions_ids['\''.$m['tsubset_answers'].'\''] .= ','.$mt['answer_question_id'];
                            }
                        }
                    }
                    $sqlq .= ' AND question_id IN ('.$answers_mcma_questions_ids['\''.$m['tsubset_answers'].'\''].')';
                }
            } elseif ($m['tsubset_type'] == 4) {
                // ORDERING ----------------------------------------
                if (empty($answers_order_questions_ids)) {
                    $answers_order_questions_ids = '0';
                    $sqlt = 'SELECT answer_question_id FROM '.K_TABLE_ANSWERS.' WHERE answer_enabled=\'1\' AND answer_position>0 GROUP BY answer_question_id HAVING (COUNT(answer_id)>1)';
                    if ($rt = F_db_query($sqlt, $db)) {
                        while ($mt = F_db_fetch_array($rt)) {
                            $answers_order_questions_ids .= ','.$mt['answer_question_id'];
                        }
                    }
                }
                $sqlq .= ' AND question_id IN ('.$answers_order_questions_ids.')';
            }
            if ($random_questions) {
                $sqlq .= ' ORDER BY RAND()';
            } else {
                $sqlq .= $sql_questions_order_by;
            }
            if (K_DATABASE_TYPE == 'ORACLE') {
                $sqlq = 'SELECT * FROM ('.$sqlq.') WHERE rownum <= '.$m['tsubset_quantity'].'';
            } else {
                $sqlq .= ' LIMIT '.$m['tsubset_quantity'].'';
            }
            if ($rq = F_db_query($sqlq, $db)) {
                while ($mq = F_db_fetch_array($rq)) {
                    // store questions data
                    $tmp_data = array(
                        'id' => $mq['question_id'],
                        'type' => $mq['question_type'],
                        'difficulty' => $mq['question_difficulty'],
                        'description' => $mq['question_description'],
                        'answers' => $m['tsubset_answers'],
                        'score' => ($testdata['test_score_unanswered'] * $mq['question_difficulty'])
                        );
                    if ($random_questions or ($test_questions_order_mode != 0)) {
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
        // 5. STORE QUESTIONS AND ANSWERS
        // ------------------------------
        if ($random_questions) {
            shuffle($questions_data);
        } else {
            ksort($questions_data);
        }

        // *-*-*-*-*

        // 4. PRINT QUESTIONS
        // ------------------------------
        $question_order = 0;
        foreach ($questions_data as $key => $q) {
            ++$question_order;

            // add question ID to QR-Code data
            $barcode_test_data[$question_order] = array(0 => $q['id'], 1 => array());

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
                if ($rsa = F_db_query($sqlsa, $db)) {
                    while ($msa = F_db_fetch_array($rsa)) {
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
                    $pdf->MultiCell(0, PDF_TEXTANSWER_HEIGHT, $shortanswers, 1, '', false, 1, '', '', true, 0, false, true, 0, 'T', false);
                } else {
                    // split text area across two pages
                    $pdf->Cell(0, $restspace, '', 'LTR', 1, 'C', 0);
                    $pdf->Cell(2*$data_cell_width_third, $data_cell_height, '', 0, 0, 'R', 0);
                    $pdf->MultiCell(0, (PDF_TEXTANSWER_HEIGHT - $restspace), $shortanswers, 'LRB', '', false, 1, '', '', true, 0, false, true, 0, 'T', false);
                }
                $pdf->SetTextColor(0, 0, 0, false);
                $pdf->Ln($data_cell_height);
            } else {
                // for each question
                $randorder = $test_random_answers_order;
                $answers_ids = array(); // array used to store answers IDs
                switch ($q['type']) {
                    case 1: { // MCSA
                        // select first right answer
                        $answers_ids += F_selectAnswers($q['id'], 1, false, 1, 0, $randorder, $test_answers_order_mode);
                        // select remaining answers
                        $answers_ids += F_selectAnswers($q['id'], 0, false, ($q['answers'] - 1), 1, $randorder, $test_answers_order_mode);
                        break;
                    }
                    case 2: { // MCMA
                        // select answers
                        $answers_ids += F_selectAnswers($q['id'], '', false, $q['answers'], 0, $randorder, $test_answers_order_mode);
                        break;
                    }
                    case 4: { // ORDERING
                        // select answers
                        $randorder = true;
                        $answers_ids += F_selectAnswers($q['id'], '', true, 0, 0, $randorder, $test_answers_order_mode);
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
                    ++$answ_id;

                    // add answer ID to QR-Code data
                    $barcode_test_data[$question_order][1][$answ_id] = $answer_id;

                    // display each answer option
                    $sqla = 'SELECT *
						FROM '.K_TABLE_ANSWERS.'
						WHERE answer_id='.$answer_id.'
						LIMIT 1';
                    if ($ra = F_db_query($sqla, $db)) {
                        if ($ma = F_db_fetch_array($ra)) {
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

    // *** OMR SECTION (Optical Mark Recognition) ******************************
    // Support up to 30 questions per sheet and up to 12 answers per questions.
    // Support only MCSA and MCMA questions.

    // remove default barcodes from header and footer to not interfere with data QR-code
    $pdf->resetHeaderTemplate();
    $pdf->setTCExamBackLink('');
    $pdf->AddPage('P'); // force portrait mode
    $pdf->setBarcode('');

    $pdf->SetTextColor(255, 0, 0);
    $pdf->SetFont(PDF_FONT_NAME_DATA, '', round(PDF_FONT_SIZE_DATA * 1.5));
    $pdf->Cell(0, 0, 'OMR DATA', 0, 1, 'C', false, '', 0, false, 'T', 'M');
    $pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
    $pdf->Cell(0, 0, '['.$test_ref.']', 0, 1, 'C', false, '', 0, false, 'T', 'M');
    $pdf->SetTextColor(0, 0, 0);

    // encode data to be printed on QR-Code
    $qr_test_data = F_encodeOMRTestData($barcode_test_data);

    $pagedim = $pdf->getPageDimensions();
    $qrw = $pagedim['wk'] - $pagedim['lm'] - $pagedim['rm']; // maximum width
    $qry = (($pagedim['hk'] - $qrw) / 2); // vertically centered on page

    // QR-CODE mode H (best error correction)
    // This will be used to create test logs
    $pdf->write2DBarcode($qr_test_data, 'QRCODE,L', '', $qry, '', '', $qrstyle, 'N');

    // --- OMR ANSWER SHEET ---------------------------------------------------

    // Instructions:
    // mark the correct/true answers
    // use blue/black marker
    // fill the box completely
    // make no stray marks

    // center the block on the page
    $start_x = (($pagedim['wk'] - 173.964) / 2);
    $start_y = (($pagedim['hk'] - 178.062) / 2);

    $pdf->SetLineStyle($line_style);
    $pdf->SetTextColorArray($grid_color);
    $pdf->SetCellPadding($line_width);

    // number of required OMR pages
    $num_questions = count($barcode_test_data) - 1;
    $num_omr_pages = ceil($num_questions / 30); // max 30 questions per page

    // remove barcode from footer
    $pdf->setBarcode('');

    // set style for barcodes containing first question number
    $bcstyle = array(
        'position' => 'C',
        'align' => 'C',
        'stretch' => false,
        'fitwidth' => false,
        'cellfitalign' => '',
        'border' => false,
        'padding' => 0,
        'fgcolor' => array(0,0,0),
        'bgcolor' => false,
        'text' => false
    );
    // barcode y position
    $bcy = $pagedim['hk'] - $pdf->getFooterMargin() - 12;

    // disable auto-page-break
    $pdf->SetAutoPageBreak(false, 0);

    for ($omrpage = 0; $omrpage < $num_omr_pages; ++$omrpage) {
        $pdf->AddPage('P');

        $pdf->SetTextColor(255, 0, 0);
        $pdf->SetFont(PDF_FONT_NAME_DATA, '', round(PDF_FONT_SIZE_DATA * 1.5));
        $pdf->Cell(0, 0, 'OMR ANSWER SHEET '.($omrpage + 1), 0, 1, 'C', false, '', 0, false, 'T', 'M');
        $pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
        $pdf->Cell(0, 0, '['.$test_ref.']', 0, 1, 'C', false, '', 0, false, 'T', 'M');
        $pdf->SetTextColor(0, 0, 0);

        // disable RTL mode
        $rtl = $pdf->getRTL();
        $pdf->setRTL(false);

        $x = $start_x;
        $y = $start_y;

        // --------------------

        // set the starting row number (starting question number)
        $first_question = (1 + (30 * $omrpage));
        $qnum = sprintf('%04d', $first_question);

        // --------------------

        // top alignment marks for columns
        $x = $start_x;
        $pdf->Rect($x, $y, $align_mark_lenght, $align_mark_lenght, 'F', array(), $align_mark_color);
        $x += $align_mark_lenght + 9;
        $pdf->SetFont('helvetica', '', 10);
        for ($i = 0; $i < 12; ++$i) {
            // vertical alignment mark
            $x += $circle_shift;
            $pdf->Rect($x + $align_mark_shift, $y, $align_mark_width, $align_mark_lenght, 'F', array(), $align_mark_color);
            $x += $circle_shift;
            $pdf->Rect($x + $align_mark_shift, $y, $align_mark_width, $align_mark_lenght, 'F', array(), $align_mark_color);
            $x += $circle_shift;
        }
        $y += ($row_height + $circle_half_width);

        // --------------------

        for ($r=0; $r < 30; ++$r) {
            $current_question = ($first_question + $r);
            // start at left margin
            $x = $start_x;
            // center of circles
            $cy = $y + $circle_half_width;
            // left alignment mark for row
            $pdf->Rect($x, $y + $align_mark_shift, $align_mark_lenght, $align_mark_width, 'F', array(), $align_mark_color);
            $x += $align_mark_lenght;
            if ($current_question <= $num_questions) {
                if (($r % 2) != 0) {
                    // row background
                    $pdf->Rect($x, $y - (4 * $line_width), 166.176, $circle_width + (8 * $line_width), 'F', array(), $grid_bg_color);
                }
                // print question number
                $pdf->SetXY($x, $y);
                $pdf->SetFont('courier', 'B', 10);
                $pdf->SetTextColorArray($grid_color);
                $pdf->Cell(8, $circle_width, $current_question, 0, 0, 'R', false, '', 0, true, 'T', 'M');
                $x += 9;
                // question type
                $question_type = $questions_data[($current_question - 1)]['type'];
                if ($question_type < 3) { // MCSA or MCMA question
                    // number of answers
                    $num_answers = count($barcode_test_data[$current_question][1]);
                    for ($i = 1; $i <= 12; ++$i) { // for each answer
                        if ($i <= $num_answers) {
                            // print answer number
                            $pdf->SetXY($x, $y);
                            $pdf->SetFont('helvetica', '', 8);
                            $pdf->SetTextColorArray($grid_color);
                            $pdf->Cell($circle_shift, $circle_width, $i, 0, 0, 'R', false, '', 0, true, 'T', 'M');
                            $x += $circle_shift;
                            // select circle
                            $pdf->Circle($x + $circle_half_width, $cy, $circle_radius, 0, 360, 'DF', $line_style, $circle_bg_color, 2);
                            $pdf->SetXY($x, $y);
                            $pdf->SetFont(PDF_FONT_NAME_DATA, '', 6);
                            $pdf->SetTextColorArray($grid_bg_color);
                            $pdf->Cell($circle_width, $circle_width, $l['w_true_acronym'], 0, 0, 'C', false, '', 1, true, 'T', 'M');
                            $x += $circle_shift;
                            if ($question_type == 2) { // MCMA question
                                $pdf->Circle($x + $circle_half_width, $cy, $circle_radius, 0, 360, 'DF', $line_style, $circle_bg_color, 2);
                                $pdf->SetXY($x, $y);
                                $pdf->Cell($circle_width, $circle_width, $l['w_false_acronym'], 0, 0, 'C', false, '', 1, true, 'T', 'M');
                            }
                        } else {
                            $x += (2 * $circle_shift);
                        }
                        $x += $circle_shift;
                    }
                } else {
                    $x += (36 * $circle_shift);
                }
            } else {
                $x += 9 + (36 * $circle_shift);
            }
            $x += $circle_shift;
            // right alignment mark for row
            $pdf->Rect($x, $y + $align_mark_shift, $align_mark_lenght, $align_mark_width, 'F', array(), $align_mark_color);
            $y += $row_height;
        }

        // --------------------

        // bottom alignment marks for columns
        $x = $start_x + $align_mark_lenght + 9;
        $y += $circle_half_width;
        $pdf->SetFont('helvetica', '', 10);
        for ($i = 0; $i < 12; ++$i) {
            // vertical alignment mark
            $x += $circle_shift;
            $pdf->Rect($x + $align_mark_shift, $y, $align_mark_width, $align_mark_lenght, 'F', array(), $align_mark_color);
            $x += $circle_shift;
            $pdf->Rect($x + $align_mark_shift, $y, $align_mark_width, $align_mark_lenght, 'F', array(), $align_mark_color);
            $x += $circle_shift;
        }

        // --------------------

        // set barcode to identify starting question number
        $pdf->write1DBarcode($qnum, 'C128C', 0, $bcy, '', 10, 0.8, $bcstyle, '');

        // reset RTL mode
        $pdf->setRTL($rtl);
    } // end for each OMR page
} //end for test_num

$pdf->lastpage(true);
$pdf->SetAutoPageBreak(false);
$pdf->SetFont('helvetica', '', 5);
$pdf->SetTextColor(0, 127, 255);
$msg = "\x50\x6f\x77\x65\x72\x65\x64\x20\x62\x79\x20\x54\x43\x45\x78\x61\x6d\x20\x28\x77\x77\x77\x2e\x74\x63\x65\x78\x61\x6d\x2e\x6f\x72\x67\x29";
$lnk = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x74\x63\x65\x78\x61\x6d\x2e\x6f\x72\x67";
$pdf->SetXY(15, $pdf->getPageHeight(), true);
$pdf->Cell(0, 0, $msg, 0, 0, 'R', 0, $lnk, 0, false, 'B', 'B');

// close and outputs PDF document
$pdf->Output('tcexam_test_'.$test_id.'_'.date('YmdHis').'.pdf', 'D');

//============================================================+
// END OF FILE
//============================================================+
