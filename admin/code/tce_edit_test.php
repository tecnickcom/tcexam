<?php
//============================================================+
// File name   : tce_edit_test.php
// Begin       : 2004-04-27
// Last Update : 2013-08-23
//
// Description : Edit Tests
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
//    Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Edit test.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-04-27
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_TESTS;
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/config/tce_user_registration.php');

$thispage_title = $l['t_tests_editor'];
$enable_calendar = true;
require_once('tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('tce_functions_tcecode_editor.php');
require_once('../../shared/code/tce_functions_auth_sql.php');
require_once('tce_functions_user_select.php');
require_once('tce_functions_test_select.php');

// set default values
if (!isset($_REQUEST['test_results_to_users']) or (empty($_REQUEST['test_results_to_users']))) {
    $test_results_to_users = false;
} else {
    $test_results_to_users = F_getBoolean($_REQUEST['test_results_to_users']);
}
if (!isset($_REQUEST['test_report_to_users']) or (empty($_REQUEST['test_report_to_users']))) {
    $test_report_to_users = false;
} else {
    $test_report_to_users = F_getBoolean($_REQUEST['test_report_to_users']);
}
if (!isset($_REQUEST['subject_id']) or (empty($_REQUEST['subject_id']))) {
    $subject_id = array();
} else {
    $subject_id = $_REQUEST['subject_id'];
}
if (!isset($_REQUEST['tsubset_type']) or (empty($_REQUEST['tsubset_type']))) {
    $tsubset_type = 0;
} else {
    $tsubset_type = intval($_REQUEST['tsubset_type']);
}
if (!isset($_REQUEST['tsubset_difficulty'])) {
    $tsubset_difficulty = 1;
} else {
    $tsubset_difficulty = intval($_REQUEST['tsubset_difficulty']);
}
if (!isset($_REQUEST['tsubset_quantity']) or (empty($_REQUEST['tsubset_quantity']))) {
    $tsubset_quantity = 1;
} else {
    $tsubset_quantity = intval($_REQUEST['tsubset_quantity']);
}
if (!isset($_REQUEST['tsubset_answers']) or (empty($_REQUEST['tsubset_answers']))) {
    $tsubset_answers = 2;
} else {
    $tsubset_answers = intval($_REQUEST['tsubset_answers']);
}
if (isset($_REQUEST['tsubset_id'])) {
    $tsubset_id = intval($_REQUEST['tsubset_id']);
}
if (isset($_REQUEST['test_duration_time'])) {
    $test_duration_time = intval($_REQUEST['test_duration_time']);
}
if (isset($_REQUEST['group_id'])) {
    $group_id = intval($_REQUEST['group_id']);
}
if (!isset($_REQUEST['test_score_right']) or (empty($_REQUEST['test_score_right']))) {
    $test_score_right = 0;
} else {
    $test_score_right = floatval($_REQUEST['test_score_right']);
}
if (!isset($_REQUEST['test_score_wrong']) or (empty($_REQUEST['test_score_wrong']))) {
    $test_score_wrong = 0;
} else {
    $test_score_wrong = floatval($_REQUEST['test_score_wrong']);
}
if (!isset($_REQUEST['test_score_unanswered']) or (empty($_REQUEST['test_score_unanswered']))) {
    $test_score_unanswered = 0;
} else {
    $test_score_unanswered = floatval($_REQUEST['test_score_unanswered']);
}
if (!isset($_REQUEST['test_score_threshold']) or (empty($_REQUEST['test_score_threshold']))) {
    $test_score_threshold = 0;
} else {
    $test_score_threshold = floatval($_REQUEST['test_score_threshold']);
}
if (!isset($_REQUEST['test_random_questions_select']) or (empty($_REQUEST['test_random_questions_select']))) {
    $test_random_questions_select = false;
} else {
    $test_random_questions_select = F_getBoolean($_REQUEST['test_random_questions_select']);
}
if (!isset($_REQUEST['test_random_questions_order']) or (empty($_REQUEST['test_random_questions_order']))) {
    $test_random_questions_order = false;
} else {
    $test_random_questions_order = F_getBoolean($_REQUEST['test_random_questions_order']);
}
if (!isset($_REQUEST['test_questions_order_mode']) or (empty($_REQUEST['test_questions_order_mode']))) {
    $test_questions_order_mode = 0;
} else {
    $test_questions_order_mode = max(0, min(3, intval($_REQUEST['test_questions_order_mode'])));
}
if (!isset($_REQUEST['test_random_answers_select']) or (empty($_REQUEST['test_random_answers_select']))) {
    $test_random_answers_select = false;
} else {
    $test_random_answers_select = F_getBoolean($_REQUEST['test_random_answers_select']);
}
if (!isset($_REQUEST['test_random_answers_order']) or (empty($_REQUEST['test_random_answers_order']))) {
    $test_random_answers_order = false;
} else {
    $test_random_answers_order = F_getBoolean($_REQUEST['test_random_answers_order']);
}
if (!isset($_REQUEST['test_answers_order_mode']) or (empty($_REQUEST['test_answers_order_mode']))) {
    $test_answers_order_mode = 0;
} else {
    $test_answers_order_mode = max(0, min(2, intval($_REQUEST['test_answers_order_mode'])));
}
if (!isset($_REQUEST['test_comment_enabled']) or (empty($_REQUEST['test_comment_enabled']))) {
    $test_comment_enabled = false;
} else {
    $test_comment_enabled = F_getBoolean($_REQUEST['test_comment_enabled']);
}
if (!isset($_REQUEST['test_menu_enabled']) or (empty($_REQUEST['test_menu_enabled']))) {
    $test_menu_enabled = false;
} else {
    $test_menu_enabled = F_getBoolean($_REQUEST['test_menu_enabled']);
}
if (!isset($_REQUEST['test_noanswer_enabled']) or (empty($_REQUEST['test_noanswer_enabled']))) {
    $test_noanswer_enabled = false;
} else {
    $test_noanswer_enabled = F_getBoolean($_REQUEST['test_noanswer_enabled']);
}
if (!isset($_REQUEST['test_mcma_radio']) or (empty($_REQUEST['test_mcma_radio']))) {
    $test_mcma_radio = false;
} else {
    $test_mcma_radio = F_getBoolean($_REQUEST['test_mcma_radio']);
}
if (!isset($_REQUEST['test_repeatable']) or (empty($_REQUEST['test_repeatable']))) {
    $test_repeatable = false;
} else {
    $test_repeatable = F_getBoolean($_REQUEST['test_repeatable']);
}
if (!isset($_REQUEST['test_mcma_partial_score']) or (empty($_REQUEST['test_mcma_partial_score']))) {
    $test_mcma_partial_score = false;
} else {
    $test_mcma_partial_score = F_getBoolean($_REQUEST['test_mcma_partial_score']);
}
if (!isset($_REQUEST['test_logout_on_timeout']) or (empty($_REQUEST['test_logout_on_timeout']))) {
    $test_logout_on_timeout = false;
} else {
    $test_logout_on_timeout = F_getBoolean($_REQUEST['test_logout_on_timeout']);
}
if (!isset($_REQUEST['test_max_score'])) {
    $test_max_score = 0;
} else {
    $test_max_score = floatval($_REQUEST['test_max_score']);
}

$test_max_score_new = 0; // test max score
$qtype = array('S', 'M', 'T', 'O'); // question types
$qordmode = array($l['w_position'], $l['w_alphabetic'], $l['w_id'], $l['w_type'], $l['w_subject']);
$aordmode = array($l['w_position'], $l['w_alphabetic'], $l['w_id']);

$test_fieldset_name = '';

if (isset($_REQUEST['test_id']) and ($_REQUEST['test_id'] > 0)) {
    $test_id = intval($_REQUEST['test_id']);
    // check user's authorization
    if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
        F_print_error('ERROR', $l['m_authorization_denied']);
        exit;
    }
} else {
    $test_id = 0;
}

if (isset($_POST['lock'])) {
    $menu_mode = 'lock';
} elseif (isset($_POST['unlock'])) {
    $menu_mode = 'unlock';
}

switch ($menu_mode) {
    case 'lock':{ // lock test by changing end date (subtract 1000 years)
        $sql = 'UPDATE '.K_TABLE_TESTS.' SET
			test_end_time='.F_empty_to_null(''.(intval(substr($test_end_time, 0, 1)) - 1).substr($test_end_time, 1)).'
			WHERE test_id='.$test_id.'';
        if (!$r = F_db_query($sql, $db)) {
            F_display_db_error(false);
        } else {
            F_print_error('MESSAGE', $l['m_updated']);
        }
        break;
    }

    case 'unlock':{ // unlock test by restoring original end date (add 1000 years)
        $sql = 'UPDATE '.K_TABLE_TESTS.' SET
			test_end_time='.F_empty_to_null(''.(intval(substr($test_end_time, 0, 1)) + 1).substr($test_end_time, 1)).'
			WHERE test_id='.$test_id.'';
        if (!$r = F_db_query($sql, $db)) {
            F_display_db_error(false);
        } else {
            F_print_error('MESSAGE', $l['m_updated']);
        }
        break;
    }

    case 'deletesubject':{ // delete subject
        // check referential integrity (NOTE: mysql do not support "ON UPDATE" constraint)
        if (!F_check_unique(K_TABLE_TEST_USER, 'testuser_test_id='.$test_id.'')) {
            F_print_error('WARNING', $l['m_update_restrict']);
            F_stripslashes_formfields();
            break;
        }
        $sql = 'DELETE FROM '.K_TABLE_TEST_SUBJSET.' WHERE tsubset_id='.$tsubset_id.'';
        if (!$r = F_db_query($sql, $db)) {
            F_display_db_error(false);
        } else {
            F_print_error('MESSAGE', $l['m_deleted']);
        }
        break;
    }

    case 'addquestion':{ // Add question type
        // check referential integrity (NOTE: mysql do not support "ON UPDATE" constraint)
        if (!F_check_unique(K_TABLE_TEST_USER, 'testuser_test_id='.$test_id.'')) {
            F_print_error('WARNING', $l['m_update_restrict']);
            $formstatus = false;
            F_stripslashes_formfields();
            break;
        }
        if ($formstatus = F_check_form_fields()) {
            if ((isset($subject_id)) and (!empty($subject_id)) and (isset($tsubset_quantity))) {
                if ($tsubset_type == 3) {
                    // free-text questions do not have alternative answers to display
                    $tsubset_answers = 0;
                } elseif (($tsubset_answers < 2) and ($tsubset_difficulty > 0)) {
                    // questions must have at least 2 alternative answers
                    $tsubset_answers = 2;
                }
                // create a comma separated list of subjects IDs
                $subjids = '';
                foreach ($subject_id as $subid) {
                    if ($subid[0] == '#') {
                        // module ID
                        $modid = intval(substr($subid, 1));
                        $sqlsm = F_select_subjects_sql('subject_module_id='.$modid.'');
                        if ($rsm = F_db_query($sqlsm, $db)) {
                            while ($msm = F_db_fetch_array($rsm)) {
                                $subjids .= $msm['subject_id'].',';
                            }
                        } else {
                            F_display_db_error();
                        }
                    } else {
                        $subjids .= intval($subid).',';
                    }
                }
                $subjids = substr($subjids, 0, -1);
                $subject_id = explode(',', $subjids);
                $subjids = '('.$subjids.')';
                $sql_answer_position = '';
                $sql_questions_position = '';
                if (!$test_random_questions_order and ($test_questions_order_mode == 0)) {
                    $sql_questions_position = ' AND question_position>0';
                }
                if (!$test_random_answers_order and ($test_answers_order_mode == 0)) {
                    $sql_answer_position = ' AND answer_position>0';
                }
                // check here if the selected number of questions are available for the current set
                // NOTE: if the same subject is used in multiple sets this control may fail.
                $sqlq = 'SELECT COUNT(*) AS numquestions FROM '.K_TABLE_QUESTIONS.'';
                $sqlq .= ' WHERE question_subject_id IN '.$subjids.'
					AND question_difficulty='.$tsubset_difficulty.'
					AND question_enabled=\'1\'';
                if ($tsubset_type > 0) {
                    $sqlq .= ' AND question_type='.$tsubset_type.'';
                }
                if ($tsubset_type == 1) {
                    // single question (MCSA)
                    // check if the selected question has enough answers
                    $sqlq .= ' AND question_id IN (
							SELECT answer_question_id
							FROM '.K_TABLE_ANSWERS.'
							WHERE answer_enabled=\'1\' AND answer_isright=\'1\'';
                    $sqlq .= $sql_answer_position;
                    $sqlq .= ' GROUP BY answer_question_id
							HAVING (COUNT(answer_id)>0))';
                    $sqlq .= ' AND question_id IN (
							SELECT answer_question_id
							FROM '.K_TABLE_ANSWERS.'
							WHERE answer_enabled=\'1\'
							AND answer_isright=\'0\'';
                    $sqlq .= $sql_answer_position;
                    $sqlq .= ' GROUP BY answer_question_id';
                    if ($tsubset_answers > 0) {
                        $sqlq .= ' HAVING (COUNT(answer_id)>='.($tsubset_answers-1).')';
                    }
                    $sqlq .= ' )';
                } elseif ($tsubset_type == 2) {
                    // multiple question (MCMA)
                    // check if the selected question has enough answers
                    $sqlq .= ' AND question_id IN (
							SELECT answer_question_id
							FROM '.K_TABLE_ANSWERS.'
							WHERE answer_enabled=\'1\'';
                    $sqlq .= $sql_answer_position;
                    $sqlq .= ' GROUP BY answer_question_id';
                    if ($tsubset_answers > 0) {
                        $sqlq .= ' HAVING (COUNT(answer_id)>='.$tsubset_answers.')';
                    }
                    $sqlq .= ' )';
                } elseif ($tsubset_type == 4) {
                    // ordering question
                    // check if the selected question has enough answers
                    $sqlq .= ' AND question_id IN (
							SELECT answer_question_id
							FROM '.K_TABLE_ANSWERS.'
							WHERE answer_enabled=\'1\'
							AND answer_position>0
							GROUP BY answer_question_id
							HAVING (COUNT(answer_id)>1))';
                }
                $sqlq .= $sql_questions_position;
                if (K_DATABASE_TYPE == 'ORACLE') {
                    $sqlq = 'SELECT * FROM ('.$sqlq.') WHERE rownum <= '.$tsubset_quantity.'';
                } else {
                    $sqlq .= ' LIMIT '.$tsubset_quantity.'';
                }
                $numofrows = 0;
                if ($rq = F_db_query($sqlq, $db)) {
                    if ($mq = F_db_fetch_array($rq)) {
                        $numofrows = $mq['numquestions'];
                    }
                } else {
                    F_display_db_error();
                }
                if ($numofrows < $tsubset_quantity) {
                    F_print_error('WARNING', $l['m_unavailable_questions']);
                    break;
                }
                if (!empty($subject_id)) {
                    // insert new subject
                    $sql = 'INSERT INTO '.K_TABLE_TEST_SUBJSET.' (tsubset_test_id,
						tsubset_type,
						tsubset_difficulty,
						tsubset_quantity,
						tsubset_answers
						) VALUES (
						\''.$test_id.'\',
						\''.$tsubset_type.'\',
						\''.$tsubset_difficulty.'\',
						\''.$tsubset_quantity.'\',
						\''.$tsubset_answers.'\'
						)';
                    if (!$r = F_db_query($sql, $db)) {
                        F_display_db_error(false);
                    } else {
                        $tsubset_id = F_db_insert_id($db, K_TABLE_TEST_SUBJSET, 'tsubset_id');
                        // add selected subject_id
                        foreach ($subject_id as $subid) {
                            $sql = 'INSERT INTO '.K_TABLE_SUBJECT_SET.' (
								subjset_tsubset_id,
								subjset_subject_id
								) VALUES (
								\''.$tsubset_id.'\',
								\''.$subid.'\'
								)';
                            if (!$r = F_db_query($sql, $db)) {
                                F_display_db_error(false);
                            }
                        }
                    }
                }
            }
        }
        break;
    }

    case 'delete':{
        F_stripslashes_formfields();        // ask confirmation
        F_print_error('WARNING', $l['m_delete_confirm_test']);
        ?>
        <div class="confirmbox">
        <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_delete">
        <div>
        <input type="hidden" name="test_id" id="test_id" value="<?php echo $test_id; ?>" />
        <input type="hidden" name="test_name" id="test_name" value="<?php echo $test_name; ?>" />
        <?php
        F_submit_button('forcedelete', $l['w_delete'], $l['h_delete']);
        F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
        ?>
        </div>
        </form>
        </div>
        <?php
        break;
    }

    case 'forcedelete':{
        F_stripslashes_formfields(); // Delete
        if ($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
            // delete test
            $sql = 'DELETE FROM '.K_TABLE_TESTS.' WHERE test_id='.$test_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                $test_id = false;
                F_print_error('MESSAGE', $test_name.': '.$l['m_deleted']);
            }
        }
        break;
    }

    case 'update':{ // Update
        // check if the confirmation chekbox has been selected
        if (!isset($_REQUEST['confirmupdate']) or ($_REQUEST['confirmupdate'] != 1)) {
            F_print_error('WARNING', $l['m_form_missing_fields'].': '.$l['w_confirm'].' &rarr; '.$l['w_update']);
            $formstatus = false;
            F_stripslashes_formfields();
            break;
        }
        if ($formstatus = F_check_form_fields()) {
            // check referential integrity (NOTE: mysql do not support "ON UPDATE" constraint)
            if (!F_check_unique(K_TABLE_TEST_USER, 'testuser_test_id='.$test_id.'')) {
                F_print_error('WARNING', $l['m_update_restrict']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            // check if name is unique
            if (!F_check_unique(K_TABLE_TESTS, 'test_name=\''.$test_name.'\'', 'test_id', $test_id)) {
                F_print_error('WARNING', $l['m_duplicate_name']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            if (!empty($new_test_password)) {
                $test_password = getPasswordHash($new_test_password);
            }
            if ($test_score_threshold > $test_max_score) {
                $test_score_threshold = 0.6 * $test_max_score;
            }
            $sql = 'UPDATE '.K_TABLE_TESTS.' SET
				test_name=\''.F_escape_sql($db, $test_name).'\',
				test_description=\''.F_escape_sql($db, $test_description).'\',
				test_begin_time='.F_empty_to_null($test_begin_time).',
				test_end_time='.F_empty_to_null($test_end_time).',
				test_duration_time=\''.$test_duration_time.'\',
				test_ip_range=\''.F_escape_sql($db, $test_ip_range).'\',
				test_results_to_users=\''.intval($test_results_to_users).'\',
				test_report_to_users=\''.intval($test_report_to_users).'\',
				test_score_right=\''.$test_score_right.'\',
				test_score_wrong=\''.$test_score_wrong.'\',
				test_score_unanswered=\''.$test_score_unanswered.'\',
				test_max_score=\''.$test_max_score.'\',
				test_score_threshold=\''.$test_score_threshold.'\',
				test_random_questions_select=\''.intval($test_random_questions_select).'\',
				test_random_questions_order=\''.intval($test_random_questions_order).'\',
				test_questions_order_mode=\''.$test_questions_order_mode.'\',
				test_random_answers_select=\''.intval($test_random_answers_select).'\',
				test_random_answers_order=\''.intval($test_random_answers_order).'\',
				test_answers_order_mode=\''.$test_answers_order_mode.'\',
				test_comment_enabled=\''.intval($test_comment_enabled).'\',
				test_menu_enabled=\''.intval($test_menu_enabled).'\',
				test_noanswer_enabled=\''.intval($test_noanswer_enabled).'\',
				test_mcma_radio=\''.intval($test_mcma_radio).'\',
				test_repeatable=\''.intval($test_repeatable).'\',
				test_mcma_partial_score=\''.intval($test_mcma_partial_score).'\',
				test_logout_on_timeout=\''.intval($test_logout_on_timeout).'\',
				test_password='.F_empty_to_null($test_password).'
				WHERE test_id='.$test_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                F_print_error('MESSAGE', $l['m_updated']);
            }

            // delete previous groups
            $sql = 'DELETE FROM '.K_TABLE_TEST_GROUPS.'
				WHERE tstgrp_test_id='.$test_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            }
            // update authorized groups
            if (!empty($user_groups)) {
                foreach ($user_groups as $group_id) {
                    $sql = 'INSERT INTO '.K_TABLE_TEST_GROUPS.' (
						tstgrp_test_id,
						tstgrp_group_id
						) VALUES (
						\''.$test_id.'\',
						\''.intval($group_id).'\'
						)';
                    if (!$r = F_db_query($sql, $db)) {
                        F_display_db_error(false);
                    }
                }
            }

            // delete previous SSL certificates
            $sql = 'DELETE FROM '.K_TABLE_TEST_SSLCERTS.'
				WHERE tstssl_test_id='.$test_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            }
            // update authorized SSL certificates
            if (!empty($sslcerts)) {
                foreach ($sslcerts as $ssl_id) {
                    $sql = 'INSERT INTO '.K_TABLE_TEST_SSLCERTS.' (
						tstssl_test_id,
						tstssl_ssl_id
						) VALUES (
						\''.$test_id.'\',
						\''.intval($ssl_id).'\'
						)';
                    if (!$r = F_db_query($sql, $db)) {
                        F_display_db_error(false);
                    }
                }
            }
        }
        break;
    }

    case 'add':{ // Add
        if ($formstatus = F_check_form_fields()) {
            // check if name is unique
            if (!F_check_unique(K_TABLE_TESTS, 'test_name=\''.F_escape_sql($db, $test_name).'\'')) {
                F_print_error('WARNING', $l['m_duplicate_name']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            if (isset($test_id) and ($test_id > 0)) {
                // save previous test_id.
                $old_test_id = $test_id;
            }
            if (!empty($new_test_password)) {
                $test_password = getPasswordHash($new_test_password);
            }
            $sql = 'INSERT INTO '.K_TABLE_TESTS.' (
			test_name,
				test_description,
				test_begin_time,
				test_end_time,
				test_duration_time,
				test_ip_range,
				test_results_to_users,
				test_report_to_users,
				test_score_right,
				test_score_wrong,
				test_score_unanswered,
				test_max_score,
				test_user_id,
				test_score_threshold,
				test_random_questions_select,
				test_random_questions_order,
				test_questions_order_mode,
				test_random_answers_select,
				test_random_answers_order,
				test_answers_order_mode,
				test_comment_enabled,
				test_menu_enabled,
				test_noanswer_enabled,
				test_mcma_radio,
				test_repeatable,
				test_mcma_partial_score,
				test_logout_on_timeout,
				test_password
				) VALUES (
				\''.F_escape_sql($db, $test_name).'\',
				\''.F_escape_sql($db, $test_description).'\',
				'.F_empty_to_null($test_begin_time).',
				'.F_empty_to_null($test_end_time).',
				\''.$test_duration_time.'\',
				\''.F_escape_sql($db, $test_ip_range).'\',
				\''.intval($test_results_to_users).'\',
				\''.intval($test_report_to_users).'\',
				\''.$test_score_right.'\',
				\''.$test_score_wrong.'\',
				\''.$test_score_unanswered.'\',
				\''.$test_max_score.'\',
				\''.intval($_SESSION['session_user_id']).'\',
				\''.$test_score_threshold.'\',
				\''.intval($test_random_questions_select).'\',
				\''.intval($test_random_questions_order).'\',
				\''.$test_questions_order_mode.'\',
				\''.intval($test_random_answers_select).'\',
				\''.intval($test_random_answers_order).'\',
				\''.$test_answers_order_mode.'\',
				\''.intval($test_comment_enabled).'\',
				\''.intval($test_menu_enabled).'\',
				\''.intval($test_noanswer_enabled).'\',
				\''.intval($test_mcma_radio).'\',
				\''.intval($test_repeatable).'\',
				\''.intval($test_mcma_partial_score).'\',
				\''.intval($test_logout_on_timeout).'\',
				'.F_empty_to_null($test_password).'
				)';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                $test_id = F_db_insert_id($db, K_TABLE_TESTS, 'test_id');
            }
            // add authorized user's groups
            if (!empty($user_groups)) {
                foreach ($user_groups as $group_id) {
                    $sql = 'INSERT INTO '.K_TABLE_TEST_GROUPS.' (
						tstgrp_test_id,
						tstgrp_group_id
						) VALUES (
						\''.$test_id.'\',
						\''.intval($group_id).'\'
						)';
                    if (!$r = F_db_query($sql, $db)) {
                        F_display_db_error(false);
                    }
                }
            }

            // update authorized SSL certificates
            if (!empty($sslcerts)) {
                foreach ($sslcerts as $ssl_id) {
                    $sql = 'INSERT INTO '.K_TABLE_TEST_SSLCERTS.' (
						tstssl_test_id,
						tstssl_ssl_id
						) VALUES (
						\''.$test_id.'\',
						\''.intval($ssl_id).'\'
						)';
                    if (!$r = F_db_query($sql, $db)) {
                        F_display_db_error(false);
                    }
                }
            }

            if (isset($old_test_id) and ($old_test_id > 0)) {
                // copy here previous selected questions to this new test
                $sql = 'SELECT *
					FROM '.K_TABLE_TEST_SUBJSET.'
					WHERE tsubset_test_id=\''.$old_test_id.'\'';
                if ($r = F_db_query($sql, $db)) {
                    while ($m = F_db_fetch_array($r)) {
                        // insert new subject
                        $sqlu = 'INSERT INTO '.K_TABLE_TEST_SUBJSET.' (
							tsubset_test_id,
							tsubset_type,
							tsubset_difficulty,
							tsubset_quantity,
							tsubset_answers
							) VALUES (
							\''.$test_id.'\',
							\''.$m['tsubset_type'].'\',
							\''.$m['tsubset_difficulty'].'\',
							\''.$m['tsubset_quantity'].'\',
							\''.$m['tsubset_answers'].'\'
							)';
                        if (!$ru = F_db_query($sqlu, $db)) {
                            F_display_db_error();
                        } else {
                            $tsubset_id = F_db_insert_id($db, K_TABLE_TEST_SUBJSET, 'tsubset_id');
                            $sqls = 'SELECT *
								FROM '.K_TABLE_SUBJECT_SET.'
								WHERE subjset_tsubset_id=\''.$m['tsubset_id'].'\'';
                            if ($rs = F_db_query($sqls, $db)) {
                                while ($ms = F_db_fetch_array($rs)) {
                                    $sqlp = 'INSERT INTO '.K_TABLE_SUBJECT_SET.' (
										subjset_tsubset_id,
										subjset_subject_id
										) VALUES (
										\''.$tsubset_id.'\',
										\''.$ms['subjset_subject_id'].'\'
										)';
                                    if (!$rp = F_db_query($sqlp, $db)) {
                                        F_display_db_error();
                                    }
                                }
                            } else {
                                F_display_db_error();
                            }
                        }
                    }
                } else {
                    F_display_db_error();
                }
            }
        }
        break;
    }

    case 'clear':{ // Clear form fields
        $test_name = '';
        $test_description = '';
        $test_begin_time = date(K_TIMESTAMP_FORMAT);
        $test_end_time = date(K_TIMESTAMP_FORMAT, time() + K_SECONDS_IN_DAY);
        $test_duration_time = 60;
        $test_ip_range = '*.*.*.*';
        $test_results_to_users = false;
        $test_report_to_users = false;
        $test_score_right = 1;
        $test_score_wrong = 0;
        $test_score_unanswered = 0;
        $test_max_score = 0;
        $test_score_threshold = 0;
        $test_random_questions_select = true;
        $test_random_questions_order = true;
        $test_questions_order_mode = 0;
        $test_random_answers_select = true;
        $test_random_answers_order = true;
        $test_answers_order_mode = 0;
        $test_comment_enabled = true;
        $test_menu_enabled = true;
        $test_noanswer_enabled = true;
        $test_mcma_radio = true;
        $test_repeatable = false;
        $test_mcma_partial_score = true;
        $test_logout_on_timeout = false;
        $test_password = '';
        break;
    }

    default :{
        break;
    }
} //end of switch

// --- Initialize variables

if (!isset($test_num) or (!empty($test_num))) {
    $test_num = 1; // default number of PDF tests to generate
}

if ($formstatus) {
    if ($menu_mode != 'clear') {
        if (!isset($test_id) or empty($test_id)) {
            $test_id = 0;
            $test_name = '';
            $test_description = '';
            $test_begin_time = date(K_TIMESTAMP_FORMAT);
            $test_end_time = date(K_TIMESTAMP_FORMAT, time() + K_SECONDS_IN_DAY);
            $test_duration_time = 60;
            $test_ip_range = '*.*.*.*';
            $test_results_to_users = false;
            $test_report_to_users = false;
            $test_score_right = 1;
            $test_score_wrong = 0;
            $test_score_unanswered = 0;
            $test_max_score = 0;
            $test_score_threshold = 0;
            $test_random_questions_select = true;
            $test_random_questions_order = true;
            $test_questions_order_mode = 0;
            $test_random_answers_select = true;
            $test_random_answers_order = true;
            $test_answers_order_mode = 0;
            $test_comment_enabled = true;
            $test_menu_enabled = true;
            $test_noanswer_enabled = true;
            $test_mcma_radio = true;
            $test_repeatable = false;
            $test_mcma_partial_score = true;
            $test_logout_on_timeout = false;
            $test_password = '';
        } else {
            $sql = 'SELECT * FROM '.K_TABLE_TESTS.' WHERE test_id='.$test_id.' LIMIT 1';
            if ($r = F_db_query($sql, $db)) {
                if ($m = F_db_fetch_array($r)) {
                    $test_id = $m['test_id'];
                    $test_name = $m['test_name'];
                    $test_description = $m['test_description'];
                    $test_begin_time = $m['test_begin_time'];
                    $test_end_time = $m['test_end_time'];
                    $test_duration_time = $m['test_duration_time'];
                    $test_ip_range = $m['test_ip_range'];
                    $test_results_to_users = F_getBoolean($m['test_results_to_users']);
                    $test_report_to_users = F_getBoolean($m['test_report_to_users']);
                    $test_score_right = $m['test_score_right'];
                    $test_score_wrong = $m['test_score_wrong'];
                    $test_score_unanswered = $m['test_score_unanswered'];
                    $test_max_score = $m['test_max_score'];
                    $test_score_threshold = $m['test_score_threshold'];
                    $test_random_questions_select = F_getBoolean($m['test_random_questions_select']);
                    $test_random_questions_order = F_getBoolean($m['test_random_questions_order']);
                    $test_questions_order_mode = intval($m['test_questions_order_mode']);
                    $test_random_answers_select = F_getBoolean($m['test_random_answers_select']);
                    $test_random_answers_order = F_getBoolean($m['test_random_answers_order']);
                    $test_answers_order_mode = intval($m['test_answers_order_mode']);
                    $test_comment_enabled = F_getBoolean($m['test_comment_enabled']);
                    $test_menu_enabled = F_getBoolean($m['test_menu_enabled']);
                    $test_noanswer_enabled = F_getBoolean($m['test_noanswer_enabled']);
                    $test_mcma_radio = F_getBoolean($m['test_mcma_radio']);
                    $test_repeatable = F_getBoolean($m['test_repeatable']);
                    $test_mcma_partial_score = F_getBoolean($m['test_mcma_partial_score']);
                    $test_logout_on_timeout = F_getBoolean($m['test_logout_on_timeout']);
                    $test_password = $m['test_password'];
                } else {
                    $test_name = '';
                    $test_description = '';
                    $test_begin_time = date(K_TIMESTAMP_FORMAT);
                    $test_end_time = date(K_TIMESTAMP_FORMAT, time() + K_SECONDS_IN_DAY);
                    $test_duration_time = 60;
                    $test_ip_range = '*.*.*.*';
                    $test_results_to_users = false;
                    $test_report_to_users = false;
                    $test_score_right = 1;
                    $test_score_wrong = 0;
                    $test_score_unanswered = 0;
                    $test_max_score = 0;
                    $test_score_threshold = 0;
                    $test_random_questions_select = true;
                    $test_random_questions_order = true;
                    $test_questions_order_mode = 0;
                    $test_random_answers_select = true;
                    $test_random_answers_order = true;
                    $test_answers_order_mode = 0;
                    $test_comment_enabled = true;
                    $test_menu_enabled = true;
                    $test_noanswer_enabled = true;
                    $test_mcma_radio = true;
                    $test_repeatable = false;
                    $test_mcma_partial_score = true;
                    $test_logout_on_timeout = false;
                    $test_password = '';
                }
            } else {
                F_display_db_error();
            }
        }
    }
}
$millennium = substr(date('Y'), 0, 1);

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_testeditor">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="test_id">'.$l['w_test'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="test_id" id="test_id" size="0" onchange="document.getElementById(\'form_testeditor\').submit()" title="'.$l['h_test'].'">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($test_id == 0) {
    echo ' selected="selected"';
}
echo '>+</option>'.K_NEWLINE;
$sql = F_select_tests_sql();
if ($r = F_db_query($sql, $db)) {
    $countitem = 1;
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['test_id'].'"';
        if ($m['test_id'] == $test_id) {
            echo ' selected="selected"';
            $test_fieldset_name = ''.substr($m['test_begin_time'], 0, 10).' '.htmlspecialchars($m['test_name'], ENT_NOQUOTES, $l['a_meta_charset']).'';
        }
        echo '>'.$countitem.'. ';
        if (substr($m['test_end_time'], 0, 1) < $millennium) {
            echo '* ';
        }
        echo substr($m['test_begin_time'], 0, 10).' '.htmlspecialchars($m['test_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
        $countitem++;
    }
} else {
    echo '</select></span></div>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;

// link for user selection popup
$jsaction = 'selectWindow=window.open(\'tce_select_tests_popup.php?cid=test_id\', \'selectWindow\', \'dependent, height=600, width=800, menubar=no, resizable=yes, scrollbars=yes, status=no, toolbar=no\');return false;';
echo '<a href="#" onclick="'.$jsaction.'" class="xmlbutton" title="'.$l['w_select'].'">...</a>';

echo '</span>'.K_NEWLINE;
echo '<br /><br />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

echo '<fieldset>'.K_NEWLINE;
echo '<legend>'.$l['w_test'].'</legend>'.K_NEWLINE;

echo getFormRowTextInput('test_name', $l['w_name'], $l['h_test_name'], '', $test_name, '', 255, false, false, false);
echo getFormRowTextBox('test_description', $l['w_description'], $l['h_test_description'], $test_description, false);
echo getFormRowTextInput('test_begin_time', $l['w_time_begin'], $l['w_time_begin'].' '.$l['w_datetime_format'], '', $test_begin_time, '', 19, false, true, false);
echo getFormRowTextInput('test_end_time', $l['w_time_end'], $l['w_time_end'].' '.$l['w_datetime_format'], '', $test_end_time, '', 19, false, true, false);
echo getFormRowTextInput('test_duration_time', $l['w_test_time'], $l['h_test_time'], '['.$l['w_minutes'].']', $test_duration_time, '^([0-9]*)$', 20, false, false, false);
echo getFormRowTextInput('test_ip_range', $l['w_ip_range'], $l['h_ip_range'], '', $test_ip_range, '^([0-9a-fA-F,\:\.\*-]*)$', 255, false, false, false);

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="user_groups">'.$l['w_groups'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="user_groups[]" id="user_groups" size="5" multiple="multiple">'.K_NEWLINE;
//$sql = F_user_group_select_sql();
$sql = 'SELECT * FROM '.K_TABLE_GROUPS.' ORDER BY group_name';
if ($r = F_db_query($sql, $db)) {
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['group_id'].'"';
        if (isset($test_id) and ($test_id > 0) and (F_isTestOnGroup($test_id, $m['group_id']))) {
            echo ' selected="selected"';
        }
        echo '>'.htmlspecialchars($m['group_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
    }
} else {
    echo '</select></span></div>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="sslcerts">'.$l['w_sslcerts'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="sslcerts[]" id="sslcerts" size="5" multiple="multiple">'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_SSLCERTS.' ORDER BY ssl_name';
if ($r = F_db_query($sql, $db)) {
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['ssl_id'].'"';
        if (isset($test_id) and ($test_id > 0) and (F_isTestOnSSLCerts($test_id, $m['ssl_id']))) {
            echo ' selected="selected"';
        }
        echo '>'.htmlspecialchars($m['ssl_name'].' ('.substr($m['ssl_end_date'], 0, 10).')', ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
    }
} else {
    echo '</select></span></div>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormRowTextInput('test_score_right', $l['w_score_right'], $l['h_score_right'], '', $test_score_right, '^([0-9\+\-]*)([\.]?)([0-9]*)$', 20, false, false, false);
echo getFormRowTextInput('test_score_wrong', $l['w_score_wrong'], $l['h_score_wrong'], '', $test_score_wrong, '^([0-9\+\-]*)([\.]?)([0-9]*)$', 20, false, false, false);
echo getFormRowTextInput('test_score_unanswered', $l['w_score_unanswered'], $l['h_score_unanswered'], '', $test_score_unanswered, '^([0-9\+\-]*)([\.]?)([0-9]*)$', 20, false, false, false);
echo getFormRowTextInput('test_score_threshold', $l['w_test_score_threshold'], $l['h_test_score_threshold'], '', $test_score_threshold, '^([0-9\+\-]*)([\.]?)([0-9]*)$', 20, false, false, false);

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="test_random_questions_select">'.$l['w_random_questions'].':</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="checkbox" name="test_random_questions_select" id="test_random_questions_select" value="1"';
if ($test_random_questions_select) {
    echo ' checked="checked"';
}
echo ' onclick="JF_check_random_boxes()"';
echo ' title="'.$l['h_random_questions'].'" />';
echo ' <label for="test_random_questions_select">'.$l['w_select'].'</label>'.K_NEWLINE;

echo ' <input type="checkbox" name="test_random_questions_order" id="test_random_questions_order" value="1"';
if ($test_random_questions_order) {
    echo ' checked="checked"';
}
echo ' onclick="JF_check_random_boxes()"';
echo ' title="'.$l['w_order'].'" />';
echo ' <label for="test_random_questions_order">'.$l['w_order'].'</label>'.K_NEWLINE;

echo '<span id="select_questions_order_mode" style="visibility:visible;">'.K_NEWLINE;
echo ' | <label for="test_questions_order_mode">'.$l['w_order_by'].'</label>'.K_NEWLINE;
echo ' <select name="test_questions_order_mode" id="test_questions_order_mode" size="1" title="'.$l['h_questions_order_mode'].'">'.K_NEWLINE;
foreach ($qordmode as $ok => $ov) {
        echo '<option value="'.$ok.'"';
    if ($test_questions_order_mode == $ok) {
        echo ' selected="selected"';
    }
        echo '>'.htmlspecialchars($ov, ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;

echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;


echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="test_random_answers_select">'.$l['w_random_answers'].':</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="checkbox" name="test_random_answers_select" id="test_random_answers_select" value="1"';
if ($test_random_answers_select) {
    echo ' checked="checked"';
}
echo ' onclick="JF_check_random_boxes()"';
echo ' title="'.$l['h_random_answers'].'" />';
echo ' <label for="test_random_answers_select">'.$l['w_select'].'</label>'.K_NEWLINE;

echo ' <input type="checkbox" name="test_random_answers_order" id="test_random_answers_order" value="1"';
if ($test_random_answers_order) {
    echo ' checked="checked"';
}
echo ' onclick="JF_check_random_boxes()"';
echo ' title="'.$l['w_order'].'" />';
echo ' <label for="test_random_answers_order">'.$l['w_order'].'</label>'.K_NEWLINE;

echo '<span id="select_answers_order_mode" style="visibility:visible;">'.K_NEWLINE;
echo ' | <label for="test_answers_order_mode">'.$l['w_order_by'].'</label>'.K_NEWLINE;
echo ' <select name="test_answers_order_mode" id="test_answers_order_mode" size="1" title="'.$l['h_answers_order_mode'].'">'.K_NEWLINE;
foreach ($aordmode as $ok => $ov) {
        echo '<option value="'.$ok.'"';
    if ($test_answers_order_mode == $ok) {
        echo ' selected="selected"';
    }
        echo '>'.htmlspecialchars($ov, ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;

echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormRowCheckBox('test_mcma_radio', $l['w_mcma_radio'], '', '', 1, $test_mcma_radio, false);
echo getFormRowCheckBox('test_mcma_partial_score', $l['w_mcma_partial_score'], '', '', 1, $test_mcma_partial_score, false);
echo getFormRowCheckBox('test_noanswer_enabled', $l['w_enable_noanswer'], '', '', 1, $test_noanswer_enabled, false);
echo getFormRowCheckBox('test_menu_enabled', $l['w_enable_menu'], '', '', 1, $test_menu_enabled, false);
echo getFormRowCheckBox('test_comment_enabled', $l['w_enable_comment'], '', '', 1, $test_comment_enabled, false);
echo getFormRowCheckBox('test_results_to_users', $l['w_results_to_users'], '', '', 1, $test_results_to_users, false);
echo getFormRowCheckBox('test_report_to_users', $l['w_report_to_users'], '', '', 1, $test_report_to_users, false);
echo getFormRowCheckBox('test_repeatable', $l['w_repeatable'], '', '', 1, $test_repeatable, false);
echo getFormRowCheckBox('test_logout_on_timeout', $l['w_logout_on_timeout'], '', '', 1, $test_logout_on_timeout, false);

echo getFormRowTextInput('new_test_password', $l['w_password'], $l['h_test_password'], ' ('.$l['d_password_lenght'].')', '', K_USRREG_PASSWORD_RE, 255, false, false, true);

echo '<div class="row">'.K_NEWLINE;
echo '<br />'.K_NEWLINE;
echo '<input type="hidden" name="test_password" id="test_password" value="'.$test_password.'" />'.K_NEWLINE;

// show buttons by case


if (isset($test_id) and ($test_id > 0)) {
    echo '<span style="background-color:#999999;">';
    echo '<input type="checkbox" name="confirmupdate" id="confirmupdate" value="1" title="confirm &rarr; update" />';
    F_submit_button('update', $l['w_update'], $l['h_update']);
    echo '</span>';
}
F_submit_button('add', $l['w_add'], $l['h_add']);
if (isset($test_id) and ($test_id > 0)) {
    F_submit_button('delete', $l['w_delete'], $l['h_delete']);
    if (substr($test_end_time, 0, 1) < $millennium) {
        F_submit_button('unlock', $l['w_unlock'], $l['w_unlock']);
    } else {
        F_submit_button('lock', $l['w_lock'], $l['w_lock']);
    }
}
F_submit_button('clear', $l['w_clear'], $l['h_clear']);

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="test_name,test_description,test_ip_range,test_duration_time,test_score_right" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'].','.$l['w_description'].','.$l['w_ip_range'].','.$l['w_test_time'].','.$l['w_score_right'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '<br /><br />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '</fieldset>'.K_NEWLINE;

// display a list of selected subject_id (topics)
if (isset($test_id) and ($test_id > 0)) {
    echo '<div class="row"><br /></div>'.K_NEWLINE;

    echo '<fieldset>'.K_NEWLINE;
    echo '<legend>'.$l['w_questions'].'</legend>'.K_NEWLINE;

    echo '<div class="row">'.K_NEWLINE;
    echo '<span class="label">&nbsp;</span>'.K_NEWLINE;
    echo '<span class="formw">'.$test_fieldset_name.'</span>'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;

    echo '<div class="row">'.K_NEWLINE;
    echo '<span class="label">'.K_NEWLINE;
    echo '<label for="subject_id">'.$l['w_subjects'].'</label>'.K_NEWLINE;
    echo '</span>'.K_NEWLINE;
    echo '<span class="formw">'.K_NEWLINE;
    echo '<select name="subject_id[]" id="subject_id" size="10" multiple="multiple" title="'.$l['h_subjects'].'">'.K_NEWLINE;
    // select subject_id
    $sql = F_select_module_subjects_sql('module_enabled=\'1\' AND subject_enabled=\'1\'');
    if ($r = F_db_query($sql, $db)) {
        $prev_module_id = 0;
        while ($m = F_db_fetch_array($r)) {
            if ($m['module_id'] != $prev_module_id) {
                $prev_module_id = $m['module_id'];
                echo '<option value="#'.$m['module_id'].'" style="background-color:#DDEEFF;font-weight:bold">* '.htmlspecialchars($m['module_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
            }
            echo '<option value="'.$m['subject_id'].'"';
            if (in_array($m['subject_id'], $subject_id)) {
                echo ' selected="selected"';
            }
            echo '>&nbsp;&nbsp;&nbsp;'.htmlspecialchars($m['subject_name'], ENT_NOQUOTES, $l['a_meta_charset']).' [';
            // count available questions for each type
            $qstat = '';
            $sqln = 'SELECT question_type, question_difficulty, COUNT(*) as numquestions
				FROM '.K_TABLE_QUESTIONS.'
				WHERE question_subject_id='.$m['subject_id'].'
					AND question_enabled=\'1\'
				GROUP BY question_type, question_difficulty';
            if ($rn = F_db_query($sqln, $db)) {
                while ($mn = F_db_fetch_array($rn)) {
                    $qstat .= ' '.$mn['numquestions'].$qtype[($mn['question_type']-1)].$mn['question_difficulty'];
                    // count min and max alternative answers
                    $amin = 999999;
                    $amax = 0;
                    $sqla = 'SELECT question_id, COUNT(*) as numanswers
						FROM '.K_TABLE_QUESTIONS.','.K_TABLE_ANSWERS.'
						WHERE answer_question_id=question_id
							AND question_subject_id='.$m['subject_id'].'
							AND question_type='.$mn['question_type'].'
							AND question_difficulty='.$mn['question_difficulty'].'
							AND question_enabled=\'1\'
							AND answer_enabled=\'1\'
						GROUP BY question_id';
                    if ($ra = F_db_query($sqla, $db)) {
                        while ($ma = F_db_fetch_array($ra)) {
                            if ($ma['numanswers'] < $amin) {
                                $amin = $ma['numanswers'];
                            }
                            if ($ma['numanswers'] > $amax) {
                                $amax = $ma['numanswers'];
                            }
                        }
                    } else {
                        F_display_db_error();
                    }
                    if ($amin == 999999) {
                        $amin = 0;
                    }
                    // display minimum alternative answers
                    $qstat .= ':'.$amin;
                    if ($amax > $amin) {
                        $qstat .= '-'.$amax;
                    }
                }
            } else {
                F_display_db_error();
            }
            echo $qstat.' ]</option>'.K_NEWLINE;
        }
    } else {
        echo '</select></span></div>'.K_NEWLINE;
        F_display_db_error();
    }

    echo '</select>'.K_NEWLINE;
    echo '</span>'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;

    echo getFormRowTextInput('tsubset_quantity', $l['w_num_questions'], $l['h_num_questions'], '', $tsubset_quantity, '^([0-9]*)$', 20, false, false, false);

    echo '<div class="row">'.K_NEWLINE;
    echo '<span class="label">'.K_NEWLINE;
    echo '<label for="tsubset_type">'.$l['w_type'].'</label>'.K_NEWLINE;
    echo '</span>'.K_NEWLINE;
    echo '<span class="formw">'.K_NEWLINE;
    echo '<select name="tsubset_type" id="tsubset_type" size="0" title="'.$l['h_question_type'].'">'.K_NEWLINE;
    echo '<option value="0"';
    if ($tsubset_type == 0) {
        echo ' selected="selected"';
    }
    echo '>*** '.$l['w_all'].' ***</option>'.K_NEWLINE;
    echo '<option value="1"';
    if ($tsubset_type == 1) {
        echo ' selected="selected"';
    }
    echo '>'.$l['w_single_answer'].'</option>'.K_NEWLINE;
    echo '<option value="2"';
    if ($tsubset_type == 2) {
        echo ' selected="selected"';
    }
    echo '>'.$l['w_multiple_answers'].'</option>'.K_NEWLINE;
    echo '<option value="3"';
    if ($tsubset_type == 3) {
        echo ' selected="selected"';
    }
    echo '>'.$l['w_free_answer'].'</option>'.K_NEWLINE;
    echo '<option value="4"';
    if ($tsubset_type == 4) {
        echo ' selected="selected"';
    }
    echo '>'.$l['w_ordering_answer'].'</option>'.K_NEWLINE;
    echo '</select>'.K_NEWLINE;
    echo '</span>'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;

    echo '<div class="row">'.K_NEWLINE;
    echo '<span class="label">'.K_NEWLINE;
    echo '<label for="tsubset_difficulty">'.$l['w_question_difficulty'].'</label>'.K_NEWLINE;
    echo '</span>'.K_NEWLINE;
    echo '<span class="formw">'.K_NEWLINE;
    echo '<select name="tsubset_difficulty" id="tsubset_difficulty" size="0" title="'.$l['h_question_difficulty'].'">'.K_NEWLINE;
    for ($i = 0; $i <= K_QUESTION_DIFFICULTY_LEVELS; ++$i) {
        echo '<option value="'.$i.'"';
        if ($i == $tsubset_difficulty) {
            echo ' selected="selected"';
        }
        echo '>'.$i.'</option>'.K_NEWLINE;
    }
    echo '</select>'.K_NEWLINE;
    echo '</span>'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;

    echo getFormRowTextInput('tsubset_answers', $l['w_num_answers'], $l['h_num_answers'], '', $tsubset_answers, '^([0-9]*)$', 20, false, false, false);

    echo '<div class="row">'.K_NEWLINE;
    echo '<span class="label">&nbsp;</span>'.K_NEWLINE;
    echo '<span class="formw">'.K_NEWLINE;
    F_submit_button("addquestion", $l['w_add_questions'], $l['h_add_questions']);
    echo '</span>'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;

    echo '<div class="rowl" title="'.$l['h_subjects'].'">'.K_NEWLINE;
    echo '<br />'.K_NEWLINE;
    echo '<div class="preview">'.K_NEWLINE;
    $subjlist = '';
    $sql = 'SELECT * FROM '.K_TABLE_TEST_SUBJSET.'
		WHERE tsubset_test_id=\''.$test_id.'\'
		ORDER BY tsubset_id';
    if ($r = F_db_query($sql, $db)) {
        while ($m = F_db_fetch_array($r)) {
            $subjlist .= '<li>';
            $subjects_list = '';
            $sqls = 'SELECT subject_id,subject_name
				FROM '.K_TABLE_SUBJECTS.', '.K_TABLE_SUBJECT_SET.'
				WHERE subject_id=subjset_subject_id
					AND subjset_tsubset_id=\''.$m['tsubset_id'].'\'
				ORDER BY subject_name';
            if ($rs = F_db_query($sqls, $db)) {
                while ($ms = F_db_fetch_array($rs)) {
                    $subjects_list .= '<a href="tce_edit_subject.php?subject_id='.$ms['subject_id'].'" title="'.$l['t_subjects_editor'].'">'.htmlspecialchars($ms['subject_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</a>, ';
                }
            } else {
                F_display_db_error();
            }
            // remove last comma + space
            $subjlist .= substr($subjects_list, 0, -2);
            $subjlist .= '<br />'.K_NEWLINE;
            $subjlist .= '<acronym class="offbox" title="'.$l['h_num_questions'].'">'.$m['tsubset_quantity'].'</acronym> ';
            $subjlist .= '<acronym class="offbox" title="'.$l['h_question_type'].'">';
            if ($m['tsubset_type'] > 0) {
                $subjlist .= $qtype[($m['tsubset_type'] - 1)];
            } else {
                // all question types
                $subjlist .= '*';
            }
            $subjlist .= '</acronym> ';
            $subjlist .= '<acronym class="offbox" title="'.$l['h_question_difficulty'].'">'.$m['tsubset_difficulty'].'</acronym> ';
            $subjlist .= '<acronym class="offbox" title="'.$l['h_num_answers'].'">'.$m['tsubset_answers'].'</acronym> ';
            $subjlist .= ' <a href="'.$_SERVER['SCRIPT_NAME'].'?menu_mode=deletesubject&amp;test_id='.$test_id.'&amp;tsubset_id='.$m['tsubset_id'].'" title="'.$l['h_delete'].'" class="deletebutton">'.$l['w_delete'].'</a>';
            $subjlist .= '</li>'.K_NEWLINE;

            // update test_max_score
            $test_max_score_new += $test_score_right * $m['tsubset_difficulty'] * $m['tsubset_quantity'];
            if (isset($test_max_score) and ($test_max_score_new != $test_max_score)) {
                $test_max_score = $test_max_score_new;
                // update max score on test table
                $sqlup = 'UPDATE '.K_TABLE_TESTS.' SET test_max_score='.$test_max_score.' WHERE test_id='.$test_id.'';
                if (!$rup = F_db_query($sqlup, $db)) {
                    F_display_db_error(false);
                }
            }
        }
        if (strlen($subjlist) > 0) {
            echo '<ul>'.K_NEWLINE.$subjlist.'</ul>'.K_NEWLINE;
        }
    } else {
        F_display_db_error();
    }

    echo '&nbsp;'.K_NEWLINE;

    echo $l['w_max_score'].': '.$test_max_score_new;
    echo '<input type="hidden" name="test_max_score" id="test_max_score" value="'.$test_max_score_new.'" />';

    echo '</div>'.K_NEWLINE;
    echo '<br /><br />'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;

    echo '</fieldset>'.K_NEWLINE;

    echo '<div class="row"><br /></div>'.K_NEWLINE;

    if (isset($test_max_score_new) and ($test_max_score_new > 0)) {
        echo '<div class="row">'.K_NEWLINE;
        echo '<span class="label">'.K_NEWLINE;
        echo '<label for="test_num">'.$l['w_pdf_offline_test'].'</label>'.K_NEWLINE;
        echo '</span>'.K_NEWLINE;
        echo '<span class="formw">'.K_NEWLINE;
        echo '<input type="text" name="test_num" id="test_num" value="'.$test_num.'" size="4" maxlength="10" title="'.$l['h_pdf_offline_test'].'" />'.K_NEWLINE;
        echo '<a href="tce_pdf_testgen.php?test_id='.$test_id.'&amp;num='.$test_num.'" title="'.$l['h_pdf_offline_test'].'" class="xmlbutton" onclick="pdfWindow=window.open(\'tce_pdf_testgen.php?test_id='.$test_id.'&amp;num=\' + document.getElementById(\'form_testeditor\').test_num.value + \'\',\'pdfWindow\',\'dependent,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes\'); return false;">'.$l['w_generate'].'</a>';
        echo '</span>&nbsp;'.K_NEWLINE;
        echo '</div>'.K_NEWLINE;
    }
}

echo '</form>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_edit_test'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

// javascript controls
echo '<script type="text/javascript">'.K_NEWLINE;
echo '//<![CDATA['.K_NEWLINE;
echo 'function JF_check_random_boxes() {'.K_NEWLINE;
echo ' if (document.getElementById(\'test_random_questions_select\').checked==true){document.getElementById(\'test_random_questions_order\').checked=true;}'.K_NEWLINE;
echo ' if ((document.getElementById(\'test_random_questions_order\').checked==false)&&(document.getElementById(\'test_random_questions_select\').checked==true)){document.getElementById(\'test_random_questions_order\').checked=true;}'.K_NEWLINE;
echo ' if (document.getElementById(\'test_random_questions_order\').checked==false){document.getElementById(\'select_questions_order_mode\').style.visibility="visible";}else{document.getElementById(\'select_questions_order_mode\').style.visibility="hidden";}'.K_NEWLINE;
echo ' if (document.getElementById(\'test_random_answers_select\').checked==true){document.getElementById(\'test_random_answers_order\').checked=true;}'.K_NEWLINE;
echo ' if ((document.getElementById(\'test_random_answers_order\').checked==false)&&(document.getElementById(\'test_random_answers_select\').checked==true)){document.getElementById(\'test_random_answers_order\').checked=true;}'.K_NEWLINE;
echo ' if (document.getElementById(\'test_random_answers_order\').checked==false){document.getElementById(\'select_answers_order_mode\').style.visibility="visible";}else{document.getElementById(\'select_answers_order_mode\').style.visibility="hidden";}'.K_NEWLINE;
echo '}'.K_NEWLINE;
echo 'JF_check_random_boxes();'.K_NEWLINE;
echo '//]]>'.K_NEWLINE;
echo '</script>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
