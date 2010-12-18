<?php
//============================================================+
// File name   : tce_functions_questions.php
// Begin       : 2008-11-26
// Last Update : 2009-10-10
//
// Description : Functions to manipulate questions.
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com S.r.l.
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
 * @param $subject_id (int) subject ID
 * @param $enabled (boolean) if true enables question, false otherwise
 */
function F_question_set_enabled($question_id, $subject_id, $enabled=true) {
	global $l, $db;
	require_once('../config/tce_config.php');
	if (!F_isAuthorizedUser(K_TABLE_SUBJECTS, 'subject_id', $subject_id, 'subject_user_id')) {
		return; // unauthorized user
	}
	$question_id = intval($question_id);
	$sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
		question_enabled=\''.intval($enabled).'\'
		WHERE question_id='.$question_id.'';
	if(!$r = F_db_query($sql, $db)) {
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
function F_question_get_position($question_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$question_id = intval($question_id);
	$question_position = 0;
	$sql = 'SELECT question_position
		FROM '.K_TABLE_QUESTIONS.'
		WHERE question_id='.$question_id.'
		LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
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
function F_question_get_data($question_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$question_id = intval($question_id);
	$question_position = 0;
	$sql = 'SELECT *
		FROM '.K_TABLE_QUESTIONS.'
		WHERE question_id='.$question_id.'
		LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
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
function F_question_delete($question_id, $subject_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$question_id = intval($question_id);
	$subject_id = intval($subject_id);
	if (!F_isAuthorizedUser(K_TABLE_SUBJECTS, 'subject_id', $subject_id, 'subject_user_id')) {
		return; // unauthorized user
	}
	// check if this record is used (test_log)
	if(!F_check_unique(K_TABLE_TESTS_LOGS, 'testlog_question_id='.$question_id.'')) {
		F_question_set_enabled($question_id, false);
	} else {
		$sql = 'START TRANSACTION';
		if(!$r = F_db_query($sql, $db)) {
			F_display_db_error();
		}
		// get question position (if defined)
		$question_position = F_question_get_position($question_id);
		// delete question
		$sql = 'DELETE FROM '.K_TABLE_QUESTIONS.' WHERE question_id='.$question_id.'';
		if(!$r = F_db_query($sql, $db)) {
			F_display_db_error(false);
			F_db_query('ROLLBACK', $db); // rollback transaction
		} else {
			// adjust questions ordering
			if ($question_position > 0) {
				$sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
					question_position=question_position-1
					WHERE question_subject_id='.$subject_id.'
						AND question_position>'.$question_position.'';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
					F_db_query('ROLLBACK', $db); // rollback transaction
				}
			}
			$sql = 'COMMIT';
			if(!$r = F_db_query($sql, $db)) {
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
function F_question_copy($question_id, $new_subject_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$question_id = intval($question_id);
	$new_subject_id = intval($new_subject_id);
	if (!F_isAuthorizedUser(K_TABLE_SUBJECTS, 'subject_id', $new_subject_id, 'subject_user_id')) {
		return; // unauthorized user
	}
	$q = F_question_get_data($question_id);
	if ($q !== false) {
		if (K_DATABASE_TYPE == 'ORACLE') {
			$chksql = 'dbms_lob.instr(question_description,\''.F_escape_sql($q['question_description']).'\',1,1)>0';
		} else {
			$chksql = 'question_description=\''.F_escape_sql($q['question_description']).'\'';
		}
		if(F_check_unique(K_TABLE_QUESTIONS, $chksql.' AND question_subject_id='.$new_subject_id.'')) {
			$sql = 'START TRANSACTION';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				break;
			}
			// adjust questions ordering
			if ($q['question_position'] > 0) {
				$sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
					question_position=question_position+1
					WHERE question_subject_id='.$new_subject_id.'
						AND question_position>='.$q['question_position'].'';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
					F_db_query('ROLLBACK', $db); // rollback transaction
				}
			}
			$sql = 'INSERT INTO '.K_TABLE_QUESTIONS.' (
				question_subject_id,
				question_description,
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
				\''.F_escape_sql($q['question_description']).'\',
				\''.$q['question_type'].'\',
				\''.$q['question_difficulty'].'\',
				\''.$q['question_enabled'].'\',
				'.F_zero_to_null($q['question_position']).',
				\''.$q['question_timer'].'\',
				\''.$q['question_fullscreen'].'\',
				\''.$q['question_inline_answers'].'\',
				\''.$q['question_auto_next'].'\'
				)';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$new_question_id = F_db_insert_id($db, K_TABLE_QUESTIONS, 'question_id');
			}
			// copy associated answers
			$sql = 'SELECT *
				FROM '.K_TABLE_ANSWERS.'
				WHERE answer_question_id='.$question_id.'';
			if($r = F_db_query($sql, $db)) {
				while($m = F_db_fetch_array($r)) {
					$sqli = 'INSERT INTO '.K_TABLE_ANSWERS.' (
						answer_question_id,
						answer_description,
						answer_isright,
						answer_enabled,
						answer_position,
						answer_keyboard_key
						) VALUES (
						'.$new_question_id.',
						\''.F_escape_sql($m['answer_description']).'\',
						\''.$m['answer_isright'].'\',
						\''.$m['answer_enabled'].'\',
						'.F_zero_to_null($m['answer_position']).',
						'.F_empty_to_null($m['answer_keyboard_key']).'
						)';
					if(!$ri = F_db_query($sqli, $db)) {
						F_display_db_error(false);
						F_db_query('ROLLBACK', $db); // rollback transaction
					}
				}
			} else {
				F_display_db_error();
			}
			$sql = 'COMMIT';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				break;
			}
		}
	}
}

//============================================================+
// END OF FILE
//============================================================+
