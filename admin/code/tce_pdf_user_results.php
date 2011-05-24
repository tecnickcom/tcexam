<?php
//============================================================+
// File name   : tce_pdf_user_results.php
// Begin       : 2008-12-26
// Last Update : 2011-05-24
//
// Description : Create PDF document to display user's results.
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
 * Create PDF document to display user's results.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2008-12-26
 * @param $_REQUEST['mode'] (int) document mode: 1=all users results, 2=questions stats, 3=detailed report for single user; 4=detailed report for all users; 5=detailed report for all users with only TEXT questions.
 * @param $_REQUEST['user_id'] (int) user ID
 * @param $_REQUEST['startdate'] (int) start date
 * @param $_REQUEST['enddate'] (int) end date
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
require_once('../code/tce_functions_statistics.php');
require_once('tce_functions_user_select.php');

if (isset($_REQUEST['user_id']) AND ($_REQUEST['user_id'] > 1)) {
	$user_id = intval($_REQUEST['user_id']);
	if (!F_isAuthorizedEditorForUser($user_id)) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		exit;
	}
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

$numberfont = 'courier';

$doc_title = unhtmlentities($l['t_all_results_user']);
$doc_description = F_compact_string(unhtmlentities($l['hp_allresults_user']));
$page_elements = 9;
$temp_order_field = 'total_score, user_lastname, user_firstname';

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
//$pdf->setTCExamBackLink(K_PATH_URL.'admin/code/tce_pdf_user_results.php?user_id='.$user_id.'&startdate='.urlencode($startdate).'&enddate='.urlencode($enddate).'&orderfield='.urlencode($order_field));
$pdf->setTCExamBackLink(K_PATH_URL.'admin/code/tce_pdf_user_results.php?user_id='.$user_id);

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

// ------------------------------------------------------------
// --- start page data ---

$pdf->AddPage();

// set barcode
$pdf->setBarcode($user_id.':'.date('YmdHis'));

$pdf->SetFillColor(204, 204, 204);
$pdf->SetLineWidth(0.1);
$pdf->SetDrawColor(0, 0, 0);

// print document name (title)
$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA * K_TITLE_MAGNIFICATION);
$pdf->Cell(0, $main_cell_height * K_TITLE_MAGNIFICATION, $doc_title, 1, 1, 'C', 1);

$pdf->Ln(5);

// display user info

// calculate some sizes
$user_elements = 5;
$user_data_cell_width = round($page_width / $user_elements, 2);

// print table headings
$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);

$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_lastname'], 1, 0, 'C', true, '', 1);
$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_firstname'], 1, 0, 'C', true, '', 1);
$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_user'], 1, 0, 'C', true, '', 1);
$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_time_begin'], 1, 0, 'C', true, '', 1);
$pdf->Cell($user_data_cell_width, $data_cell_height, $l['w_time_end'], 1, 1, 'C', true, '', 1);

$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

$sqlu = 'SELECT user_lastname, user_firstname, user_name
	FROM '.K_TABLE_USERS.'
	WHERE user_id='.$user_id.'';
if($ru = F_db_query($sqlu, $db)) {
	if($mu = F_db_fetch_array($ru)) {
		$pdf->Cell($user_data_cell_width, $data_cell_height, $mu['user_lastname'], 1, 0, 'C', false, '', 1);
		$pdf->Cell($user_data_cell_width, $data_cell_height, $mu['user_firstname'], 1, 0, 'C', false, '', 1);
		$pdf->Cell($user_data_cell_width, $data_cell_height, $mu['user_name'], 1, 0, 'C', false, '', 1);
		$pdf->Cell($user_data_cell_width, $data_cell_height, $startdate, 1, 0, 'C', 0);
		$pdf->Cell($user_data_cell_width, $data_cell_height, $enddate, 1, 1, 'C', 0);
	}
} else {
	F_display_db_error();
}

$pdf->Ln(5);

// print table headings
$pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
$pdf->Cell($data_cell_width_third, $data_cell_height, '#', 1, 0, 'C', true, '', 1);
$pdf->Cell((4 * $data_cell_width_third), $data_cell_height, $l['w_time_begin'], 1, 0, 'C', true, '', 1);
$pdf->Cell((2 * $data_cell_width_third), $data_cell_height, $l['w_time'], 1, 0, 'C', true, '', 1);
$pdf->Cell((8 * $data_cell_width_third), $data_cell_height, $l['w_test'], 1, 0, 'C', true, '', 1);
$pdf->Cell(3 * $data_cell_width_third, $data_cell_height, $l['w_score'], 1, 0, 'C', true, '', 1);
$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $l['w_answers_right_th'], 1, 0, 'C', true, '', 1);
$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $l['w_answers_wrong_th'], 1, 0, 'C', true, '', 1);
$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $l['w_questions_unanswered_th'], 1, 0, 'C', true, '', 1);
$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $l['w_questions_undisplayed_th'], 1, 1, 'C', true, '', 1);

// print table rows

$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

$statsdata = array();
$statsdata['score'] = array();
$statsdata['right'] = array();
$statsdata['wrong'] = array();
$statsdata['unanswered'] = array();
$statsdata['undisplayed'] = array();
$statsdata['unrated'] = array();

$sqlr = 'SELECT
	testuser_id,
	test_id,
	test_name,
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
$sqlr .= ' GROUP BY testuser_id, test_id, test_name, testuser_creation_time, testuser_status ORDER BY '.$order_field.'';
if($rr = F_db_query($sqlr, $db)) {
	$itemcount = 0;
	$passed = 0;
	$pdf->SetFillColor(128, 255, 128);
	while($mr = F_db_fetch_array($rr)) {
		$itemcount++;
		$usrtestdata = F_getUserTestStat($mr['test_id'], $user_id);
		$halfscore = ($usrtestdata['max_score'] / 2);
		$pdf->SetFont($numberfont, '', 6);
		$pfill = 0;
		if ($usrtestdata['score_threshold'] > 0) {
			if ($usrtestdata['score'] >= $usrtestdata['score_threshold']) {
				$pfill = 1;
				$passed++;
			} else {
				$pfill = 0;
			}
		} elseif ($usrtestdata['score'] > $halfscore) {
			$passed++;
		}
		$pdf->SetFont($numberfont, '', 6);
		$pdf->Cell($data_cell_width_third, $data_cell_height, $itemcount, 1, 0, 'R', $pfill);
		$pdf->Cell((4 * $data_cell_width_third), $data_cell_height, $mr['testuser_creation_time'], 1, 0, 'R', 0);
		$time_diff = strtotime($mr['testuser_end_time']) - strtotime($mr['testuser_creation_time']); //sec
		$time_diff = gmdate('H:i:s', $time_diff);
		$pdf->Cell((2 * $data_cell_width_third), $data_cell_height, $time_diff, 1, 0, 'R', 0);
		$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
		$pdf->Cell((8 * $data_cell_width_third), $data_cell_height, $mr['test_name'], 1, 0, '', false, '', 1);
		$pdf->SetFont($numberfont, '', 6);
		$pdf->Cell(3 * $data_cell_width_third, $data_cell_height, F_formatFloat($mr['total_score']).' '.F_formatPdfPercentage($usrtestdata['score'] / $usrtestdata['max_score']), 1, 0, 'R', 0);
		$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $usrtestdata['right'].' '.F_formatPdfPercentage($usrtestdata['right'] / $usrtestdata['all']), 1, 0, 'R', 0);
		$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $usrtestdata['wrong'].' '.F_formatPdfPercentage($usrtestdata['wrong'] / $usrtestdata['all']), 1, 0, 'R', 0);
		$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $usrtestdata['unanswered'].' '.F_formatPdfPercentage($usrtestdata['unanswered'] / $usrtestdata['all']), 1, 0, 'R', 0);
		$pdf->Cell($data_cell_width * 3 / 4, $data_cell_height, $usrtestdata['undisplayed'].' '.F_formatPdfPercentage($usrtestdata['undisplayed'] / $usrtestdata['all']), 1, 1, 'R', 0);

		// collects data for descriptive statistics
		$statsdata['score'][] = $mr['total_score'] / $usrtestdata['max_score'];
		$statsdata['right'][] = $usrtestdata['right'] / $usrtestdata['all'];
		$statsdata['wrong'][] = $usrtestdata['wrong'] / $usrtestdata['all'];
		$statsdata['unanswered'][] = $usrtestdata['unanswered'] / $usrtestdata['all'];
		$statsdata['undisplayed'][] = $usrtestdata['undisplayed'] / $usrtestdata['all'];
		$statsdata['unrated'][] = $usrtestdata['unrated'] / $usrtestdata['all'];
	}

	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
	$pdf->Cell((15 * $data_cell_width_third), $data_cell_height, $l['w_passed'], 0, 0, 'R', 0);
	$pdf->SetFont($numberfont, 'B', 6);
	$pdf->Cell(3 * $data_cell_width_third, $data_cell_height, $passed.' '.F_formatPdfPercentage($passed / $itemcount), 1, 1, 'R', 0);

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
$pdf->Cell($page_width, $data_cell_height, $l['w_statistics'], 1, 1, 'C', true, '', 1);

// columns headers
$pdf->Cell(2 * $data_cell_width, $data_cell_height, '', 1, 0, 'C', 1);
$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $l['w_score'], 1, 0, 'C', true, '', 1);
$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $l['w_answers_right_th'], 1, 0, 'C', true, '', 1);
$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $l['w_answers_wrong_th'], 1, 0, 'C', true, '', 1);
$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $l['w_questions_unanswered_th'], 1, 0, 'C', true, '', 1);
$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $l['w_questions_undisplayed_th'], 1, 1, 'C', true, '', 1);

$pdf->SetFont($numberfont, '', 6);

foreach ($stats as $row => $columns) {
	if (!in_array($row, $excludestat)) {
		$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
		$pdf->Cell(2 * $data_cell_width, $data_cell_height, $l['w_'.$row], 1, 0, $dirlabel, 0);
		$pdf->SetFont($numberfont, '', 6);
		$cstr = F_formatFloat($columns['score']);
		if (in_array($row, $calcpercent)) {
			$cstr .= ' '.F_formatPdfPercentage($columns['score'] / $usrtestdata['max_score']);
		}
		$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $cstr, 1, 0, 'R', 0);
		$cstr = F_formatFloat($columns['right']);
		if (in_array($row, $calcpercent)) {
			$cstr .= ' '.F_formatPdfPercentage($columns['right'] / $usrtestdata['all']);
		}
		$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $cstr, 1, 0, 'R', 0);
		$cstr = F_formatFloat($columns['wrong']);
		if (in_array($row, $calcpercent)) {
			$cstr .= ' '.F_formatPdfPercentage($columns['wrong'] / $usrtestdata['all']);
		}
		$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $cstr, 1, 0, 'R', 0);
		$cstr = F_formatFloat($columns['unanswered']);
		if (in_array($row, $calcpercent)) {
			$cstr .= ' '.F_formatPdfPercentage($columns['unanswered'] / $usrtestdata['all']);
		}
		$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $cstr, 1, 0, 'R', 0);
		$cstr = F_formatFloat($columns['undisplayed']);
		if (in_array($row, $calcpercent)) {
			$cstr .= ' '.F_formatPdfPercentage($columns['undisplayed'] / $usrtestdata['all']);
		}
		$pdf->Cell($data_cell_width * 7 / 5, $data_cell_height, $cstr, 1, 1, 'R', 0);
	}
}

$pdf->lastpage(true);
$pdf->SetAutoPageBreak(false);
$pdf->SetFont('helvetica', '', 5);
$pdf->SetTextColor(0,127,255);
$msg = "\x50\x6f\x77\x65\x72\x65\x64\x20\x62\x79\x20\x54\x43\x45\x78\x61\x6d\x20\x28\x77\x77\x77\x2e\x74\x63\x65\x78\x61\x6d\x2e\x6f\x72\x67\x29";
$lnk = "\x68\x74\x74\x70\x3a\x2f\x2f\x77\x77\x77\x2e\x74\x63\x65\x78\x61\x6d\x2e\x6f\x72\x67";
$pdf->SetXY(15, $pdf->getPageHeight(), true);
$pdf->Cell(0, 0, $msg, 0, 0, 'R', 0, $lnk, 0, false, 'B', 'B');

// Send PDF output
$pdf->Output('tcexam_user_results_'.$user_id.'_'.date('YmdHis').'.pdf', 'D');

//============================================================+
// END OF FILE
//============================================================+
