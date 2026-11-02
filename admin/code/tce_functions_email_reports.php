<?php

//============================================================+
// File name   : tce_functions_email_reports.php
// Begin       : 2005-02-24
// Last Update : 2023-11-30
//
// Description : Sends email test reports to users.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Functions to send email reports to users.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2005-02-24
 */

/**
 * Sends email test reports to users.
 * @author Nicola Asuni
 * @since 2005-02-24
 * @param $test_id (int) TEST ID
 * @param $user_id (int) USER ID (0 means all users)
 * @param $testuser_id (int) test-user ID - if greater than zero, filter stats for the specified test-user.
 * @param $group_id (int) GROUP ID (0 means all groups)
 * @param $startdate (int) start date ID - if greater than zero, filter stats for the specified starting date
 * @param $enddate (int) end date ID - if greater than zero, filter stats for the specified ending date
 * @param $mode (int) type of report to send: 0=detailed report; 1=summary report (without questions)
 * @param $display_mode display (int) mode: 0 = disabled; 1 = minimum; 2 = module; 3 = subject; 4 = question; 5 = answer.
 * @param $show_graph (boolean) If true display the score graph.
 */
function F_send_report_emails(
    $test_id,
    $user_id = 0,
    $testuser_id = 0,
    $group_id = 0,
    $startdate = 0,
    $enddate = 0,
    $mode = 0,
    $display_mode = 1,
    $show_graph = false,
) {
    global $l, $db;
    require_once '../config/tce_config.php';
    require_once '../../shared/code/tce_functions_test.php';
    require_once '../../shared/code/tce_functions_test_stats.php';
    require_once '../../shared/code/tce_class_mailer.php';
    require_once 'tce_functions_user_select.php';

    $mode = (int) $mode;
    if ($test_id > 0) {
        $test_id = (int) $test_id;
        if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
            return;
        }
    } else {
        $test_id = 0;
    }

    $user_id = $user_id > 0 ? (int) $user_id : 0;

    $testuser_id = $testuser_id > 0 ? (int) $testuser_id : 0;

    $group_id = $group_id > 0 ? (int) $group_id : 0;

    if (!empty($startdate)) {
        $startdate_time = strtotime($startdate);
        $startdate = date(K_TIMESTAMP_FORMAT, $startdate_time);
    } else {
        $startdate = '';
    }

    if (!empty($enddate)) {
        $enddate_time = strtotime($enddate);
        $enddate = date(K_TIMESTAMP_FORMAT, $enddate_time);
    } else {
        $enddate = '';
    }

    // Instantiate C_mailer class
    $mail = new C_mailer();

    //Load default values
    $mail->setLanguageData($l);

    // $emailcfg is defined by the email config file included via tce_class_mailer.php
    $emailcfg = isset($emailcfg) && is_array($emailcfg) ? $emailcfg : [];
    $mail->setConfigData($emailcfg, (string) ($l['a_meta_charset'] ?? ''));

    $mail->Subject = (string) $l['t_result_user'];
    $mail->isHTML(true); // Set message type to HTML.

    $email_num = 0; // count emails;

    // get all data
    $data = F_getAllUsersTestStat(
        $test_id,
        $group_id,
        $user_id,
        $startdate,
        $enddate,
        'total_score',
        false,
        $display_mode,
    );

    require_once '../../shared/code/tce_functions_pdf_results.php';

    foreach ($data['testuser'] as $tu) {
        if (strlen($tu['user_email']) > 3) {
            // set HTML header
            $mail->Body = (string) ($emailcfg['MsgHeader'] ?? '');
            // compose alternate TEXT message
            $mail->AltBody = '' . $l['t_result_user'] . ' [' . $tu['testuser_creation_time'] . ']' . K_NEWLINE;
            $mail->AltBody .= $l['w_test'] . ': ' . $tu['test']['test_name'] . K_NEWLINE;

            $passmsg = '';
            if ($tu['test']['test_score_threshold'] > 0) {
                $mail->AltBody .= $l['w_test_score_threshold'] . ': ' . $tu['test']['test_score_threshold'];
                if ($tu['total_score'] >= $tu['test']['test_score_threshold']) {
                    $passmsg = ' - ' . $l['w_passed'];
                } else {
                    $passmsg = ' - ' . $l['w_not_passed'];
                }

                $mail->AltBody .= K_NEWLINE;
            }

            $mail->AltBody .=
                $l['w_score']
                . ': '
                . F_formatFloat($tu['total_score'])
                . ' '
                . F_formatPercentage($tu['total_score_perc'], false)
                . $passmsg
                . K_NEWLINE;
            if ($display_mode > 0) {
                $mail->AltBody .=
                    $l['w_answers_right']
                    . ': '
                    . $tu['right']
                    . '&nbsp;'
                    . F_formatPercentage($tu['right_perc'], false)
                    . K_NEWLINE;
                $mail->AltBody .=
                    $l['w_answers_wrong']
                    . ': '
                    . $tu['wrong']
                    . '&nbsp;'
                    . F_formatPercentage($tu['wrong_perc'], false)
                    . K_NEWLINE;
                $mail->AltBody .=
                    $l['w_questions_unanswered']
                    . ': '
                    . $tu['unanswered']
                    . '&nbsp;'
                    . F_formatPercentage($tu['unanswered_perc'], false)
                    . K_NEWLINE;
                $mail->AltBody .=
                    $l['w_questions_undisplayed']
                    . ': '
                    . $tu['undisplayed']
                    . '&nbsp;'
                    . F_formatPercentage($tu['undisplayed_perc'], false)
                    . K_NEWLINE;
            }

            if ($mode == 0) {
                // create the PDF document
                try {
                    $pdf = F_getResultsPDF(
                        3,
                        $tu['test']['test_id'],
                        0,
                        $tu['user_id'],
                        $tu['id'],
                        '',
                        '',
                        'total_score, user_lastname, user_firstname',
                        $display_mode,
                        (int) $show_graph,
                        false,
                        '',
                    );
                    $pdf_content = $pdf === null ? '' : $pdf->getOutPDFString();
                } catch (\Throwable $e) {
                    $pdf_content = '';
                }
                // set PDF document file name
                $doc_name = 'tcexam_report';
                $doc_name .= '_3';
                $doc_name .= '_0';
                $doc_name .= '_' . $tu['test']['test_id'];
                $doc_name .= '_0';
                $doc_name .= '_' . $tu['user_id'];
                $doc_name .= '_' . $tu['id'];
                $doc_name .= '.pdf';

                // attach document
                $mail->addStringAttachment(
                    $pdf_content,
                    $doc_name,
                    (string) ($emailcfg['AttachmentsEncoding'] ?? 'base64'),
                    'application/octet-stream',
                );
                $mail->AltBody .= K_NEWLINE . $l['w_attachment'] . ': ' . $doc_name . K_NEWLINE;
            }

            // convert alternate text to HTML
            $mail->Body .= str_replace(K_NEWLINE, '<br />' . K_NEWLINE, $mail->AltBody);

            // add HTML footer
            $mail->Body .= (string) ($emailcfg['MsgFooter'] ?? '');

            //--- Elaborate user Templates ---
            $mail->Body = str_replace('#CHARSET#', $l['a_meta_charset'], $mail->Body);
            $mail->Body = str_replace('#LANG#', $l['a_meta_language'], $mail->Body);
            $mail->Body = str_replace('#LANGDIR#', $l['a_meta_dir'], $mail->Body);
            $mail->Body = str_replace('#EMAIL#', $tu['user_email'], $mail->Body);
            $mail->Body = str_replace(
                '#USERNAME#',
                htmlspecialchars($tu['user_name'], ENT_NOQUOTES, $l['a_meta_charset']),
                $mail->Body,
            );
            $mail->Body = str_replace(
                '#USERFIRSTNAME#',
                htmlspecialchars($tu['user_firstname'] ?? '', ENT_NOQUOTES, $l['a_meta_charset']),
                $mail->Body,
            );
            $mail->Body = str_replace(
                '#USERLASTNAME#',
                htmlspecialchars($tu['user_lastname'] ?? '', ENT_NOQUOTES, $l['a_meta_charset']),
                $mail->Body,
            );

            // add a "To" address
            $mail->addAddress($tu['user_email'], $tu['user_name']);

            ++$email_num;
            $progresslog = '' . $email_num . '. ' . $tu['user_email'] . ' [' . $tu['user_name'] . ']'; //output user data

            if (!$mail->send()) { //send email to user
                $progresslog .= ' [' . $l['t_error'] . ']'; //display error message
            }

            $mail->clearAddresses(); // Clear all addresses for next loop
            $mail->clearAttachments(); // Clears all previously set filesystem, string, and binary attachments
        } else {
            $progresslog = '[' . $l['t_error'] . '] ' . $tu['user_name'] . ': ' . $l['m_unknown_email'] . ''; //output user data
        }

        echo $progresslog . '<br />' . K_NEWLINE; //output processed emails
        flush(); // force browser output
    }

    $mail->clearAddresses(); // Clear all addresses for next loop
    $mail->clearCustomHeaders(); // Clears all custom headers
    $mail->clearAllRecipients(); // Clears all recipients assigned in the TO, CC and BCC
    $mail->clearAttachments(); // Clears all previously set filesystem, string, and binary attachments
    $mail->clearReplyTos(); // Clears all recipients assigned in the ReplyTo array
    return;
}
