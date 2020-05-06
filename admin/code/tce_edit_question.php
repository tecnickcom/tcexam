<?php
//============================================================+
// File name   : tce_edit_question.php
// Begin       : 2004-04-27
// Last Update : 2020-05-06
//
// Description : Edit questions
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
//    Copyright (C) 2004-2020 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display form to edit exam questions.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-04-27
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_QUESTIONS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_questions_editor'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../code/tce_functions_tcecode_editor.php');
require_once('../../shared/code/tce_functions_auth_sql.php');

// upload multimedia files
$uploadedfile = array();
for ($id = 0; $id < 2; ++$id) {
    if (isset($_POST['sendfile'.$id]) and ($_FILES['userfile'.$id]['name'])) {
        require_once('../code/tce_functions_upload.php');
        $uploadedfile['\''.$id.'\''] = F_upload_file('userfile'.$id, K_PATH_CACHE);
    }
}

// set default values
if (isset($_REQUEST['subject_module_id'])) {
    $subject_module_id = intval($_REQUEST['subject_module_id']);
} else {
    $subject_module_id = 0;
}
if (!isset($_REQUEST['question_id'])) {
    $question_id = 0;
} else {
    $question_id = intval($_REQUEST['question_id']);
}
if (!isset($_REQUEST['question_type']) or (empty($_REQUEST['question_type']))) {
    $question_type = 1;
} else {
    $question_type = intval($_REQUEST['question_type']);
}
if (!isset($_REQUEST['question_difficulty'])) {
    $question_difficulty = 1;
} else {
    $question_difficulty = intval($_REQUEST['question_difficulty']);
}
if (!isset($_REQUEST['question_enabled']) or (empty($_REQUEST['question_enabled']))) {
    $question_enabled = false;
} else {
    $question_enabled = F_getBoolean($_REQUEST['question_enabled']);
}
if (isset($_REQUEST['changemodule']) and ($_REQUEST['changemodule'] > 0)) {
    $changemodule = 1;
} elseif (isset($_REQUEST['selectmodule'])) {
    $changemodule = 1;
} else {
    $changemodule = 0;
}
if (isset($_REQUEST['changecategory']) and ($_REQUEST['changecategory'] > 0)) {
    $changecategory = 1;
} elseif (isset($_REQUEST['selectcategory'])) {
    $changecategory = 1;
} else {
    $changecategory = 0;
}
if (isset($_REQUEST['subject_id'])) {
    $subject_id = intval($_REQUEST['subject_id']);
} else {
    $subject_id = 0;
}
if (isset($_REQUEST['question_subject_id'])) {
    $question_subject_id = intval($_REQUEST['question_subject_id']);
} else {
    $question_subject_id = 0;
}
if (!isset($_REQUEST['max_position']) or empty($_REQUEST['max_position'])) {
    $max_position = 0;
} else {
    $max_position = intval($_REQUEST['max_position']);
}
if (!isset($_REQUEST['question_position']) or empty($_REQUEST['question_position'])) {
    $question_position = 0;
} else {
    $question_position = intval($_REQUEST['question_position']);
}
if (!isset($_REQUEST['question_timer']) or (empty($_REQUEST['question_timer']))) {
    $question_timer = 0;
} else {
    $question_timer = intval($_REQUEST['question_timer']);
}
if (!isset($_REQUEST['question_fullscreen']) or (empty($_REQUEST['question_fullscreen']))) {
    $question_fullscreen = false;
} else {
    $question_fullscreen = F_getBoolean($_REQUEST['question_fullscreen']);
}
if (!isset($_REQUEST['question_inline_answers']) or (empty($_REQUEST['question_inline_answers']))) {
    $question_inline_answers = false;
} else {
    $question_inline_answers = F_getBoolean($_REQUEST['question_inline_answers']);
}
if (!isset($_REQUEST['question_auto_next']) or (empty($_REQUEST['question_auto_next']))) {
    $question_auto_next = false;
} else {
    $question_auto_next = F_getBoolean($_REQUEST['question_auto_next']);
}
if (isset($_REQUEST['question_description'])) {
    $question_description = utrim($_REQUEST['question_description']);
    if (function_exists('normalizer_normalize')) {
        // normalize UTF-8 string based on settings
        $question_description = F_utf8_normalizer($question_description, K_UTF8_NORMALIZATION_MODE);
    }
}
if (isset($_REQUEST['question_explanation'])) {
    $question_explanation = utrim($_REQUEST['question_explanation']);
} else {
    $question_explanation = '';
}
$qtype = array('S', 'M', 'T', 'O'); // question types

// comma separated list of required fields
$_REQUEST['ff_required'] = 'question_description';
$_REQUEST['ff_required_labels'] = htmlspecialchars($l['w_description'], ENT_COMPAT, $l['a_meta_charset']);

// check user's authorization
if ($question_id > 0) {
    $sql = 'SELECT subject_module_id, question_subject_id
		FROM '.K_TABLE_SUBJECTS.', '.K_TABLE_QUESTIONS.'
		WHERE subject_id=question_subject_id
			AND question_id='.$question_id.'
		LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            // check user's authorization for parent module
            if ((!F_isAuthorizedUser(K_TABLE_MODULES, 'module_id', $m['subject_module_id'], 'module_user_id'))
                and (!F_isAuthorizedUser(K_TABLE_SUBJECTS, 'subject_id', $m['question_subject_id'], 'subject_user_id'))) {
                F_print_error('ERROR', $l['m_authorization_denied'], true);
            }
        }
    } else {
        F_display_db_error();
    }
}

switch ($menu_mode) {
    case 'delete':{
        F_stripslashes_formfields();
        // check if this record is used (test_log)
        if (!F_check_unique(K_TABLE_TESTS_LOGS, 'testlog_question_id='.$question_id.'')) {
            //this record will be only disabled and not deleted because it's used
            $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
				question_enabled=\'0\'
				WHERE question_id='.$question_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error();
            }
            F_print_error('WARNING', $l['m_disabled_vs_deleted']);
        } else {
            // ask confirmation
            F_print_error('WARNING', $l['m_delete_confirm']);
            echo '<div class="confirmbox">'.K_NEWLINE;
            echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
            echo '<div>'.K_NEWLINE;
            echo '<input type="hidden" name="question_id" id="question_id" value="'.$question_id.'" />'.K_NEWLINE;
            echo '<input type="hidden" name="subject_module_id" id="subject_module_id" value="'.$subject_module_id.'" />'.K_NEWLINE;
            echo '<input type="hidden" name="question_subject_id" id="question_subject_id" value="'.$question_subject_id.'" />'.K_NEWLINE;
            echo '<input type="hidden" name="question_description" id="question_description" value="'.$question_description.'" />'.K_NEWLINE;
            echo '<input type="hidden" name="question_explanation" id="question_explanation" value="'.$question_explanation.'" />'.K_NEWLINE;
            F_submit_button('forcedelete', $l['w_delete'], $l['h_delete']);
            F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
            echo '</div>'.K_NEWLINE;
            echo F_getCSRFTokenField().K_NEWLINE;
            echo '</form>'.K_NEWLINE;
            echo '</div>'.K_NEWLINE;
        }
        break;
    }

    case 'forcedelete':{
        F_stripslashes_formfields(); // Delete
        if ($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
            $sql = 'START TRANSACTION';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
                break;
            }

            // get question position (if defined)
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
            // delete question
            $sql = 'DELETE FROM '.K_TABLE_QUESTIONS.' WHERE question_id='.$question_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
                F_db_query('ROLLBACK', $db); // rollback transaction
            } else {
                $question_id=false;
                // adjust questions ordering
                if ($question_position > 0) {
                    $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
						question_position=question_position-1
						WHERE question_subject_id='.$question_subject_id.'
							AND question_position>'.$question_position.'';
                    if (!$r = F_db_query($sql, $db)) {
                        F_display_db_error(false);
                        F_db_query('ROLLBACK', $db); // rollback transaction
                    }
                }

                $sql = 'COMMIT';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error(false);
                    break;
                }
                F_print_error('MESSAGE', $l['m_deleted']);
            }
        }
        break;
    }

    case 'update':{ // Update
        // check if the confirmation chekbox has been selected
        if (!isset($_REQUEST['confirmupdate']) or ($_REQUEST['confirmupdate'] != 1)) {
            F_print_error('WARNING', $l['m_form_missing_fields'].': '.$l['w_confirm'].' &rarr; '.$l['w_update']);
            F_stripslashes_formfields();
            break;
        }
        if ($formstatus = F_check_form_fields()) {
            // get previous question position (if defined)
            $prev_question_position = 0;
            $sql = 'SELECT question_position
				FROM '.K_TABLE_QUESTIONS.'
				WHERE question_id='.$question_id.'
				LIMIT 1';
            if ($r = F_db_query($sql, $db)) {
                if ($m = F_db_fetch_array($r)) {
                    $prev_question_position = intval($m['question_position']);
                }
            } else {
                F_display_db_error();
            }

            // check referential integrity (NOTE: mysql do not support "ON UPDATE" constraint)
            if (!F_check_unique(K_TABLE_TESTS_LOGS, 'testlog_question_id='.$question_id.'')) {
                F_print_error('WARNING', $l['m_update_restrict']);
                // when the question is disabled, the position is discarded
                if (!$question_enabled) {
                    $question_position = 0;
                } else {
                    $question_position = $prev_question_position;
                }
                // enable or disable record
                $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
					question_enabled=\''.intval($question_enabled).'\',
					question_position='.F_zero_to_null($question_position).'
					WHERE question_id='.$question_id.'';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error(false);
                } else {
                    $strmsg = $l['w_record_status'].': ';
                    if ($question_enabled) {
                        $strmsg .= $l['w_enabled'];
                    } else {
                        $strmsg .= $l['w_disabled'];
                    }
                    F_print_error('MESSAGE', $strmsg);
                }

                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            // check if alternate key is unique
            if (K_DATABASE_TYPE == 'ORACLE') {
                $chksql = 'dbms_lob.instr(question_description,\''.F_escape_sql($db, $question_description).'\',1,1)>0';
            } elseif ((K_DATABASE_TYPE == 'MYSQL') and K_MYSQL_QA_BIN_UNIQUITY) {
                $chksql = 'question_description=\''.F_escape_sql($db, $question_description).'\' COLLATE utf8_bin';
            } else {
                $chksql = 'question_description=\''.F_escape_sql($db, $question_description).'\'';
            }
            if (!F_check_unique(K_TABLE_QUESTIONS, $chksql.' AND question_subject_id='.$question_subject_id.'', 'question_id', $question_id)) {
                F_print_error('WARNING', $l['m_duplicate_question']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }

            $sql = 'START TRANSACTION';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
                break;
            }
            // when the question is disabled, the position is discarded
            if (!$question_enabled) {
                $question_position = 0;
            }
            if ($question_position > $max_position) {
                $question_position = $max_position;
            }
            // arrange positions if necessary
            if ($question_position != $prev_question_position) {
                if ($question_position > 0) {
                    if ($prev_question_position > 0) {
                        // swap positions
                        $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
							question_position='.$prev_question_position.'
							WHERE question_subject_id='.$question_subject_id.'
								AND question_position='.$question_position.'';
                    } elseif ($prev_question_position == 0) {
                        // right shift positions
                        $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
							question_position=question_position+1
							WHERE question_subject_id='.$question_subject_id.'
								AND question_position>='.$question_position.'';
                    }
                } else {
                    // left shift positions
                    $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
						question_position=question_position-1
						WHERE question_subject_id='.$question_subject_id.'
							AND question_position>'.$prev_question_position.'';
                }
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error(false);
                    F_db_query('ROLLBACK', $db); // rollback transaction
                }
            }
            $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
				question_subject_id='.$question_subject_id.',
				question_description=\''.F_escape_sql($db, $question_description).'\',
				question_explanation='.F_empty_to_null($question_explanation).',
				question_type=\''.$question_type.'\',
				question_difficulty=\''.$question_difficulty.'\',
				question_enabled=\''.intval($question_enabled).'\',
				question_position='.F_zero_to_null($question_position).',
				question_timer=\''.$question_timer.'\',
				question_fullscreen=\''.intval($question_fullscreen).'\',
				question_inline_answers=\''.intval($question_inline_answers).'\',
				question_auto_next=\''.intval($question_auto_next).'\'
				WHERE question_id='.$question_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                F_print_error('MESSAGE', $l['m_updated']);
            }

            $sql = 'COMMIT';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
                break;
            }
        }
        break;
    }

    case 'add':{ // Add
        if ($formstatus = F_check_form_fields()) {
            // check if alternate key is unique
            if (K_DATABASE_TYPE == 'ORACLE') {
                $chksql = 'dbms_lob.instr(question_description,\''.F_escape_sql($db, $question_description).'\',1,1)>0';
            } elseif ((K_DATABASE_TYPE == 'MYSQL') and K_MYSQL_QA_BIN_UNIQUITY) {
                $chksql = 'question_description=\''.F_escape_sql($db, $question_description).'\' COLLATE utf8_bin';
            } else {
                $chksql = 'question_description=\''.F_escape_sql($db, $question_description).'\'';
            }
            if (!F_check_unique(K_TABLE_QUESTIONS, $chksql.' AND question_subject_id='.$question_subject_id.'')) {
                F_print_error('WARNING', $l['m_duplicate_question']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            $sql = 'START TRANSACTION';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
                break;
            }
            // adjust questions ordering
            if ($question_position > 0) {
                $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
					question_position=question_position+1
					WHERE question_subject_id='.$question_subject_id.'
						AND question_position>='.$question_position.'';
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
				'.$question_subject_id.',
				\''.F_escape_sql($db, $question_description).'\',
				'.F_empty_to_null($question_explanation).',
				\''.$question_type.'\',
				\''.$question_difficulty.'\',
				\''.intval($question_enabled).'\',
				'.F_zero_to_null($question_position).',
				\''.$question_timer.'\',
				\''.intval($question_fullscreen).'\',
				\''.intval($question_inline_answers).'\',
				\''.intval($question_auto_next).'\'
				)';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                $question_id = F_db_insert_id($db, K_TABLE_QUESTIONS, 'question_id');
            }
            $sql = 'COMMIT';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
                break;
            }
        }
        break;
    }

    case 'clear':{ // Clear form fields
        $question_description = '';
        $question_explanation = '';
        $question_type = 1;
        $question_difficulty = 1;
        $question_enabled = true;
        $question_position = 0;
        $question_timer = 0;
        $question_fullscreen = false;
        $question_inline_answers = false;
        $question_auto_next = false;
        break;
    }

    default :{
        break;
    }
} //end of switch

// select default module/subject (if not specified)
if ($subject_module_id <= 0) {
    $sql = F_select_modules_sql().' LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            $subject_module_id = $m['module_id'];
        } else {
            $subject_module_id = 0;
        }
    } else {
        F_display_db_error();
    }
}

// select subject
if (($changemodule > 0) or ($question_subject_id <= 0)) {
    $sql = F_select_subjects_sql('subject_module_id='.$subject_module_id.'').' LIMIT 1';
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            $question_subject_id = $m['subject_id'];
        } else {
            $question_subject_id = 0;
        }
    } else {
        F_display_db_error();
    }
}

// --- Initialize variables
if ($formstatus) {
    if ($menu_mode != 'clear') {
        if (($changemodule > 0) or ($changecategory > 0) or empty($question_id)) {
            $question_id = 0;
            $question_description = '';
            $question_explanation = '';
            $question_type = 1;
            $question_difficulty = 1;
            $question_enabled = true;
            $question_position = 0;
            $question_timer = 0;
            $question_fullscreen = false;
            $question_inline_answers = false;
            $question_auto_next = false;
        } else {
            $sql = 'SELECT *
				FROM '.K_TABLE_QUESTIONS.'
				WHERE question_id='.$question_id.'
				LIMIT 1';
            if ($r = F_db_query($sql, $db)) {
                if ($m = F_db_fetch_array($r)) {
                    $question_id = $m['question_id'];
                    $question_subject_id = $m['question_subject_id'];
                    $question_description = $m['question_description'];
                    $question_explanation = $m['question_explanation'];
                    $question_type = $m['question_type'];
                    $question_difficulty = $m['question_difficulty'];
                    $question_enabled = F_getBoolean($m['question_enabled']);
                    $question_position = $m['question_position'];
                    $question_timer = $m['question_timer'];
                    $question_fullscreen = F_getBoolean($m['question_fullscreen']);
                    $question_inline_answers = F_getBoolean($m['question_inline_answers']);
                    $question_auto_next = F_getBoolean($m['question_auto_next']);
                } else {
                    $question_description = '';
                    $question_explanation = '';
                    $question_type = 1;
                    $question_difficulty = 1;
                    $question_enabled = true;
                    $question_position = 0;
                    $question_timer = 0;
                    $question_fullscreen = false;
                    $question_inline_answers = false;
                    $question_auto_next = false;
                }
            } else {
                F_display_db_error();
            }
        }
    }
}

if (($subject_module_id <= 0) or ($question_subject_id <= 0)) {
    echo '<div class="container">'.K_NEWLINE;
    echo '<p><a href="tce_edit_subject.php" title="'.$l['t_subjects_editor'].'" class="xmlbutton">&lt; '.$l['t_subjects_editor'].'</a></p>'.K_NEWLINE;
    echo '<div class="pagehelp">'.$l['hp_edit_question'].'</div>'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;
    require_once('../code/tce_page_footer.php');
    exit;
}

echo '<script src="'.K_PATH_SHARED_JSCRIPTS.'inserttag.js" type="text/javascript"></script>'.K_NEWLINE;
if (K_ENABLE_VIRTUAL_KEYBOARD) {
    echo '<script src="'.K_PATH_SHARED_JSCRIPTS.'vk/vk_easy.js?vk_skin=default" type="text/javascript"></script>'.K_NEWLINE;
}

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_questioneditor">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="subject_module_id">'.$l['w_module'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="hidden" name="changemodule" id="changemodule" value="" />'.K_NEWLINE;
echo '<select name="subject_module_id" id="subject_module_id" size="0" onchange="document.getElementById(\'form_questioneditor\').changemodule.value=1; document.getElementById(\'form_questioneditor\').submit();" title="'.$l['w_module'].'">'.K_NEWLINE;
$sql = F_select_modules_sql();
if ($r = F_db_query($sql, $db)) {
    $countitem = 1;
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['module_id'].'"';
        if ($m['module_id'] == $subject_module_id) {
            echo ' selected="selected"';
        }
        echo '>'.$countitem.'. ';
        if (F_getBoolean($m['module_enabled'])) {
            echo '+';
        } else {
            echo '-';
        }
        echo ' '.htmlspecialchars($m['module_name'], ENT_NOQUOTES, $l['a_meta_charset']).'&nbsp;</option>'.K_NEWLINE;
        $countitem++;
    }
    if ($countitem == 1) {
        echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
    }
} else {
    echo '</select></span></div>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectmodule');

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="question_subject_id">'.$l['w_subject'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="hidden" name="changecategory" id="changecategory" value="" />'.K_NEWLINE;
echo '<select name="question_subject_id" id="question_subject_id" size="0" onchange="document.getElementById(\'form_questioneditor\').changecategory.value=1; document.getElementById(\'form_questioneditor\').submit();" title="'.$l['h_subject'].'">'.K_NEWLINE;
$sql = F_select_subjects_sql('subject_module_id='.$subject_module_id);
if ($r = F_db_query($sql, $db)) {
    $countitem = 1;
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['subject_id'].'"';
        if ($m['subject_id'] == $question_subject_id) {
            echo ' selected="selected"';
        }
        echo '>'.$countitem.'. ';
        if (F_getBoolean($m['subject_enabled'])) {
            echo '+';
        } else {
            echo '-';
        }
        echo ' '.htmlspecialchars(F_remove_tcecode($m['subject_name']), ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
        $countitem++;
    }
    if ($countitem == 1) {
        echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
    }
} else {
    echo '</select></span></div>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectcategory');

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="question_id">'.$l['w_question'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="question_id" id="question_id" size="0" onchange="document.getElementById(\'form_questioneditor\').submit()" title="'.$l['h_question'].'">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($question_id == 0) {
    echo ' selected="selected"';
}
echo '>+</option>'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_QUESTIONS.' WHERE question_subject_id='.intval($question_subject_id).' ORDER BY question_enabled DESC, question_position,';
if (K_DATABASE_TYPE == 'ORACLE') {
    $sql .= 'CAST(question_description as varchar2(100))';
} else {
    $sql .= 'question_description';
}
if ($r = F_db_query($sql, $db)) {
    $countitem = 1;
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['question_id'].'"';
        if ($m['question_id'] == $question_id) {
            echo ' selected="selected"';
        }
        echo '>'.$countitem.'. ';
        if (!F_getBoolean($m['question_enabled'])) {
            echo '-';
        } else {
            echo $qtype[($m['question_type'] - 1)];
        }
        echo ' '.htmlspecialchars(F_substr_utf8(F_remove_tcecode($m['question_description']), 0, K_SELECT_SUBSTRING), ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
        $countitem++;
    }
    if ($countitem == 1) {
        echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
    }
} else {
    echo '</select></span></div>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="question_description">'.$l['w_question'].'</label>'.K_NEWLINE;
echo '<br />'.K_NEWLINE;
echo '<a href="#" title="'.$l['h_preview'].'" class="xmlbutton" onclick="previewWindow=window.open(\'tce_preview_tcecode.php?tcexamcode=\'+encodeURIComponent(document.getElementById(\'form_questioneditor\').question_description.value),\'previewWindow\',\'dependent,height=500,width=500,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'.$l['w_preview'].'</a>'.K_NEWLINE;

echo '</span>'.K_NEWLINE;
echo '<span class="formw" style="border:1px solid #808080;">'.K_NEWLINE;
echo '<textarea cols="50" rows="10" name="question_description" id="question_description" onselect="FJ_update_selection(document.getElementById(\'form_questioneditor\').question_description)" title="'.$l['h_question_description'].'"';
if (K_ENABLE_VIRTUAL_KEYBOARD) {
    echo ' class="keyboardInput"';
}
echo '>'.htmlspecialchars($question_description, ENT_NOQUOTES, $l['a_meta_charset']).'</textarea>'.K_NEWLINE;
echo '<br />'.K_NEWLINE;
echo tcecodeEditorTagButtons('form_questioneditor', 'question_description');
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

if (K_ENABLE_QUESTION_EXPLANATION) {
    echo '<div class="row">'.K_NEWLINE;
    echo '<span class="label">'.K_NEWLINE;
    echo '<label for="question_explanation">'.$l['w_explanation'].'</label>'.K_NEWLINE;
    echo '<br />'.K_NEWLINE;
    $showexplanationarea = 'javascript:if(document.getElementById(\'explanationarea\').style.display==\'none\'){document.getElementById(\'explanationarea\').style.display=\'block\';document.getElementById(\'showexplanationarea\').style.display=\'none\';document.getElementById(\'hideexplanationarea\').style.display=\'block\';}; return false;';
    echo '<span id="showexplanationarea"><a class="xmlbutton" href="#" onclick="'.$showexplanationarea.'" title="'.$l['w_show'].'">'.$l['w_show'].' &rarr;</a></span>';
    $hideexplanationarea = 'javascript:if(document.getElementById(\'explanationarea\').style.display==\'block\'){document.getElementById(\'explanationarea\').style.display=\'none\';document.getElementById(\'showexplanationarea\').style.display=\'block\';document.getElementById(\'hideexplanationarea\').style.display=\'none\';}; return false;';
    echo '<span id="hideexplanationarea" style="display:none;">';
    echo '<a href="#" title="'.$l['h_preview'].'" class="xmlbutton" onclick="previewWindow=window.open(\'tce_preview_tcecode.php?tcexamcode=\'+encodeURIComponent(document.getElementById(\'form_questioneditor\').question_explanation.value),\'previewWindow\',\'dependent,height=500,width=500,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'.$l['w_preview'].'</a>'.K_NEWLINE;
    echo '<a class="xmlbutton" href="#" onclick="'.$hideexplanationarea.'" title="'.$l['w_hide'].'">'.$l['w_hide'].'</a> ';
    echo '</span>';
    echo '</span>'.K_NEWLINE;
    echo '<span id="explanationarea" class="formw" style="display:none;border:1px solid #808080;">'.K_NEWLINE;
    echo '<textarea cols="50" rows="10" name="question_explanation" id="question_explanation" onselect="FJ_update_selection(document.getElementById(\'form_questioneditor\').question_explanation)" title="'.$l['h_explanation'].'"';
    if (K_ENABLE_VIRTUAL_KEYBOARD) {
        echo ' class="keyboardInput"';
    }
    echo '>'.htmlspecialchars($question_explanation, ENT_NOQUOTES, $l['a_meta_charset']).'</textarea>'.K_NEWLINE;
    echo '<br />'.K_NEWLINE;
    echo tcecodeEditorTagButtons('form_questioneditor', 'question_explanation');
    echo '</span>'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;
}

// question type
echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">&nbsp;</span>'.K_NEWLINE;
echo '<div class="formw">'.K_NEWLINE;
echo '<fieldset class="noborder">'.K_NEWLINE;
echo '<legend title="'.$l['h_question_type'].'">'.$l['w_type'].'</legend>'.K_NEWLINE;

echo '<input type="radio" name="question_type" id="single_answer" value="1"';
if ($question_type==1) {
    echo ' checked="checked"';
}
echo ' title="'.$l['h_enable_single_answer'].'" />';
echo '<label for="single_answer">'.$l['w_single_answer'].'</label>'.K_NEWLINE;
echo '<br />'.K_NEWLINE;

echo '<input type="radio" name="question_type" id="multiple_answers" value="2"';
if ($question_type==2) {
    echo ' checked="checked"';
}
echo ' title="'.$l['h_enable_multiple_answers'].'" />';
echo '<label for="multiple_answers">'.$l['w_multiple_answers'].'</label>'.K_NEWLINE;
echo '<br />'.K_NEWLINE;

echo '<input type="radio" name="question_type" id="free_answer" value="3"';
if ($question_type==3) {
    echo ' checked="checked"';
}
echo ' title="'.$l['h_enable_free_answer'].'" />';
echo '<label for="free_answer">'.$l['w_free_answer'].'</label>'.K_NEWLINE;
echo '<br />'.K_NEWLINE;

echo '<input type="radio" name="question_type" id="ordering_answer" value="4"';
if ($question_type==4) {
    echo ' checked="checked"';
}
echo ' title="'.$l['h_enable_ordering_answer'].'" />';
echo '<label for="free_answer">'.$l['w_ordering_answer'].'</label>'.K_NEWLINE;

echo '</fieldset>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

// question difficulty
$items = array();
for ($i = 0; $i <= K_QUESTION_DIFFICULTY_LEVELS; ++$i) {
    $items[$i] = $i;
}
echo getFormRowSelectBox('question_difficulty', $l['w_question_difficulty'], $l['h_question_difficulty'], '', $question_difficulty, $items, '');

// question position
echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="question_position">'.$l['w_position'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="question_position" id="question_position" size="0" title="'.$l['h_position'].'">'.K_NEWLINE;
if (isset($question_id) and ($question_id > 0)) {
    $max_position = (1 + F_count_rows(K_TABLE_QUESTIONS, "WHERE question_subject_id=".$question_subject_id." AND question_position>0 AND question_id<>".$question_id.""));
} else {
    $max_position = 0;
}
echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
for ($pos=1; $pos <= $max_position; $pos++) {
    echo '<option value="'.$pos.'"';
    if ($pos == $question_position) {
        echo ' selected="selected"';
    }
    echo '>'.$pos.'</option>'.K_NEWLINE;
}
echo '<option value="'.($max_position + 1).'" style="color:#ff0000">'.($max_position + 1).'</option>'.K_NEWLINE;
echo '</select>'.K_NEWLINE;
echo '<input type="hidden" name="max_position" id="max_position" value="'.$max_position.'" />'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormRowTextInput('question_timer', $l['w_timer'], $l['h_question_timer'], '[sec]', $question_timer, '^([0-9]*)$', 20, false, false, false, '');

echo getFormRowCheckBox('question_fullscreen', $l['w_fullscreen'], $l['h_question_fullscreen'], '', 1, $question_fullscreen, false, '');
echo getFormRowCheckBox('question_inline_answers', $l['w_inline_answers'], $l['h_question_inline_answers'], '', 1, $question_inline_answers, false, '');
echo getFormRowCheckBox('question_auto_next', $l['w_auto_next'], $l['h_question_auto_next'], '', 1, $question_auto_next, false, '');
echo getFormRowCheckBox('question_enabled', $l['w_enabled'], $l['h_enabled'], '', 1, $question_enabled, false, '');

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($question_id) and ($question_id > 0)) {
    echo '<span style="background-color:#999999;">';
    echo '<input type="checkbox" name="confirmupdate" id="confirmupdate" value="1" title="confirm &rarr; update" />';
    F_submit_button('update', $l['w_update'], $l['h_update']);
    echo '</span>';
    F_submit_button('add', $l['w_add'], $l['h_add']);
    F_submit_button('delete', $l['w_delete'], $l['h_delete']);
} else {
    F_submit_button('add', $l['w_add'], $l['h_add']);
}
F_submit_button('clear', $l['w_clear'], $l['h_clear']);

echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="left">'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;
if (isset($question_subject_id) and ($question_subject_id > 0)) {
    echo '<a href="tce_edit_subject.php?subject_module_id='.$subject_module_id.'&amp;subject_id='.$question_subject_id.'" title="'.$l['t_subjects_editor'].'" class="xmlbutton">&lt; '.$l['t_subjects_editor'].'</a>';
}
echo '</span>'.K_NEWLINE;
echo '<span class="right">'.K_NEWLINE;
if (isset($question_id) and ($question_id > 0)) {
    echo '<a href="tce_edit_answer.php?subject_module_id='.$subject_module_id.'&amp;question_subject_id='.$question_subject_id.'&amp;answer_question_id='.$question_id.'" title="'.$l['t_answers_editor'].'" class="xmlbutton">'.$l['t_answers_editor'].' &gt;</a>';
}

echo '&nbsp;'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo '<div class="rowl" title="'.$l['h_preview'].'">'.K_NEWLINE;
echo $l['w_preview'];
echo '<div class="preview">'.K_NEWLINE;

echo F_decode_tcecode($question_description);

echo '&nbsp;'.K_NEWLINE;
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;
echo F_getCSRFTokenField().K_NEWLINE;
echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_edit_question'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
