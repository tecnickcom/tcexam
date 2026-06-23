<?php

//============================================================+
// File name   : tce_pdf_results.php
// Begin       : 2004-06-10
// Last Update : 2023-11-30
//
// Description : Create PDF document to display test results
//               summary for all users.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
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

// Use the generated tc-lib-pdf fonts for this document (set before the config defines the legacy default).
require_once __DIR__ . '/../../vendor/autoload.php';
define('K_PATH_FONTS', realpath(__DIR__ . '/../../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

require_once '../config/tce_config.php';
require_once '../../shared/code/tce_authorization.php';
require_once '../../shared/code/tce_functions_tcecode.php';
require_once '../../shared/code/tce_functions_test.php';
require_once '../../shared/code/tce_functions_test_stats.php';
require_once '../../shared/config/tce_pdf.php';
require_once '../../shared/code/tce_pdf_report.php';
require_once '../../shared/code/tce_functions_statistics.php';
require_once 'tce_functions_user_select.php';
$mode = isset($_REQUEST['mode']) && $_REQUEST['mode'] > 0 ? (int) $_REQUEST['mode'] : 0;

$test_id = $_REQUEST['test_id'] ?? 0;
$user_id = $_REQUEST['user_id'] ?? 0;
$group_id = $_REQUEST['group_id'] ?? 0;
$testuser_id = $_REQUEST['testuser_id'] ?? 0;
$order_field = $_REQUEST['order_field'] ?? '';
$orderdir = $_REQUEST['orderdir'] ?? 0;

$onlytext = $mode == 5;
// The ?email= token bypasses the normal authorization check, so it must fail closed when
// K_RANDOM_SECURITY is still the shipped default (otherwise the token is publicly forgeable).
if (
    isset($_REQUEST['email'])
    && (
        !F_isRandomSecurityConfigured()
        || !checkPassword(
            date('Y') . $testuser_id . K_RANDOM_SECURITY . $test_id . date('m') . $user_id,
            $_REQUEST['email'],
        )
    )
) {
    F_print_error('ERROR', $l['m_authorization_denied'], true);
}

$filter = 'sel=1';
if (isset($_REQUEST['test_id']) && $_REQUEST['test_id'] > 0) {
    $test_id = (int) $_REQUEST['test_id'];
    if (!isset($_REQUEST['email']) && !F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
        exit();
    }

    $filter .= '&amp;test_id=' . $test_id . '';
} else {
    $test_id = 0;
}

if (isset($_REQUEST['group_id']) && $_REQUEST['group_id'] > 0) {
    $group_id = (int) $_REQUEST['group_id'];
    $filter .= '&amp;group_id=' . $group_id . '';
} else {
    $group_id = 0;
}

if (isset($_REQUEST['user_id']) && $_REQUEST['user_id'] > 1) {
    $user_id = (int) $_REQUEST['user_id'];
    $filter .= '&amp;user_id=' . $user_id;
} else {
    $user_id = 0;
}

if (isset($_REQUEST['testuser_id']) && $_REQUEST['testuser_id'] > 1) {
    $testuser_id = (int) $_REQUEST['testuser_id'];
    $filter .= '&amp;testuser_id=' . $testuser_id . '';
} else {
    $testuser_id = 0;
}

if (isset($_REQUEST['startdate'])) {
    $startdate = $_REQUEST['startdate'];
    $startdate_time = strtotime($startdate);
    $startdate = date(K_TIMESTAMP_FORMAT, $startdate_time);
    $filter .= '&amp;startdate=' . urlencode($startdate);
} else {
    $startdate = '';
}

if (isset($_REQUEST['enddate'])) {
    $enddate = $_REQUEST['enddate'];
    $enddate_time = strtotime($enddate);
    $enddate = date(K_TIMESTAMP_FORMAT, $enddate_time);
    $filter .= '&amp;enddate=' . urlencode($enddate) . '';
} else {
    $enddate = '';
}

if (isset($_REQUEST['display_mode'])) {
    $display_mode = max(0, min(5, (int) $_REQUEST['display_mode']));
    $filter .= '&amp;display_mode=' . $display_mode;
} else {
    $display_mode = 1;
}

if (isset($_REQUEST['show_graph'])) {
    $show_graph = (int) $_REQUEST['show_graph'];
    $filter .= '&amp;show_graph=' . $show_graph;
    if ($show_graph && $display_mode == 0) {
        $display_mode = 1;
    }
} else {
    $show_graph = 0;
}

if (
    isset($_REQUEST['order_field'])
    && !empty($_REQUEST['order_field'])
    && in_array($_REQUEST['order_field'], [
        'testuser_creation_time',
        'testuser_end_time',
        'user_name',
        'user_lastname',
        'user_firstname',
        'total_score',
        'testuser_test_id',
    ])
) {
    $order_field = $_REQUEST['order_field'];
} else {
    $order_field = 'total_score, user_lastname, user_firstname';
}

$filter .= '&amp;order_field=' . urlencode($order_field) . '';
if (!isset($_REQUEST['orderdir']) || empty($_REQUEST['orderdir'])) {
    $orderdir = 0;
    $nextorderdir = 1;
    $full_order_field = $order_field;
} else {
    $orderdir = 1;
    $nextorderdir = 0;
    $full_order_field = $order_field . ' DESC';
}

$filter .= '&amp;orderdir=' . $orderdir . '';

$pubmode = false;

// get the data to print
$ts = F_getAllUsersTestStat(
    $test_id,
    $group_id,
    $user_id,
    $startdate,
    $enddate,
    $full_order_field,
    $pubmode,
    $display_mode,
);

if (empty($ts['num_records'])) {
    return;
}

switch ($mode) {
    case 1:
        {
            // all users results
            $doc_title = unhtmlentities($l['t_result_all_users']);
            $doc_description = F_compact_string(unhtmlentities($l['hp_result_alluser']));
            break;
        }
    case 3: // detailed report for specific user
    case 4: // detailed report for all users
    case 5:
        { // detailed report for all users with only open questions
            $doc_title = unhtmlentities($l['t_result_user']);
            $doc_description = F_compact_string(unhtmlentities($l['hp_result_user']));
            break;
        }
    default:
        {
            echo $l['m_authorization_denied'];
            exit();
        }
}

// --- create the PDF document (tc-lib-pdf) ---

$pdf = new TcePdfReport();

// header back-link QR-Code
if ($pubmode) {
    $pdf->setTCExamBackLink(K_PATH_URL . 'public/code/tce_test_allresults.php?' . $filter);
} else {
    $pdf->setTCExamBackLink(K_PATH_URL . 'admin/code/tce_show_result_allusers.php?' . $filter);
}

// document metadata
$pdf->setCreator('TCExam ver.' . K_TCEXAM_VERSION);
$pdf->setAuthor(PDF_AUTHOR);
$pdf->setTitle((string) $doc_title);
$pdf->setSubject((string) $doc_description);
$pdf->setKeywords('TCExam, ' . $doc_title);
$pdf->setLanguageArray($l);

// page header content (title, description, logo)
$pdf->setReportHeader(PDF_HEADER_TITLE, PDF_HEADER_STRING, PDF_HEADER_LOGO, (float) PDF_HEADER_LOGO_WIDTH);

if ($mode != 3) {
    $pdf->addReportPage();
    $pdf->writeReportHTML(
        '<h1 style="text-align:center;font-size:13pt;">' . htmlspecialchars((string) $doc_title) . '</h1>',
    );
    $pdf->printTestResultStat($ts, $pubmode, $display_mode);
    if ($show_graph !== 0) {
        $pdf->printSVGStatsGraph($ts['svgpoints']);
    }
    if ($display_mode > 1) {
        $pdf->setBookmark($l['w_statistics']);
        $pdf->printQuestionStats($ts['qstats'], $display_mode);
    }
}

if ($mode > 2) {
    // detailed report per test user
    if ($testuser_id === 0) {
        foreach ($ts['testuser'] as $tstusr) {
            $pdf->addReportPage();
            $pdf->printTestUserInfo($tstusr, $onlytext);
        }
    } else {
        $pdf->addReportPage();
        $pdf->printTestUserInfo($ts['testuser']["'" . $testuser_id . "'"], $onlytext);
    }
}

// build the download file name
$pdf_filename = 'tcexam_report';
$pdf_filename .= $startdate === '' ? '' : '_' . date('YmdHis', $startdate_time);
$pdf_filename .= $enddate === '' ? '' : '_' . date('YmdHis', $enddate_time);
$pdf_filename .=
    '_' . $mode . '_' . $display_mode . '_' . $test_id . '_' . $group_id . '_' . $user_id . '_' . $testuser_id . '.pdf';

$pdf->outputReport($pdf_filename);
