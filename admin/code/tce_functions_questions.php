<?php
//============================================================+
// File name   : tce_functions_questions.php
// Begin       : 2008-11-26
// Last Update : 2012-11-07
//
// Description : Functions to manipulate questions.
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
 * Functions to manipulate questions.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2008-11-26
 */

/**
 * Enable/Disable selected question
 * @author Nicola Asuni
 * @since 2008-11-26
 * @param $question_id (int) question ID
 * @param $enabled (boolean) if true enables question, false otherwise
 */
function F_question_set_enabled($question_id, $enabled = true)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    $question_id = intval($question_id);
    $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
		question_enabled=\''.intval($enabled).'\'
		WHERE question_id='.$question_id.'';
    if (!$r = F_db_query($sql, $db)) {
        F_display_db_error(false);
    }
}

/**
 * Get question position
 * @author Nicola Asuni
 * @since 2008-11-26
 * @param $question_id (int) question ID
 * @return int question position
 */
function F_question_get_position($question_id)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    $question_id = intval($question_id);
    $question_position = 0;
    $sql = 'SELECT question_position
		FROM '.K_TABLE_QUESTIONS.'
		WHERE question_id='.$question_id.'
		LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            $question_position = $m['question_position'];
        }
    } else {
        F_display_db_error();
    }
    return $question_position;
}

/**
 * Get question data
 * @author Nicola Asuni
 * @since 2008-11-26
 * @param $question_id (int) question ID
 * @return array containing selected question data, false in case of error
 */
function F_question_get_data($question_id)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    $question_id = intval($question_id);
    $question_position = 0;
    $sql = 'SELECT *
		FROM '.K_TABLE_QUESTIONS.'
		WHERE question_id='.$question_id.'
		LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            return $m;
        }
    } else {
        F_display_db_error();
    }
    return false;
}

/**
 * Delete selected question (or disable it if used)
 * @author Nicola Asuni
 * @since 2008-11-26
 * @param $question_id (int) question ID
 * @param $subject_id (int) subject ID
 */
function F_question_delete($question_id, $subject_id)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    $question_id = intval($question_id);
    $subject_id = intval($subject_id);
    // check if this record is used (test_log)
    if (!F_check_unique(K_TABLE_TESTS_LOGS, 'testlog_question_id='.$question_id.'')) {
        F_question_set_enabled($question_id, false);
    } else {
        $sql = 'START TRANSACTION';
        if (!$r = F_db_query($sql, $db)) {
            F_display_db_error();
        }
        // get question position (if defined)
        $question_position = F_question_get_position($question_id);
        // delete question
        $sql = 'DELETE FROM '.K_TABLE_QUESTIONS.' WHERE question_id='.$question_id.'';
        if (!$r = F_db_query($sql, $db)) {
            F_display_db_error(false);
            F_db_query('ROLLBACK', $db); // rollback transaction
        } else {
            // adjust questions ordering
            if ($question_position > 0) {
                $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
					question_position=question_position-1
					WHERE question_subject_id='.$subject_id.'
						AND question_position>'.$question_position.'';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error(false);
                    F_db_query('ROLLBACK', $db); // rollback transaction
                }
            }
            $sql = 'COMMIT';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error();
            }
        }
    }
}

/**
 * Copy selected question to another topic
 * @author Nicola Asuni
 * @since 2008-11-26
 * @param $question_id (int) question ID
 * @param $new_subject_id (int) new subject ID
 */
function F_question_copy($question_id, $new_subject_id)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    $question_id = intval($question_id);
    $new_subject_id = intval($new_subject_id);
    // check authorization
    $sql = 'SELECT subject_module_id FROM '.K_TABLE_SUBJECTS.' WHERE subject_id='.$new_subject_id.' LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            $subject_module_id = $m['subject_module_id'];
            // check user's authorization for parent module
            if (!F_isAuthorizedUser(K_TABLE_MODULES, 'module_id', $subject_module_id, 'module_user_id')) {
                return;
            }
        }
    } else {
        F_display_db_error();
        return;
    }
    $q = F_question_get_data($question_id);
    if ($q !== false) {
        if (K_DATABASE_TYPE == 'ORACLE') {
            $chksql = 'dbms_lob.instr(question_description,\''.F_escape_sql($db, $q['question_description']).'\',1,1)>0';
        } elseif ((K_DATABASE_TYPE == 'MYSQL') and defined('K_MYSQL_QA_BIN_UNIQUITY') and K_MYSQL_QA_BIN_UNIQUITY) {
            $chksql = 'question_description=\''.F_escape_sql($db, $q['question_description']).'\' COLLATE utf8_bin';
        } else {
            $chksql = 'question_description=\''.F_escape_sql($db, $q['question_description']).'\'';
        }
        if (F_check_unique(K_TABLE_QUESTIONS, $chksql.' AND question_subject_id='.$new_subject_id.'')) {
            $sql = 'START TRANSACTION';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
                break;
            }
            // adjust questions ordering
            if ($q['question_position'] > 0) {
                $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
					question_position=question_position+1
					WHERE question_subject_id='.$new_subject_id.'
						AND question_position>='.$q['question_position'].'';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error(false);
                    F_db_query('ROLLBACK', $db); // rollback transaction
                }
            }
            $sql = 'INSERT INTO '.K_TABLE_QUESTIONS.' (
				question_subject_id,
				question_description,
				question_explanation,
				question_type,
				question_difficulty,
				question_enabled,
				question_position,
				question_timer,
				question_fullscreen,
				question_inline_answers,
				question_auto_next
				) VALUES (
				'.$new_subject_id.',
				\''.F_escape_sql($db, $q['question_description']).'\',
				\''.F_escape_sql($db, $q['question_explanation']).'\',
				\''.$q['question_type'].'\',
				\''.$q['question_difficulty'].'\',
				\''.$q['question_enabled'].'\',
				'.F_zero_to_null($q['question_position']).',
				\''.$q['question_timer'].'\',
				\''.$q['question_fullscreen'].'\',
				\''.$q['question_inline_answers'].'\',
				\''.$q['question_auto_next'].'\'
				)';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                $new_question_id = F_db_insert_id($db, K_TABLE_QUESTIONS, 'question_id');
            }
            // copy associated answers
            $sql = 'SELECT *
				FROM '.K_TABLE_ANSWERS.'
				WHERE answer_question_id='.$question_id.'';
            if ($r = F_db_query($sql, $db)) {
                while ($m = F_db_fetch_array($r)) {
                    $sqli = 'INSERT INTO '.K_TABLE_ANSWERS.' (
						answer_question_id,
						answer_description,
						answer_explanation,
						answer_isright,
						answer_enabled,
						answer_position,
						answer_keyboard_key
						) VALUES (
						'.$new_question_id.',
						\''.F_escape_sql($db, $m['answer_description']).'\',
						\''.F_escape_sql($db, $m['answer_explanation']).'\',
						\''.$m['answer_isright'].'\',
						\''.$m['answer_enabled'].'\',
						'.F_zero_to_null($m['answer_position']).',
						'.F_empty_to_null($m['answer_keyboard_key']).'
						)';
                    if (!$ri = F_db_query($sqli, $db)) {
                        F_display_db_error(false);
                        F_db_query('ROLLBACK', $db); // rollback transaction
                    }
                }
            } else {
                F_display_db_error();
            }
            $sql = 'COMMIT';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
                break;
            }
        }
    }
}

//============================================================+
// END OF FILE
//============================================================+
