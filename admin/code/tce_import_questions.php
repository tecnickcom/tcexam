<?php
//============================================================+
// File name   : tce_import_questions.php
// Begin       : 2006-03-12
// Last Update : 2013-04-12
//
// Description : Import questions from an XML file.
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
 * Import questions from an XML file to a selected subject.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2006-03-12
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_IMPORT;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_question_importer'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_auth_sql.php');

if (!isset($type) or (empty($type))) {
    $type = 1;
} else {
    $type = intval($type);
}

if (isset($menu_mode) and ($menu_mode == 'upload')) {
    if ($_FILES['userfile']['name']) {
        require_once('../code/tce_functions_upload.php');
        // upload file
        $uploadedfile = F_upload_file('userfile', K_PATH_CACHE);
        if ($uploadedfile !== false) {
            $qimp = false;
            switch ($type) {
                case 1: {
                    // standard TCExam XML format
                    require_once('../code/tce_class_import_xml.php');
                    $qimp = new XMLQuestionImporter(K_PATH_CACHE.$uploadedfile);
                    break;
                }
                case 2: {
                    // standard TCExam TSV format
                    $qimp = F_TSVQuestionImporter(K_PATH_CACHE.$uploadedfile);
                    break;
                }
                case 3: {
                    // Custom TCExam XML format
                    require_once('../code/tce_import_custom.php');
                    $qimp = new CustomQuestionImporter(K_PATH_CACHE.$uploadedfile);
                    break;
                }
            }
            if ($qimp) {
                F_print_error('MESSAGE', $l['m_importing_complete']);
            }
        }
    }
}
echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_importquestions">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="userfile">'.$l['w_upload_file'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="hidden" name="MAX_FILE_SIZE" value="'.K_MAX_UPLOAD_SIZE.'" />'.K_NEWLINE;
echo '<input type="file" name="userfile" id="userfile" size="20" title="'.$l['h_upload_file'].'" />'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">&nbsp;</span>'.K_NEWLINE;
echo '<div class="formw">'.K_NEWLINE;
echo '<fieldset class="noborder">'.K_NEWLINE;

echo '<legend title="'.$l['w_type'].'">'.$l['w_type'].'</legend>'.K_NEWLINE;
echo '<input type="radio" name="type" id="type_xml" value="1" title="TCExam XML Format"';
if ($type == 1) {
    echo ' checked="checked"';
}
echo ' />';
echo '<label for="type_xml">TCExam XML</label><br />'.K_NEWLINE;

echo '<input type="radio" name="type" id="type_tsv" value="2" title="TCExam TSV Format"'.K_NEWLINE;
if ($type == 2) {
    echo ' checked="checked"';
}
echo ' />';
echo '<label for="type_tsv">TCExam TSV</label>'.K_NEWLINE;

$custom_import = K_ENABLE_CUSTOM_IMPORT;
if (!empty($custom_import)) {
    echo '<input type="radio" name="type" id="type_custom" value="3" title="'.$custom_import.'"'.K_NEWLINE;
    if ($type == 3) {
        echo ' checked="checked"';
    }
    echo ' />';
    echo '<label for="type_custom">'.$custom_import.'</label>'.K_NEWLINE;
}

echo '</fieldset>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<br />'.K_NEWLINE;

// show upload button
F_submit_button('upload', $l['w_upload'], $l['h_submit_file']);

echo '</div>'.K_NEWLINE;
echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_import_xml_questions'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

// ---------------------------------------------------------------------

/**
 * Import questions from TSV file (tab delimited text).
 * The format of TSV is the same obtained by exporting data from TCExam interface.
 * @param $tsvfile (string) TSV (tab delimited text) file name
 * @return boolean TRUE in case of success, FALSE otherwise
 */
function F_TSVQuestionImporter($tsvfile)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    require_once('../../shared/code/tce_functions_auth_sql.php');
    $qtype = array('S' => 1, 'M' => 2, 'T' => 3, 'O' => 4);
    // get file content as array
    $tsvrows = file($tsvfile, FILE_IGNORE_NEW_LINES); // array of TSV lines
    if ($tsvrows === false) {
        return false;
    }
    $current_module_id = 0;
    $current_subject_id = 0;
    $current_question_id = 0;
    $current_answer_id = 0;
    $questionhash = array();
    // for each row
    while (list($item, $rowdata) = each($tsvrows)) {
        // get user data into array
        $qdata = explode("\t", $rowdata);
        switch ($qdata[0]) {
            case 'M': { // MODULE
                $current_module_id = 0;
                if (!isset($qdata[2]) or empty($qdata[2])) {
                    break;
                }
                $module_enabled = intval($qdata[1]);
                $module_name = F_escape_sql($db, F_tsv_to_text($qdata[2]), false);
                // check if this module already exist
                $sql = 'SELECT module_id
					FROM '.K_TABLE_MODULES.'
					WHERE module_name=\''.$module_name.'\'
					LIMIT 1';
                if ($r = F_db_query($sql, $db)) {
                    if ($m = F_db_fetch_array($r)) {
                        // get existing module ID
                        if (!F_isAuthorizedUser(K_TABLE_MODULES, 'module_id', $m['module_id'], 'module_user_id')) {
                            // unauthorized user
                            $current_module_id = 0;
                        } else {
                            $current_module_id = $m['module_id'];
                        }
                    } else {
                        // insert new module
                        $sql = 'INSERT INTO '.K_TABLE_MODULES.' (
							module_name,
							module_enabled,
							module_user_id
							) VALUES (
							\''.$module_name.'\',
							\''.$module_enabled.'\',
							\''.$_SESSION['session_user_id'].'\'
							)';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error();
                        } else {
                            // get new module ID
                            $current_module_id = F_db_insert_id($db, K_TABLE_MODULES, 'module_id');
                        }
                    }
                } else {
                    F_display_db_error();
                }
                break;
            }
            case 'S': { // SUBJECT
                $current_subject_id = 0;
                if ($current_module_id == 0) {
                    return;
                }
                if (!isset($qdata[2]) or empty($qdata[2])) {
                    break;
                }
                $subject_enabled = intval($qdata[1]);
                $subject_name = F_escape_sql($db, F_tsv_to_text($qdata[2]), false);
                $subject_description = '';
                if (isset($qdata[3])) {
                    $subject_description = F_empty_to_null(F_tsv_to_text($qdata[3]));
                }
                // check if this subject already exist
                $sql = 'SELECT subject_id
					FROM '.K_TABLE_SUBJECTS.'
					WHERE subject_name=\''.$subject_name.'\'
						AND subject_module_id='.$current_module_id.'
					LIMIT 1';
                if ($r = F_db_query($sql, $db)) {
                    if ($m = F_db_fetch_array($r)) {
                        // get existing subject ID
                        $current_subject_id = $m['subject_id'];
                    } else {
                        // insert new subject
                        $sql = 'INSERT INTO '.K_TABLE_SUBJECTS.' (
							subject_name,
							subject_description,
							subject_enabled,
							subject_user_id,
							subject_module_id
							) VALUES (
							\''.$subject_name.'\',
							'.$subject_description.',
							\''.$subject_enabled.'\',
							\''.$_SESSION['session_user_id'].'\',
							'.$current_module_id.'
							)';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error();
                        } else {
                            // get new subject ID
                            $current_subject_id = F_db_insert_id($db, K_TABLE_SUBJECTS, 'subject_id');
                        }
                    }
                } else {
                    F_display_db_error();
                }
                break;
            }
            case 'Q': { // QUESTION
                $current_question_id = 0;
                if (($current_module_id == 0) or ($current_subject_id == 0)) {
                    return;
                }
                if (!isset($qdata[5])) {
                    break;
                }
                $question_enabled = intval($qdata[1]);
                $question_description = F_escape_sql($db, F_tsv_to_text($qdata[2]), false);
                $question_explanation = F_empty_to_null(F_tsv_to_text($qdata[3]));
                $question_type = $qtype[$qdata[4]];
                $question_difficulty = intval($qdata[5]);
                if (isset($qdata[6])) {
                    $question_position = F_zero_to_null($qdata[6]);
                } else {
                    $question_position = F_zero_to_null(0);
                }
                if (isset($qdata[7])) {
                    $question_timer = intval($qdata[7]);
                } else {
                    $question_timer = 0;
                }
                if (isset($qdata[8])) {
                    $question_fullscreen = intval($qdata[8]);
                } else {
                    $question_fullscreen = 0;
                }
                if (isset($qdata[9])) {
                    $question_inline_answers = intval($qdata[9]);
                } else {
                    $question_inline_answers = 0;
                }
                if (isset($qdata[10])) {
                    $question_auto_next = intval($qdata[10]);
                } else {
                    $question_auto_next = 0;
                }
                // check if this question already exist
                $sql = 'SELECT question_id
					FROM '.K_TABLE_QUESTIONS.'
					WHERE ';
                if (K_DATABASE_TYPE == 'ORACLE') {
                    $sql .= 'dbms_lob.instr(question_description,\''.$question_description.'\',1,1)>0';
                } elseif ((K_DATABASE_TYPE == 'MYSQL') and K_MYSQL_QA_BIN_UNIQUITY) {
                    $sql .= 'question_description=\''.$question_description.'\' COLLATE utf8_bin';
                } else {
                    $sql .= 'question_description=\''.$question_description.'\'';
                }
                $sql .= ' AND question_subject_id='.$current_subject_id.' LIMIT 1';
                if ($r = F_db_query($sql, $db)) {
                    if ($m = F_db_fetch_array($r)) {
                        // get existing question ID
                        $current_question_id = $m['question_id'];
                        return;
                    }
                } else {
                    F_display_db_error();
                }
                if (K_DATABASE_TYPE == 'MYSQL') {
                    // this section is to avoid the problems on MySQL string comparison
                    $maxkey = 240;
                    $strkeylimit = min($maxkey, strlen($question_description));
                    $stop = $maxkey / 3;
                    while (in_array(md5(strtolower(substr($current_subject_id.$question_description, 0, $strkeylimit))), $questionhash) and ($stop > 0)) {
                        // a similar question was already imported, so we change it a little bit to avoid duplicate keys
                        $question_description = '_'.$question_description;
                        $strkeylimit = min($maxkey, ($strkeylimit + 1));
                        $stop--; // variable used to avoid infinite loop
                    }
                    if ($stop == 0) {
                        F_print_error('ERROR', 'Unable to get unique question ID');
                        return;
                    }
                }
                $sql = 'START TRANSACTION';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error();
                }
                // insert question
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
					'.$current_subject_id.',
					\''.$question_description.'\',
					'.$question_explanation.',
					\''.$question_type.'\',
					\''.$question_difficulty.'\',
					\''.$question_enabled.'\',
					'.$question_position.',
					\''.$question_timer.'\',
					\''.$question_fullscreen.'\',
					\''.$question_inline_answers.'\',
					\''.$question_auto_next.'\'
					)';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error(false);
                } else {
                    // get new question ID
                    $current_question_id = F_db_insert_id($db, K_TABLE_QUESTIONS, 'question_id');
                    if (K_DATABASE_TYPE == 'MYSQL') {
                        $questionhash[] = md5(strtolower(substr($current_subject_id.$question_description, 0, $strkeylimit)));
                    }
                }
                $sql = 'COMMIT';
                if (!$r = F_db_query($sql, $db)) {
                    F_display_db_error();
                }

                break;
            }
            case 'A': { // ANSWER
                $current_answer_id = 0;
                if (($current_module_id == 0) or ($current_subject_id == 0) or ($current_question_id == 0)) {
                    return;
                }
                if (!isset($qdata[4])) {
                    break;
                }
                $answer_enabled = intval($qdata[1]);
                $answer_description = F_escape_sql($db, F_tsv_to_text($qdata[2]), false);
                $answer_explanation = F_empty_to_null(F_tsv_to_text($qdata[3]));
                $answer_isright = intval($qdata[4]);
                if (isset($qdata[5])) {
                    $answer_position = F_zero_to_null($qdata[5]);
                } else {
                    $answer_position = F_zero_to_null(0);
                }
                if (isset($qdata[6])) {
                    $answer_keyboard_key = F_empty_to_null(F_tsv_to_text($qdata[6]));
                } else {
                    $answer_keyboard_key = F_empty_to_null('');
                }
                // check if this answer already exist
                $sql = 'SELECT answer_id
					FROM '.K_TABLE_ANSWERS.'
					WHERE ';
                if (K_DATABASE_TYPE == 'ORACLE') {
                    $sql .= 'dbms_lob.instr(answer_description, \''.$answer_description.'\',1,1)>0';
                } elseif ((K_DATABASE_TYPE == 'MYSQL') and K_MYSQL_QA_BIN_UNIQUITY) {
                    $sql .= 'answer_description=\''.$answer_description.'\' COLLATE utf8_bin';
                } else {
                    $sql .= 'answer_description=\''.$answer_description.'\'';
                }
                $sql .= ' AND answer_question_id='.$current_question_id.' LIMIT 1';
                if ($r = F_db_query($sql, $db)) {
                    if ($m = F_db_fetch_array($r)) {
                        // get existing subject ID
                        $current_answer_id = $m['answer_id'];
                    } else {
                        $sql = 'START TRANSACTION';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error();
                        }
                        $sql = 'INSERT INTO '.K_TABLE_ANSWERS.' (
							answer_question_id,
							answer_description,
							answer_explanation,
							answer_isright,
							answer_enabled,
							answer_position,
							answer_keyboard_key
							) VALUES (
							'.$current_question_id.',
							\''.$answer_description.'\',
							'.$answer_explanation.',
							\''.$answer_isright.'\',
							\''.$answer_enabled.'\',
							'.$answer_position.',
							'.$answer_keyboard_key.'
							)';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error(false);
                            F_db_query('ROLLBACK', $db);
                        } else {
                            // get new answer ID
                            $current_answer_id = F_db_insert_id($db, K_TABLE_ANSWERS, 'answer_id');
                        }
                        $sql = 'COMMIT';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error();
                        }
                    }
                } else {
                    F_display_db_error();
                }
                break;
            }
        } // end of switch
    } // end of while
    return true;
}

//============================================================+
// END OF FILE
//============================================================+
