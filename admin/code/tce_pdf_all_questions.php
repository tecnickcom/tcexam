<?php

//============================================================+
// File name   : tce_pdf_all_questions.php
// Begin       : 2004-06-10
// Last Update : 2026-06-22
//
// Description : Creates a PDF document containing exported questions.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Creates a PDF document containing exported questions.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2005-07-06
 * @param $_REQUEST['subject_id'] (int) topic ID
 */

// Use the generated tc-lib-pdf fonts for this document (set before the config defines the legacy default).
require_once __DIR__ . '/../../vendor/autoload.php';
define('K_PATH_FONTS', realpath(__DIR__ . '/../../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

require_once '../config/tce_config.php';
$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once '../../shared/code/tce_authorization.php';
require_once '../../shared/code/tce_functions_auth_sql.php';
require_once '../../shared/code/tce_functions_tcecode.php';
require_once '../../shared/config/tce_pdf.php';
require_once '../../shared/code/tce_pdf_report.php';

if (
    !isset($_REQUEST['expmode'])
    || $_REQUEST['expmode'] <= 0
    || !isset($_REQUEST['module_id'])
    || $_REQUEST['module_id'] <= 0
    || (!isset($_REQUEST['subject_id']) || $_REQUEST['subject_id'] <= 0)
) {
    exit();
}

$expmode = (int) $_REQUEST['expmode'];
$module_id = (int) $_REQUEST['module_id'];
$subject_id = (int) $_REQUEST['subject_id'];

// check user's authorization for module
if (!F_isAuthorizedUser(K_TABLE_MODULES, 'module_id', $module_id, 'module_user_id')) {
    exit();
}

$show_answers = true;
if (isset($_REQUEST['hide_answers']) && $_REQUEST['hide_answers'] == 1) {
    $show_answers = false;
}

$doc_title = unhtmlentities($l['t_questions_list']);
$doc_description = F_compact_string(unhtmlentities($l['hp_select_all_questions']));

$qtype = ['S', 'M', 'T', '0']; // question types
$qright = [' ', '*']; // answer right marker

// --- create the PDF document (tc-lib-pdf) ---

$pdf = new TcePdfReport();

// header back-link QR-Code
switch ($expmode) {
    case 1:
        $pdf->setTCExamBackLink(
            K_PATH_URL
            . 'admin/code/tce_show_all_questions.php?subject_module_id='
            . $module_id
            . '&subject_id='
            . $subject_id,
        );
        break;
    case 2:
        $pdf->setTCExamBackLink(K_PATH_URL . 'admin/code/tce_show_all_questions.php?subject_module_id=' . $module_id);
        break;
    case 3:
        $pdf->setTCExamBackLink(K_PATH_URL . 'admin/code/tce_show_all_questions.php');
        break;
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

$rtl = $l['a_meta_dir'] == 'rtl';

// ---- module
$andmodwhere = '';
if ($expmode < 3) {
    $andmodwhere = 'module_id=' . $module_id;
}

$sqlm = F_select_modules_sql($andmodwhere);
if ($rm = F_db_query($sqlm, $db)) {
    while ($mm = F_db_fetch_array($rm)) {
        $module_id = $mm['module_id'];
        $module_name = $mm['module_name'];

        // ---- topic
        $where_sqls = 'subject_module_id=' . $module_id;
        if ($expmode < 2) {
            $where_sqls .= ' AND subject_id=' . $subject_id;
        }

        $sqls = F_select_subjects_sql($where_sqls);
        if ($rs = F_db_query($sqls, $db)) {
            while ($ms = F_db_fetch_array($rs)) {
                $subject_id = $ms['subject_id'];
                $subject_name = $ms['subject_name'];
                $subject_description = F_decode_tcecode($ms['subject_description']);

                $pdf->addReportPage();

                // subject header block
                $html =
                    '<h1 style="text-align:center;font-size:13pt;">' . htmlspecialchars((string) $doc_title) . '</h1>';
                $html .=
                    '<div style="background-color:#cccccc;font-weight:bold;padding:2px;">'
                    . htmlspecialchars($module_name . ' :: ' . $subject_name)
                    . '</div>';
                $html .=
                    '<div style="font-size:8pt;border:0.5px solid #000000;padding:2px;">'
                    . $subject_description
                    . '</div>';

                // ---- questions
                $sqlq =
                    'SELECT * FROM '
                    . K_TABLE_QUESTIONS
                    . '
					WHERE question_subject_id='
                    . $subject_id
                    . '
					ORDER BY question_enabled DESC, question_position, question_description';
                if ($rq = F_db_query($sqlq, $db)) {
                    $itemcount = 1;
                    while ($mq = F_db_fetch_array($rq)) {
                        $disabled = !F_getBoolean($mq['question_enabled']);
                        $rowstyle = $disabled ? 'color:#999999;' : '';
                        $flags =
                            (F_getBoolean($mq['question_fullscreen']) ? 'F' : '')
                            . (F_getBoolean($mq['question_inline_answers']) ? 'I' : '')
                            . (F_getBoolean($mq['question_auto_next']) ? 'A' : '');
                        $pos = $mq['question_position'] > 0 ? $mq['question_position'] : '';
                        $timer = $mq['question_timer'] > 0 ? $mq['question_timer'] : '';

                        // question metadata row: number, type, difficulty, position, flags, timer
                        $html .=
                            '<table border="0.5" cellpadding="2" style="font-size:7pt;'
                            . $rowstyle
                            . '"><tr style="text-align:center;">';
                        foreach ([
                            '#' . $itemcount,
                            $qtype[$mq['question_type'] - 1],
                            $mq['question_difficulty'],
                            $pos,
                            $flags,
                            $timer,
                        ] as $c) {
                            $html .= '<td>' . htmlspecialchars((string) $c) . '</td>';
                        }
                        $html .= '</tr></table>';

                        $html .=
                            '<div style="font-size:8pt;'
                            . $rowstyle
                            . '">'
                            . F_decode_tcecode($mq['question_description'])
                            . '</div>';
                        if (K_ENABLE_QUESTION_EXPLANATION && !empty($mq['question_explanation'])) {
                            $html .=
                                '<div style="font-size:7pt;border:0.5px solid #000000;"><b><i><u>'
                                . htmlspecialchars($l['w_explanation'])
                                . '</u></i></b><br/>'
                                . F_decode_tcecode($mq['question_explanation'])
                                . '</div>';
                        }

                        if ($show_answers) {
                            $sqla =
                                'SELECT * FROM '
                                . K_TABLE_ANSWERS
                                . '
								WHERE answer_question_id=\''
                                . $mq['question_id']
                                . '\'
								ORDER BY answer_position,answer_isright DESC';
                            if ($ra = F_db_query($sqla, $db)) {
                                $html .= '<table border="0.5" cellpadding="2" style="font-size:7pt;">';
                                $idx = 0;
                                while ($ma = F_db_fetch_array($ra)) {
                                    ++$idx;
                                    $adisabled = !F_getBoolean($ma['answer_enabled']);
                                    $astyle = $adisabled ? 'color:#999999;' : '';
                                    $rightmark = $mq['question_type'] != 4
                                        ? $qright[(int) F_getBoolean($ma['answer_isright'])]
                                        : '';
                                    $apos = $ma['answer_position'] > 0 ? $ma['answer_position'] : '';
                                    $akey = $ma['answer_keyboard_key'] > 0
                                        ? F_text_to_xml(chr($ma['answer_keyboard_key']))
                                        : '';
                                    $html .= '<tr style="' . $astyle . '">';
                                    $html .= '<td style="text-align:center;">' . $idx . '</td>';
                                    $html .=
                                        '<td style="text-align:center;">'
                                        . htmlspecialchars((string) $rightmark)
                                        . '</td>';
                                    $html .=
                                        '<td style="text-align:center;">' . htmlspecialchars((string) $apos) . '</td>';
                                    $html .=
                                        '<td style="text-align:center;">' . htmlspecialchars((string) $akey) . '</td>';
                                    $html .= '<td>' . F_decode_tcecode($ma['answer_description']) . '</td>';
                                    $html .= '</tr>';
                                    if (K_ENABLE_ANSWER_EXPLANATION && !empty($ma['answer_explanation'])) {
                                        $html .=
                                            '<tr><td colspan="5" style="font-size:6pt;"><b><i><u>'
                                            . htmlspecialchars($l['w_explanation'])
                                            . '</u></i></b><br/>'
                                            . F_decode_tcecode($ma['answer_explanation'])
                                            . '</td></tr>';
                                    }
                                }
                                $html .= '</table>';
                            } else {
                                F_display_db_error();
                            }
                        }
                        ++$itemcount;
                    } // end while questions
                } else {
                    F_display_db_error();
                }

                if ($rtl) {
                    $html = '<div dir="rtl">' . $html . '</div>';
                }
                $pdf->writeReportHTML($html);
            } // end while topics
        } else {
            F_display_db_error();
        }
    } // end while modules
} else {
    F_display_db_error();
}

// build the download file name
$pdf_filename = match ($expmode) {
    1 => 'tcexam_subject_' . $subject_id . '_' . date('YmdHi') . '.pdf',
    2 => 'tcexam_module_' . $module_id . '_' . date('YmdHi') . '.pdf',
    3 => 'tcexam_all_modules_' . date('YmdHi') . '.pdf',
    default => 'tcexam_export_' . date('YmdHi') . '.pdf',
};

$pdf->outputReport($pdf_filename);
