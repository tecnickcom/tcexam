<?php
//============================================================+
// File name   : tce_pdf_all_questions.php
// Begin       : 2004-06-10
// Last Update : 2011-02-24
//
// Description : Creates a PDF document containing exported questions.
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Creates a PDF document containing exported questions.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2005-07-06
 * @param $_REQUEST['subject_id'] (int) topic ID
 */

/**
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/code/tce_functions_auth_sql.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/config/tce_pdf.php');
require_once('../../shared/code/tcpdfex.php');

if ((isset($_REQUEST['expmode']) and ($_REQUEST['expmode'] > 0))
    and (isset($_REQUEST['module_id']) and ($_REQUEST['module_id'] > 0))
    and (isset($_REQUEST['subject_id']) and ($_REQUEST['subject_id'] > 0))) {
    $expmode = intval($_REQUEST['expmode']);
    $module_id = intval($_REQUEST['module_id']);
    $subject_id = intval($_REQUEST['subject_id']);
} else {
    exit;
}

// check user's authorization for module
if (!F_isAuthorizedUser(K_TABLE_MODULES, 'module_id', $module_id, 'module_user_id')) {
    exit;
}

$show_answers = true;
if (isset($_REQUEST['hide_answers']) and ($_REQUEST['hide_answers'] == 1)) {
    $show_answers = false;
}

$doc_title = unhtmlentities($l['t_questions_list']);
$doc_description = F_compact_string(unhtmlentities($l['hp_select_all_questions']));
$page_elements = 6;

$qtype = array('S', 'M', 'T', '0'); // question types
$qright = array(' ', '*'); // question types

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

switch ($expmode) {
    case 1: {
        // Set backlink QR-Code
        $pdf->setTCExamBackLink(K_PATH_URL.'admin/code/tce_show_all_questions.php?subject_module_id='.$module_id.'&subject_id='.$subject_id);
        break;
    }
    case 2: {
        // Set backlink QR-Code
        $pdf->setTCExamBackLink(K_PATH_URL.'admin/code/tce_show_all_questions.php?subject_module_id='.$module_id);
        break;
    }
    case 3: {
        // Set backlink QR-Code
        $pdf->setTCExamBackLink(K_PATH_URL.'admin/code/tce_show_all_questions.php');
        break;
    }
}

// set document information
$pdf->SetCreator('TCExam ver.'.K_TCEXAM_VERSION."");
$pdf->SetAuthor(PDF_AUTHOR);
$pdf->SetTitle($doc_title);
$pdf->SetSubject($doc_description);
$pdf->SetKeywords('TCExam, '.$doc_title);

$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

//set auto page breaks
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->setLanguageArray($l); //set language items

// set default alignment for cells
$defalign = $l['a_meta_dir']=='rtl' ? 'R' : 'L';

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

// ---- module
$andmodwhere = '';
if ($expmode < 3) {
    $andmodwhere = 'module_id='.$module_id.'';
}
$sqlm = F_select_modules_sql($andmodwhere);
if ($rm = F_db_query($sqlm, $db)) {
    while ($mm = F_db_fetch_array($rm)) {
        $module_id =  $mm['module_id'];
        $module_name = $mm['module_name'];
        //$module_enabled = F_getBoolean($mm['module_enabled']);

        // ---- topic
        $where_sqls = 'subject_module_id='.$module_id.'';
        if ($expmode < 2) {
            $where_sqls .= ' AND subject_id='.$subject_id.'';
        }
        $sqls = F_select_subjects_sql($where_sqls);
        if ($rs = F_db_query($sqls, $db)) {
            while ($ms = F_db_fetch_array($rs)) {
                $subject_id = $ms['subject_id'];
                $subject_name = $ms['subject_name'];
                $subject_description = F_decode_tcecode($ms['subject_description']);
                //$subject_enabled = F_getBoolean($ms['subject_enabled']);

                // --- start page data ---

                $pdf->AddPage();

                // set barcode
                $pdf->setBarcode($subject_id);

                $pdf->SetFillColor(204, 204, 204);
                $pdf->SetLineWidth(0.1);
                $pdf->SetDrawColor(0, 0, 0);

                // print document name (title)
                $pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA * K_TITLE_MAGNIFICATION);
                $pdf->Cell(0, $main_cell_height * K_TITLE_MAGNIFICATION, $doc_title, 1, 1, 'C', 1);

                $pdf->Ln(5);

                // --- display subject info ---
                $pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA * HEAD_MAGNIFICATION);
                $pdf->Cell($page_width, $data_cell_height * HEAD_MAGNIFICATION, ''.$module_name.' :: '.$subject_name.'', 1, 1, $defalign, 1);
                $pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
                $pdf->writeHTMLCell(0, $data_cell_height, PDF_MARGIN_LEFT, $pdf->GetY(), $subject_description, 1, 1);
                // --- end subject info ---

                $pdf->Ln(5);

                // ---- questions
                $sqlq = 'SELECT *
					FROM '.K_TABLE_QUESTIONS.'
					WHERE question_subject_id='.$subject_id.'
					ORDER BY question_enabled DESC, question_position, question_description';
                if ($rq = F_db_query($sqlq, $db)) {
                    $pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
                    $itemcount = 1;
                    while ($mq = F_db_fetch_array($rq)) {
                        $question_disabled = 0;
                        if (!F_getBoolean($mq['question_enabled'])) {
                            $question_disabled = 1;
                        }
                        $pdf->Cell($data_cell_width_third, $data_cell_height, $itemcount, 1, 0, 'R', $question_disabled);

                        $pdf->Cell($data_cell_width_third/2, $data_cell_height, $qtype[($mq['question_type']-1)], 1, 0, 'C', $question_disabled);
                        $pdf->Cell($data_cell_width_third/2, $data_cell_height, $mq['question_difficulty'], 1, 0, 'R', $question_disabled);

                        if ($mq['question_position'] <= 0) {
                            $mq['question_position'] = '';
                        }
                        $pdf->Cell($data_cell_width_third, $data_cell_height, $mq['question_position'], 1, 0, 'R', $question_disabled);
                        if (F_getBoolean($mq['question_fullscreen'])) {
                            $mq['question_fullscreen'] = 'F';
                        } else {
                            $mq['question_fullscreen'] = '';
                        }
                        $pdf->Cell($data_cell_width_third/2, $data_cell_height, $mq['question_fullscreen'], 1, 0, 'C', $question_disabled);
                        if (F_getBoolean($mq['question_inline_answers'])) {
                            $mq['question_inline_answers'] = 'I';
                        } else {
                            $mq['question_inline_answers'] = '';
                        }
                        $pdf->Cell($data_cell_width_third/2, $data_cell_height, $mq['question_inline_answers'], 1, 0, 'C', $question_disabled);
                        if (F_getBoolean($mq['question_auto_next'])) {
                            $mq['question_auto_next'] = 'A';
                        } else {
                            $mq['question_auto_next'] = '';
                        }
                        $pdf->Cell($data_cell_width_third/2, $data_cell_height, $mq['question_auto_next'], 1, 0, 'C', $question_disabled);
                        if ($mq['question_timer'] <= 0) {
                            $mq['question_timer'] = '';
                        }
                        $pdf->Cell($data_cell_width_third, $data_cell_height, $mq['question_timer'], 1, 0, 'R', $question_disabled);

                        $pdf->Ln();
                        $pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + $data_cell_width_third), $pdf->GetY(), F_decode_tcecode($mq['question_description']), 1, 1, '', '');
                        if (K_ENABLE_QUESTION_EXPLANATION and !empty($mq['question_explanation'])) {
                            $pdf->Cell($data_cell_width_third, $data_cell_height, '', 0, 0, 'C', 0);
                            $pdf->SetFont('', 'BIU');
                            $pdf->Cell(0, $data_cell_height, $l['w_explanation'], 'LTR', 1, '', 0, '', 0);
                            $pdf->SetFont('', '');
                            $pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + $data_cell_width_third), $pdf->GetY(), F_decode_tcecode($mq['question_explanation']), 'LRB', 1, '', '');
                        }

                        if ($show_answers) {
                            // display alternative answers
                            $sqla = 'SELECT *
								FROM '.K_TABLE_ANSWERS.'
								WHERE answer_question_id=\''.$mq['question_id'].'\'
								ORDER BY answer_position,answer_isright DESC';
                            if ($ra = F_db_query($sqla, $db)) {
                                $idx = 0; // count items
                                while ($ma = F_db_fetch_array($ra)) {
                                    $idx++;
                                    $answer_disabled = intval(!F_getBoolean($ma['answer_enabled']));
                                    $answer_isright = intval(F_getBoolean($ma['answer_isright']));

                                    $pdf->Cell($data_cell_width_third, $data_cell_height, '', 0, 0, 'C', 0);
                                    $pdf->Cell($data_cell_width_third, $data_cell_height, $idx, 1, 0, 'C', $answer_disabled);

                                    if ($mq['question_type'] != 4) {
                                        $pdf->Cell($data_cell_width_third/2, $data_cell_height, $qright[$answer_isright], 1, 0, 'C', $answer_disabled);
                                    } else {
                                        $pdf->Cell($data_cell_width_third/2, $data_cell_height, '', 1, 0, 'C', $answer_disabled);
                                    }
                                    if ($ma['answer_position'] > 0) {
                                        $pdf->Cell($data_cell_width_third, $data_cell_height, $ma['answer_position'], 1, 0, 'C', $answer_disabled);
                                    } else {
                                        $pdf->Cell($data_cell_width_third, $data_cell_height, '', 1, 0, 'C', $answer_disabled);
                                    }
                                    if ($ma['answer_keyboard_key'] > 0) {
                                        $pdf->Cell($data_cell_width_third/2, $data_cell_height, F_text_to_xml(chr($ma['answer_keyboard_key'])), 1, 0, 'C', $answer_disabled);
                                    } else {
                                        $pdf->Cell($data_cell_width_third/2, $data_cell_height, '', 1, 0, 'C', $answer_disabled);
                                    }
                                    $pdf->Ln();
                                    $pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + (2 * $data_cell_width_third)), $pdf->GetY(), F_decode_tcecode($ma['answer_description']), 1, 1, '', '', '');
                                    if (K_ENABLE_ANSWER_EXPLANATION and !empty($ma['answer_explanation'])) {
                                        $pdf->Cell((2 * $data_cell_width_third), $data_cell_height, '', 0, 0, 'C', 0);
                                        $pdf->SetFont('', 'BIU');
                                        $pdf->Cell(0, $data_cell_height, $l['w_explanation'], 'LTR', 1, '', 0, '', 0);
                                        $pdf->SetFont('', '');
                                        $pdf->writeHTMLCell(0, $data_cell_height, (PDF_MARGIN_LEFT + (2 * $data_cell_width_third)), $pdf->GetY(), F_decode_tcecode($ma['answer_explanation']), 'LRB', 1, '', '');
                                    }
                                }
                            } else {
                                F_display_db_error();
                            }
                        } // end $show_answers

                        $pdf->Ln($data_cell_height);
                        $itemcount++;
                    } // end while for questions
                } else {
                    F_display_db_error();
                }
            } // end while for topics
        } else {
            F_display_db_error();
        }
    } // end while for module
} else {
    F_display_db_error();
}

$pdf->lastpage(true);
$pdf->SetAutoPageBreak(false);
$pdf->SetFont('helvetica', '', 5);
$pdf->SetTextColor(0, 127, 255);
$msg = "\x50\x6f\x77\x65\x72\x65\x64\x20\x62\x79\x20\x54\x43\x45\x78\x61\x6d\x20\x28\x77\x77\x77\x2e\x74\x63\x65\x78\x61\x6d\x2e\x6f\x72\x67\x29";
$lnk = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x74\x63\x65\x78\x61\x6d\x2e\x6f\x72\x67";
$pdf->SetXY(15, $pdf->getPageHeight(), true);
$pdf->Cell(0, 0, $msg, 0, 0, 'R', 0, $lnk, 0, false, 'B', 'B');

// set PDF file name
switch ($expmode) {
    case 1: {
        $pdf_filename = 'tcexam_subject_'.$subject_id.'_'.date('YmdHi').'.pdf';
        break;
    }
    case 2: {
        $pdf_filename = 'tcexam_module_'.$module_id.'_'.date('YmdHi').'.pdf';
        break;
    }
    case 3: {
        $pdf_filename = 'tcexam_all_modules_'.date('YmdHi').'.pdf';
        break;
    }
    default: {
        $pdf_filename = 'tcexam_export_'.date('YmdHi').'.pdf';
        break;
    }
}

// Send PDF output
$pdf->Output($pdf_filename, 'D');

//============================================================+
// END OF FILE
//============================================================+
