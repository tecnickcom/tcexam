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
$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once '../../shared/code/tce_authorization.php';
require_once '../../shared/code/tce_functions_pdf_results.php';
$mode = isset($_REQUEST['mode']) && $_REQUEST['mode'] > 0 ? (int) $_REQUEST['mode'] : 0;

$filter = 'sel=1';
if (isset($_REQUEST['test_id']) && $_REQUEST['test_id'] > 0) {
    $test_id = (int) $_REQUEST['test_id'];
    if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
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

$pdf = F_getResultsPDF(
    $mode,
    $test_id,
    $group_id,
    $user_id,
    $testuser_id,
    $startdate,
    $enddate,
    $full_order_field,
    $display_mode,
    $show_graph,
    false,
    $filter,
);
if ($pdf === null) {
    if (!in_array($mode, [1, 3, 4, 5], true)) {
        echo $l['m_authorization_denied'];
    }

    exit();
}

// build the download file name
$pdf_filename = 'tcexam_report';
$pdf_filename .= $startdate === '' ? '' : '_' . date('YmdHis', $startdate_time);
$pdf_filename .= $enddate === '' ? '' : '_' . date('YmdHis', $enddate_time);
$pdf_filename .=
    '_' . $mode . '_' . $display_mode . '_' . $test_id . '_' . $group_id . '_' . $user_id . '_' . $testuser_id . '.pdf';

$pdf->outputReport($pdf_filename);
