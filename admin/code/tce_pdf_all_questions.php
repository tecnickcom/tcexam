<?php
//============================================================+
// File name   : tce_pdf_all_questions.php
// Begin       : 2004-06-10
// Last Update : 2009-12-31
// 
// Description : Creates a PDF document containing exported questions.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Creates a PDF document containing exported questions.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2005-07-06
 * @param int $_REQUEST['subject_id'] topic ID
 * @param string $_REQUEST['order_field'] ORDER BY portion of SQL selection query
 */

/**
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_authorization.php');
require_once('../code/tce_functions_auth_sql.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/config/tce_pdf.php');
require_once('../../shared/code/tcpdf.php');

if ((isset($_REQUEST['expmode']) AND ($_REQUEST['expmode'] > 0))
	AND (isset($_REQUEST['module_id']) AND ($_REQUEST['module_id'] > 0))
	AND (isset($_REQUEST['subject_id']) AND ($_REQUEST['subject_id'] > 0))) {
	$expmode = intval($_REQUEST['expmode']);
	$module_id = intval($_REQUEST['module_id']);
	$subject_id = intval($_REQUEST['subject_id']);
} else {
	exit;
}

$show_answers = true;
if (isset($_REQUEST['hide_answers']) AND ($_REQUEST['hide_answers'] == 1)) {
	$show_answers = false;
}

$doc_title = unhtmlentities($l['t_questions_list']);
$doc_description = F_compact_string(unhtmlentities($l['hp_select_all_questions']));
$page_elements = 6;
$temp_order_field = 'question_description';

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
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, $isunicode); 

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
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 

$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->setLanguageArray($l); //set language items

// set default alignment for cells
$defalign = $l['a_meta_dir']=='rtl' ? 'R' : 'L';

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

// ---- module
$sqlm = 'SELECT * FROM '.K_TABLE_MODULES.'';
if ($expmode < 3) {
	$sqlm .= ' WHERE module_id='.$module_id.'';
}
$sqlm .= ' ORDER BY module_name';
if($rm = F_db_query($sqlm, $db)) {
	while($mm = F_db_fetch_array($rm)) {
		$module_id =  $mm['module_id'];
		$module_name = $mm['module_name'];
		//$module_enabled = F_getBoolean($mm['module_enabled']);
		
		// ---- topic
		$where_sqls = 'subject_module_id='.$module_id.'';
		if ($expmode < 2) {
			$where_sqls .= ' AND subject_id='.$subject_id.'';
		}
		$sqls = F_select_subjects_sql($where_sqls);
		if($rs = F_db_query($sqls, $db)) {
			while($ms = F_db_fetch_array($rs)) {
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
				if($rq = F_db_query($sqlq, $db)) {
					$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
					$itemcount = 1;
					while($mq = F_db_fetch_array($rq)) {
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
						if (K_ENABLE_QUESTION_EXPLANATION AND !empty($mq['question_explanation'])) {
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
							if($ra = F_db_query($sqla, $db)) {
								$idx = 0; // count items
								while($ma = F_db_fetch_array($ra)) {
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
									if ($ma['answer_position'] > 0 ) {
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
									if (K_ENABLE_ANSWER_EXPLANATION AND !empty($ma['answer_explanation'])) {
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

// Send PDF output
$pdf->Output('tcexam_questions_'.$subject_id.'_'.date('YmdHi').'.pdf', 'I');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
