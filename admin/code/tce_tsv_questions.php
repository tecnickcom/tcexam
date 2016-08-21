<?php
//============================================================+
// File name   : tce_tsv_questions.php
// Begin       : 2006-03-06
// Last Update : 2013-09-05
//
// Description : Functions to export questions using CVS format.
//               (tab-separated values)
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
//    Copyright (C) 2004-2013  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display all questions grouped by topic in TSV format.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2012-12-31
 */

/**
 */

if ((isset($_REQUEST['expmode']) and ($_REQUEST['expmode'] > 0))
    and (isset($_REQUEST['module_id']) and ($_REQUEST['module_id'] > 0))
    and (isset($_REQUEST['subject_id']) and ($_REQUEST['subject_id'] > 0))) {
    $expmode = intval($_REQUEST['expmode']);
    $module_id = intval($_REQUEST['module_id']);
    $subject_id = intval($_REQUEST['subject_id']);

    // set TSV file name
    switch ($expmode) {
        case 1: {
            $tsv_filename = 'tcexam_subject_'.$subject_id.'_'.date('YmdHi').'.tsv';
            break;
        }
        case 2: {
            $tsv_filename = 'tcexam_module_'.$module_id.'_'.date('YmdHi').'.tsv';
            break;
        }
        case 3: {
            $tsv_filename = 'tcexam_all_modules_'.date('YmdHi').'.tsv';
            break;
        }
        default: {
            $tsv_filename = 'tcexam_export_'.date('YmdHi').'.tsv';
            break;
        }
    }

    // send TSV headers
    header('Content-Description: TSV File Transfer');
    header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    // force download dialog
    header('Content-Type: application/force-download');
    header('Content-Type: application/octet-stream', false);
    header('Content-Type: application/download', false);
    header('Content-Type: text/tab-separated-values', false);
    // use the Content-Disposition header to supply a recommended filename
    header('Content-Disposition: attachment; filename='.$tsv_filename.';');
    header('Content-Transfer-Encoding: binary');

    echo F_tsv_export_questions($module_id, $subject_id, $expmode);
} else {
    exit;
}

/**
 * Export all questions of the selected subject to TSV.
 * @param $module_id (int)  module ID
 * @param $subject_id (int) topic ID
 * @param $expmode (int) export mode: 1 = selected topic; 2 = selected module; 3 = all modules.
 * @return TSV data
 */
function F_tsv_export_questions($module_id, $subject_id, $expmode)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    require_once('../../shared/code/tce_authorization.php');
    require_once('../../shared/code/tce_functions_auth_sql.php');
    $module_id = intval($module_id);
    $subject_id = intval($subject_id);
    $expmode = intval($expmode);
    $qtype = array('S', 'M', 'T', 'O');
    $tsv = ''; // TSV data to be returned
    
    // headers
    
    $tsv .= 'M=MODULE'; // MODULE
    $tsv .= K_TAB.'module_enabled';
    $tsv .= K_TAB.'module_name';
    $tsv .= K_NEWLINE;

    $tsv .= 'S=SUBJECT'; // SUBJECT
    $tsv .= K_TAB.'subject_enabled';
    $tsv .= K_TAB.'subject_name';
    $tsv .= K_TAB.'subject_description';
    $tsv .= K_NEWLINE;
    
    $tsv .= 'Q=QUESTION'; // QUESTION
    $tsv .= K_TAB.'question_enabled';
    $tsv .= K_TAB.'question_description';
    $tsv .= K_TAB.'question_explanation';
    $tsv .= K_TAB.'question_type';
    $tsv .= K_TAB.'question_difficulty';
    $tsv .= K_TAB.'question_position';
    $tsv .= K_TAB.'question_timer';
    $tsv .= K_TAB.'question_fullscreen';
    $tsv .= K_TAB.'question_inline_answers';
    $tsv .= K_TAB.'question_auto_next';
    $tsv .= K_NEWLINE;
    
    $tsv .= 'A=ANSWER'; // ANSWER
    $tsv .= K_TAB.'answer_enabled';
    $tsv .= K_TAB.'answer_description';
    $tsv .= K_TAB.'answer_explanation';
    $tsv .= K_TAB.'answer_isright';
    $tsv .= K_TAB.'answer_position';
    $tsv .= K_TAB.'answer_keyboard_key';
    $tsv .= K_NEWLINE;
    
    $tsv .= K_NEWLINE;
    
    // ---- module
    $andmodwhere = '';
    if ($expmode < 3) {
        $andmodwhere = 'module_id='.$module_id.'';
    }
    $sqlm = F_select_modules_sql($andmodwhere);
    if ($rm = F_db_query($sqlm, $db)) {
        while ($mm = F_db_fetch_array($rm)) {
            $tsv .= 'M'; // MODULE
            $tsv .= K_TAB.intval(F_getBoolean($mm['module_enabled']));
            $tsv .= K_TAB.F_text_to_tsv($mm['module_name']);
            $tsv .= K_NEWLINE;
            // ---- topic
            $where_sqls = 'subject_module_id='.$mm['module_id'].'';
            if ($expmode < 2) {
                $where_sqls .= ' AND subject_id='.$subject_id.'';
            }
            $sqls = F_select_subjects_sql($where_sqls);
            if ($rs = F_db_query($sqls, $db)) {
                while ($ms = F_db_fetch_array($rs)) {
                    $tsv .= 'S'; // SUBJECT
                    $tsv .= K_TAB.intval(F_getBoolean($ms['subject_enabled']));
                    $tsv .= K_TAB.F_text_to_tsv($ms['subject_name']);
                    $tsv .= K_TAB.F_text_to_tsv($ms['subject_description']);
                    $tsv .= K_NEWLINE;
                    // ---- questions
                    $sql = 'SELECT *
						FROM '.K_TABLE_QUESTIONS.'
						WHERE question_subject_id='.$ms['subject_id'].'
						ORDER BY question_enabled DESC, question_position, question_description';
                    if ($r = F_db_query($sql, $db)) {
                        while ($m = F_db_fetch_array($r)) {
                            $tsv .= 'Q'; // QUESTION
                            $tsv .= K_TAB.intval(F_getBoolean($m['question_enabled']));
                            $tsv .= K_TAB.F_text_to_tsv($m['question_description']);
                            $tsv .= K_TAB.F_text_to_tsv($m['question_explanation']);
                            $tsv .= K_TAB.$qtype[$m['question_type']-1];
                            $tsv .= K_TAB.$m['question_difficulty'];
                            $tsv .= K_TAB.$m['question_position'];
                            $tsv .= K_TAB.$m['question_timer'];
                            $tsv .= K_TAB.intval(F_getBoolean($m['question_fullscreen']));
                            $tsv .= K_TAB.intval(F_getBoolean($m['question_inline_answers']));
                            $tsv .= K_TAB.intval(F_getBoolean($m['question_auto_next']));
                            $tsv .= K_NEWLINE;
                            // display alternative answers
                            $sqla = 'SELECT *
								FROM '.K_TABLE_ANSWERS.'
								WHERE answer_question_id=\''.$m['question_id'].'\'
								ORDER BY answer_position,answer_isright DESC';
                            if ($ra = F_db_query($sqla, $db)) {
                                while ($ma = F_db_fetch_array($ra)) {
                                    $tsv .= 'A'; // ANSWER
                                    $tsv .= K_TAB.intval(F_getBoolean($ma['answer_enabled']));
                                    $tsv .= K_TAB.F_text_to_tsv($ma['answer_description']);
                                    $tsv .= K_TAB.F_text_to_tsv($ma['answer_explanation']);
                                    $tsv .= K_TAB.intval(F_getBoolean($ma['answer_isright']));
                                    $tsv .= K_TAB.$ma['answer_position'];
                                    $tsv .= K_TAB.$ma['answer_keyboard_key'];
                                    $tsv .= K_NEWLINE;
                                }
                            } else {
                                F_display_db_error();
                            }
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
    return $tsv;
}

//============================================================+
// END OF FILE
//============================================================+
