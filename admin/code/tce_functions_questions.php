<?php

//============================================================+
// File name   : tce_functions_questions.php
// Begin       : 2008-11-26
// Last Update : 2023-11-30
//
// Description : Functions to manipulate questions.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
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
 * @return boolean true if the question was updated, false otherwise
 */
function F_question_set_enabled($question_id, $enabled = true)
{
    global $l, $db;
    require_once '../config/tce_config.php';
    $question_id = (int) $question_id;
    if (!F_isAuthorizedQuestion($question_id)) {
        return false;
    }

    $sql =
        'UPDATE '
        . K_TABLE_QUESTIONS
        . ' SET
		question_enabled=\''
        . (int) $enabled
        . '\'
		WHERE question_id='
        . $question_id
        . '';
    if (!($r = F_db_query($sql, $db))) {
        F_display_db_error(false);
        return false;
    }

    return true;
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
    require_once '../config/tce_config.php';
    $question_id = (int) $question_id;
    $question_position = 0;
    $sql = 'SELECT question_position
		FROM ' . K_TABLE_QUESTIONS . '
		WHERE question_id=' . $question_id . '
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
    require_once '../config/tce_config.php';
    $question_id = (int) $question_id;
    $question_position = 0;
    $sql = 'SELECT *
		FROM ' . K_TABLE_QUESTIONS . '
		WHERE question_id=' . $question_id . '
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
 * @return boolean true if the question was deleted or disabled, false otherwise
 */
function F_question_delete($question_id)
{
    global $l, $db;
    require_once '../config/tce_config.php';
    $question_id = (int) $question_id;
    $q = F_question_get_data($question_id);
    if ($q === false || !F_isAuthorizedSubject($q['question_subject_id'])) {
        return false;
    }

    // check if this record is used (test_log)
    if (!F_check_unique(K_TABLE_TESTS_LOGS, 'testlog_question_id=' . $question_id . '')) {
        return F_question_set_enabled($question_id, false);
    }

    $subject_id = (int) $q['question_subject_id'];
    $question_position = (int) $q['question_position'];
    if (!F_db_query('START TRANSACTION', $db)) {
        F_display_db_error(false);
        return false;
    }

    $sql = 'DELETE FROM ' . K_TABLE_QUESTIONS . ' WHERE question_id=' . $question_id . '';
    if (!F_db_query($sql, $db)) {
        F_display_db_error(false);
        F_db_query('ROLLBACK', $db);
        return false;
    }

    // adjust questions ordering
    if ($question_position > 0) {
        $sql =
            'UPDATE '
            . K_TABLE_QUESTIONS
            . ' SET
			question_position=question_position-1
			WHERE question_subject_id='
            . $subject_id
            . '
				AND question_position>'
            . $question_position
            . '';
        if (!F_db_query($sql, $db)) {
            F_display_db_error(false);
            F_db_query('ROLLBACK', $db);
            return false;
        }
    }

    if (!F_db_query('COMMIT', $db)) {
        F_display_db_error(false);
        return false;
    }

    return true;
}

/**
 * Copy selected question to another topic
 * @author Nicola Asuni
 * @since 2008-11-26
 * @param $question_id (int) question ID
 * @param $new_subject_id (int) new subject ID
 * @return boolean true if a new copy was created, false otherwise
 */
function F_question_copy($question_id, $new_subject_id)
{
    global $l, $db;
    require_once '../config/tce_config.php';
    $question_id = (int) $question_id;
    $new_subject_id = (int) $new_subject_id;
    $q = F_question_get_data($question_id);
    if ($q === false || !F_isAuthorizedSubject($q['question_subject_id']) || !F_isAuthorizedSubject($new_subject_id)) {
        return false;
    }

    if (K_DATABASE_TYPE == 'ORACLE') {
        $chksql = "dbms_lob.instr(question_description,'" . F_escape_sql($db, $q['question_description']) . "',1,1)>0";
    } elseif (K_DATABASE_TYPE === 'MYSQL' && defined('K_MYSQL_QA_BIN_UNIQUITY') && K_MYSQL_QA_BIN_UNIQUITY) {
        $chksql =
            "question_description='"
            . F_escape_sql($db, $q['question_description'])
            . "' COLLATE "
            . F_db_mysql_bin_collation();
    } else {
        $chksql = "question_description='" . F_escape_sql($db, $q['question_description']) . "'";
    }

    if (!F_check_unique(K_TABLE_QUESTIONS, $chksql . ' AND question_subject_id=' . $new_subject_id . '')) {
        return false;
    }

    if (!F_db_query('START TRANSACTION', $db)) {
        F_display_db_error(false);
        return false;
    }

    // adjust questions ordering
    if ($q['question_position'] > 0) {
        $sql =
            'UPDATE '
            . K_TABLE_QUESTIONS
            . ' SET
			question_position=question_position+1
			WHERE question_subject_id='
            . $new_subject_id
            . '
				AND question_position>='
            . (int) $q['question_position']
            . '';
        if (!F_db_query($sql, $db)) {
            F_display_db_error(false);
            F_db_query('ROLLBACK', $db);
            return false;
        }
    }

    $sql =
        'INSERT INTO '
        . K_TABLE_QUESTIONS
        . ' (
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
		'
        . $new_subject_id
        . ',
		\''
        . F_escape_sql($db, $q['question_description'])
        . '\',
		\''
        . F_escape_sql($db, $q['question_explanation'])
        . '\',
		\''
        . (int) $q['question_type']
        . '\',
		\''
        . (int) $q['question_difficulty']
        . '\',
		\''
        . F_escape_sql($db, $q['question_enabled'])
        . '\',
		'
        . F_zero_to_null($q['question_position'])
        . ',
		\''
        . (int) $q['question_timer']
        . '\',
		\''
        . F_escape_sql($db, $q['question_fullscreen'])
        . '\',
		\''
        . F_escape_sql($db, $q['question_inline_answers'])
        . '\',
		\''
        . F_escape_sql($db, $q['question_auto_next'])
        . '\'
		)';
    if (!F_db_query($sql, $db)) {
        F_display_db_error(false);
        F_db_query('ROLLBACK', $db);
        return false;
    }

    $new_question_id = (int) F_db_insert_id($db, K_TABLE_QUESTIONS, 'question_id');

    // copy associated answers
    $sql = 'SELECT *
		FROM ' . K_TABLE_ANSWERS . '
		WHERE answer_question_id=' . $question_id . '';
    if (!($r = F_db_query($sql, $db))) {
        F_display_db_error(false);
        F_db_query('ROLLBACK', $db);
        return false;
    }

    while ($m = F_db_fetch_array($r)) {
        $sqli =
            'INSERT INTO '
            . K_TABLE_ANSWERS
            . ' (
			answer_question_id,
			answer_description,
			answer_explanation,
			answer_isright,
			answer_enabled,
			answer_position,
			answer_keyboard_key
			) VALUES (
			'
            . $new_question_id
            . ',
			\''
            . F_escape_sql($db, $m['answer_description'])
            . '\',
			\''
            . F_escape_sql($db, $m['answer_explanation'])
            . '\',
			\''
            . F_escape_sql($db, $m['answer_isright'])
            . '\',
			\''
            . F_escape_sql($db, $m['answer_enabled'])
            . '\',
			'
            . F_zero_to_null($m['answer_position'])
            . ',
			'
            . F_empty_to_null((string) $m['answer_keyboard_key'])
            . '
			)';
        if (!F_db_query($sqli, $db)) {
            F_display_db_error(false);
            F_db_query('ROLLBACK', $db);
            return false;
        }
    }

    if (!F_db_query('COMMIT', $db)) {
        F_display_db_error(false);
        return false;
    }

    return true;
}
