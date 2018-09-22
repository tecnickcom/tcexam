<?php
//============================================================+
// File name   : tce_pdf_results.php
// Begin       : 2004-06-10
// Last Update : 2017-04-22
//
// Description : Create PDF document to display test results
//               summary for all users.
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
//    Copyright (C) 2004-2018 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Create PDF document to display users' tests results.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-06-11
 * @param $_REQUEST['mode'] (int) document mode: 1=all users results, 3=detailed report for single user; 4=detailed report for all users; 5=detailed report for all users with only TEXT questions.
 * @param $_REQUEST['test_id'] (int) test ID
 * @param $_REQUEST['user_id'] (int) user ID
 * @param $_REQUEST['group_id'] (int) group ID
 * @param $_REQUEST['testuser_id'] (int) test user ID
 * @param $_REQUEST['order_field'] (string) ORDER BY portion of SQL selection query
 * @param $_REQUEST['orderdir'] (int) Ordering direction.
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
require_once('../../shared/code/tce_functions_statistics.php');
require_once('tce_functions_user_select.php');

if (isset($_REQUEST['mode']) and ($_REQUEST['mode'] > 0)) {
    $mode = intval($_REQUEST['mode']);
} else {
    $mode = 0;
}
$onlytext = ($mode == 5);
if (isset($_REQUEST['email']) and !checkPassword(date('Y').$testuser_id.K_RANDOM_SECURITY.$test_id.date('m').$user_id, $_REQUEST['email'])) {
    F_print_error('ERROR', $l['m_authorization_denied']);
    exit;
}
$filter = 'sel=1';
if (isset($_REQUEST['test_id']) and ($_REQUEST['test_id'] > 0)) {
    $test_id = intval($_REQUEST['test_id']);
    if (!isset($_REQUEST['email'])) {
        if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
            exit;
        }
    }
    $filter .= '&amp;test_id='.$test_id.'';
} else {
    $test_id = 0;
}
if (isset($_REQUEST['group_id']) and ($_REQUEST['group_id'] > 0)) {
    $group_id = intval($_REQUEST['group_id']);
    $filter .= '&amp;group_id='.$group_id.'';
} else {
    $group_id = 0;
}
if (isset($_REQUEST['user_id']) and ($_REQUEST['user_id'] > 1)) {
    $user_id = intval($_REQUEST['user_id']);
    $filter .= '&amp;user_id='.$user_id;
} else {
    $user_id = 0;
}
if (isset($_REQUEST['testuser_id']) and ($_REQUEST['testuser_id'] > 1)) {
    $testuser_id = intval($_REQUEST['testuser_id']);
    $filter .= '&amp;testuser_id='.$testuser_id.'';
} else {
    $testuser_id = 0;
}
if (isset($_REQUEST['startdate'])) {
    $startdate = $_REQUEST['startdate'];
    $startdate_time = strtotime($startdate);
    $startdate = date(K_TIMESTAMP_FORMAT, $startdate_time);
    $filter .= '&amp;startdate='.urlencode($startdate);
} else {
    $startdate = '';
}
if (isset($_REQUEST['enddate'])) {
    $enddate = $_REQUEST['enddate'];
    $enddate_time = strtotime($enddate);
    $enddate = date(K_TIMESTAMP_FORMAT, $enddate_time);
    $filter .= '&amp;enddate='.urlencode($enddate).'';
} else {
    $enddate = '';
}
if (isset($_REQUEST['display_mode'])) {
    $display_mode = max(0, min(5, intval($_REQUEST['display_mode'])));
    $filter .= '&amp;display_mode='.$display_mode;
} else {
    $display_mode = 0;
}

if (isset($_REQUEST['show_graph'])) {
    $show_graph = intval($_REQUEST['show_graph']);
    $filter .= '&amp;show_graph='.$show_graph;
    if ($show_graph and ($display_mode == 0)) {
        $display_mode = 1;
    }
} else {
    $show_graph = 0;
}

if (isset($_REQUEST['order_field']) and !empty($_REQUEST['order_field']) and (in_array($_REQUEST['order_field'], array('testuser_creation_time', 'testuser_end_time', 'user_name', 'user_lastname', 'user_firstname', 'total_score', 'testuser_test_id')))) {
    $order_field = $_REQUEST['order_field'];
} else {
    $order_field = 'total_score, user_lastname, user_firstname';
}
$filter .= '&amp;order_field='.urlencode($order_field).'';
if (!isset($_REQUEST['orderdir']) or empty($_REQUEST['orderdir'])) {
    $orderdir = 0;
    $nextorderdir = 1;
    $full_order_field = $order_field;
} else {
    $orderdir = 1;
    $nextorderdir = 0;
    $full_order_field = $order_field.' DESC';
}
$filter .= '&amp;orderdir='.$orderdir.'';

$pubmode = false;

// get the data to print
$ts = F_getAllUsersTestStat($test_id, $group_id, $user_id, $startdate, $enddate, $full_order_field, $pubmode, $display_mode);

if (empty($ts['num_records'])) {
    return;
}

switch ($mode) {
    case 1: {
        // all users results
        $doc_title = unhtmlentities($l['t_result_all_users']);
        $doc_description = F_compact_string(unhtmlentities($l['hp_result_alluser']));
        break;
    }
    case 3: // detailed report for specific user
    case 4: // detailed report for all users
    case 5: { // detailed report for all users with only open questions
        $doc_title = unhtmlentities($l['t_result_user']);
        $doc_description = F_compact_string(unhtmlentities($l['hp_result_user']));
        break;
    }
    default: {
        echo $l['m_authorization_denied'];
        exit;
    }
}

// --- create pdf document

$isunicode = (strcasecmp($l['a_meta_charset'], 'UTF-8') == 0);
//create new PDF document (document units are set by default to millimeters)
$pdf = new TCPDFEX(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, $isunicode);

// Set backlink QR-Code
if ($pubmode) {
    $pdf->setTCExamBackLink(K_PATH_URL.'public/code/tce_test_allresults.php?'.$filter);
} else {
    $pdf->setTCExamBackLink(K_PATH_URL.'admin/code/tce_show_result_allusers.php?'.$filter);
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
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->setLanguageArray($l); //set language items

if (defined('K_DIGSIG_ENABLE') and K_DIGSIG_ENABLE) {
    // set document signature
    $pdf->setSignature(K_DIGSIG_CERTIFICATE, K_DIGSIG_PRIVATE_KEY, K_DIGSIG_PASSWORD, K_DIGSIG_EXTRA_CERTS, K_DIGSIG_CERT_TYPE, array('Name'=>K_DIGSIG_NAME, 'Location'=>K_DIGSIG_LOCATION, 'Reason'=>K_DIGSIG_REASON, 'ContactInfo'=>K_DIGSIG_CONTACT));
}

$pdf->SetFillColor(204, 204, 204);
$pdf->SetLineWidth(0.1);
$pdf->SetDrawColor(0, 0, 0);

if ($mode != 3) {
    $pdf->AddPage();
    // print document name (title)
    $pdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA * K_TITLE_MAGNIFICATION);
    $pdf->Cell(0, 0, $doc_title, 1, 1, 'C', 1);
    $pdf->Ln(5);
    // print test stats table
    $pdf->printTestResultStat($ts, $pubmode, $display_mode);

    if ($show_graph) {
        // display graph
        $pdf->Ln(5);
        $pdf->printSVGStatsGraph($ts['svgpoints']);
    }
    if ($display_mode > 1) {
        // print question
        $pdf->Bookmark($l['w_statistics']);
        $pdf->printQuestionStats($ts['qstats'], $display_mode);
    }
}

if ($mode > 2) {
    // print testuser details
    if ($testuser_id == 0) {
        foreach ($ts['testuser'] as $tstusr) {
            $pdf->AddPage();
            $pdf->printTestUserInfo($tstusr, $onlytext, $pubmode);
        }
    } else {
        $pdf->AddPage();
        $pdf->printTestUserInfo($ts['testuser']['\''.$testuser_id.'\''], $onlytext, $pubmode);
    }
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
$pdf_filename = 'tcexam_report';
$pdf_filename .= empty($startdate) ? '' : '_'.date('YmdHis', $startdate_time);
$pdf_filename .= empty($enddate) ? '' : '_'.date('YmdHis', $enddate_time);
$pdf_filename .= '_'.$mode;
$pdf_filename .= '_'.$display_mode;
$pdf_filename .= '_'.$test_id;
$pdf_filename .= '_'.$group_id;
$pdf_filename .= '_'.$user_id;
$pdf_filename .= '_'.$testuser_id;
$pdf_filename .= '.pdf';

// Send PDF output
$pdf->Output($pdf_filename, 'D');

//============================================================+
// END OF FILE
//============================================================+
