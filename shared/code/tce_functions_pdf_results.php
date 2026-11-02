<?php

//============================================================+
// File name   : tce_functions_pdf_results.php
// Begin       : 2026-11-02
//
// Description : Build the PDF document of test results.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Function to build the PDF document of test results.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2026-11-02
 */

/**
 * Build the PDF document of test results.
 * The caller is responsible for authorizing the requested test, group and user.
 * @param $mode (int) document mode: 1=all users results, 3=detailed report for single user; 4=detailed report for all users; 5=detailed report for all users with only TEXT questions.
 * @param $test_id (int) test ID (0 = all authorized tests).
 * @param $group_id (int) group ID (0 = all groups).
 * @param $user_id (int) user ID (0 = all users).
 * @param $testuser_id (int) test-user ID (0 = all test-users).
 * @param $startdate (string) start date filter, empty for none.
 * @param $enddate (string) end date filter, empty for none.
 * @param $full_order_field (string) ORDER BY portion of the SQL selection query.
 * @param $display_mode (int) display mode: 0 = disabled; 1 = minimum; 2 = module; 3 = subject; 4 = question; 5 = answer.
 * @param $show_graph (int) if not zero, display the score graph.
 * @param $pubmode (boolean) if true, filter the results for the public interface.
 * @param $filter (string) URL query string used for the back-link QR-Code.
 * @return TcePdfReport|null PDF document, or null when the mode is invalid or there are no results.
 */
function F_getResultsPDF(
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
    $pubmode,
    $filter,
) {
    global $l;
    require_once '../config/tce_config.php';
    require_once '../../shared/code/tce_functions_tcecode.php';
    require_once '../../shared/code/tce_functions_test.php';
    require_once '../../shared/code/tce_functions_test_stats.php';
    require_once '../../shared/config/tce_pdf.php';
    require_once '../../shared/code/tce_pdf_report.php';
    require_once '../../shared/code/tce_functions_statistics.php';

    $mode = (int) $mode;
    $testuser_id = (int) $testuser_id;
    switch ($mode) {
        case 1:
            // all users results
            $doc_title = unhtmlentities($l['t_result_all_users']);
            $doc_description = F_compact_string(unhtmlentities($l['hp_result_alluser']));
            break;
        case 3: // detailed report for specific user
        case 4: // detailed report for all users
        case 5: // detailed report for all users with only open questions
            $doc_title = unhtmlentities($l['t_result_user']);
            $doc_description = F_compact_string(unhtmlentities($l['hp_result_user']));
            break;
        default:
            return null;
    }

    $onlytext = $mode === 5;

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
        return null;
    }

    $pdf = new TcePdfReport();

    // header back-link QR-Code
    if ($pubmode) {
        $pdf->setTCExamBackLink(K_PATH_URL . 'public/code/tce_test_allresults.php?' . $filter);
    } else {
        $pdf->setTCExamBackLink(
            (defined('K_PATH_URL_ADMIN') ? K_PATH_URL_ADMIN : K_PATH_URL)
            . 'admin/code/tce_show_result_allusers.php?'
            . $filter,
        );
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

    if ($mode !== 3) {
        $pdf->addReportPage();
        $pdf->writeReportHTML(
            '<h1 style="text-align:center;font-size:13pt;">' . htmlspecialchars((string) $doc_title) . '</h1>',
        );
        $pdf->printTestResultStat($ts, $pubmode, $display_mode);
        if ((int) $show_graph !== 0) {
            $pdf->printSVGStatsGraph($ts['svgpoints']);
        }
        if ($display_mode > 1) {
            $pdf->setBookmark($l['w_statistics']);
            $pdf->printQuestionStats($ts['qstats'], $display_mode);
        }
    }

    if ($mode > 2) {
        // detailed report per test user (respecting the per-test "report to users" flag in public mode)
        if ($testuser_id === 0) {
            foreach ($ts['testuser'] as $tstusr) {
                if (!$pubmode || F_getBoolean($tstusr['test']['test_report_to_users'])) {
                    $pdf->addReportPage();
                    $pdf->printTestUserInfo($tstusr, $onlytext);
                }
            }
        } elseif (isset($ts['testuser']["'" . $testuser_id . "'"])) {
            $tstusr = $ts['testuser']["'" . $testuser_id . "'"];
            if (!$pubmode || F_getBoolean($tstusr['test']['test_report_to_users'])) {
                $pdf->addReportPage();
                $pdf->printTestUserInfo($tstusr, $onlytext);
            }
        }
    }

    return $pdf;
}
