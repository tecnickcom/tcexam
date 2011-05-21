<?php
//============================================================+
// File name   : tce_edit_answer.php
// Begin       : 2004-04-27
// Last Update : 2011-05-20
//
// Description : Edit answers.
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
//    Copyright (C) 2004-2011  Nicola Asuni - Tecnick.com S.r.l.
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
 * Display form to edit exam answers.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-04-27
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_ANSWERS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_answers_editor'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../code/tce_functions_tcecode_editor.php');
require_once('../code/tce_functions_auth_sql.php');

// upload multimedia files
$uploadedfile = array();
for ($id = 0; $id < 2; ++$id) {
	if(isset($_POST['sendfile'.$id]) AND ($_FILES['userfile'.$id]['name'])) {
		require_once('../code/tce_functions_upload.php');
		$uploadedfile['\''.$id.'\''] = F_upload_file('userfile'.$id, K_PATH_CACHE);
	}
}

// set default values
if(!isset($answer_id)) {
	$answer_id = 0;
}
if(!isset($answer_isright) OR (empty($answer_isright))) {
	$answer_isright = 0;
} else {
	$answer_isright = intval($answer_isright);
}
if(!isset($answer_enabled) OR (empty($answer_enabled))) {
	$answer_enabled = 0;
} else {
	$answer_enabled = intval($answer_enabled);
}
if (isset($selectmodule)) {
	$changemodule = 1;
}
if (isset($selectsubject)) {
	$changesubject = 1;
}
if (isset($selectcategory)) {
	$changecategory = 1;
}
if (!isset($answer_position) OR empty($answer_position)) {
	$answer_position = 0;
} else {
	$answer_position = intval($answer_position);
}
if (!isset($max_position) OR empty($max_position)) {
	$max_position = 0;
} else {
	$max_position = intval($max_position);
}
if (isset($prev_answer_position)) {
	$prev_answer_position = intval($prev_answer_position);
}
if (isset($subject_id)) {
	$subject_id = intval($subject_id);
}
if (isset($answer_id)) {
	$answer_id = intval($answer_id);
}
if (isset($answer_question_id)) {
	$answer_question_id = intval($answer_question_id);
}
if(!isset($answer_keyboard_key) OR (empty($answer_keyboard_key))) {
	$answer_keyboard_key = '';
} else {
	$answer_keyboard_key = intval($answer_keyboard_key);
}
if (isset($answer_description)) {
	$answer_description = utrim($answer_description);
}
if (isset($answer_explanation)) {
	$answer_explanation = utrim($answer_explanation);
} else {
	$answer_explanation = '';
}
$qtype = array('S', 'M', 'T', 'O'); // question types

// check user's authorization
if (isset($_REQUEST['answer_id']) AND ($_REQUEST['answer_id'] > 0)) {
	$answer_id = intval($_REQUEST['answer_id']);
	$sql = 'SELECT subject_module_id,question_subject_id,answer_question_id
		FROM '.K_TABLE_SUBJECTS.', '.K_TABLE_QUESTIONS.', '.K_TABLE_ANSWERS.'
		WHERE subject_id=question_subject_id
			AND question_id=answer_question_id
			AND answer_id='.$answer_id.'
		LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			// check user's authorization for parent module
			if (!F_isAuthorizedUser(K_TABLE_MODULES, 'module_id', $m['subject_module_id'], 'module_user_id')) {
				F_print_error('ERROR', $l['m_authorization_denied']);
					exit;
			}
		}
	} else {
		F_display_db_error();
	}
}

switch($menu_mode) {

	case 'delete':{
		F_stripslashes_formfields();
		// check if this record is used (test_log)
		if(!F_check_unique(K_TABLE_LOG_ANSWER, 'logansw_answer_id='.$answer_id.'')) {
			//this record will be only disabled and not deleted because it's used
			$sql = 'UPDATE '.K_TABLE_ANSWERS.' SET
				answer_enabled=\'0\'
				WHERE answer_id='.$answer_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error();
			}
			F_print_error('WARNING', $l['m_disabled_vs_deleted']);
		} else {
			// ask confirmation
			F_print_error('WARNING', $l['m_delete_confirm']);
			?>
			<div class="confirmbox">
			<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_delete">
			<div>
			<input type="hidden" name="answer_id" id="answer_id" value="<?php echo $answer_id; ?>" />
			<input type="hidden" name="subject_module_id" id="subject_module_id" value="<?php echo $subject_module_id; ?>" />
			<input type="hidden" name="question_subject_id" id="question_subject_id" value="<?php echo $question_subject_id; ?>" />
			<input type="hidden" name="answer_question_id" id="answer_question_id" value="<?php echo $answer_question_id; ?>" />
			<input type="hidden" name="answer_description" id="answer_description" value="<?php echo $answer_description; ?>" />
			<input type="hidden" name="answer_explanation" id="answer_explanation" value="<?php echo $answer_explanation; ?>" />
			<?php
			F_submit_button('forcedelete', $l['w_delete'], $l['h_delete']);
			F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
			?>
			</div>
			</form>
			</div>
		<?php
		}
		break;
	}

	case 'forcedelete':{
		F_stripslashes_formfields(); // Delete
		if($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)

			$sql = 'START TRANSACTION';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				break;
			}

			// get answer position (if defined)
			$sql = 'SELECT answer_position
				FROM '.K_TABLE_ANSWERS.'
				WHERE answer_id='.$answer_id.'
				LIMIT 1';
			if($r = F_db_query($sql, $db)) {
				if($m = F_db_fetch_array($r)) {
					$answer_position = $m['answer_position'];
				}
			} else {
				F_display_db_error();
			}
			// delete answer
			$sql = 'DELETE FROM '.K_TABLE_ANSWERS.' WHERE answer_id='.$answer_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				F_db_query('ROLLBACK', $db); // rollback transaction
			} else {
				$answer_id=FALSE;
				// adjust questions ordering
				if ($answer_position > 0) {
					$sql = 'UPDATE '.K_TABLE_ANSWERS.' SET
						answer_position=answer_position-1
						WHERE answer_question_id='.$answer_question_id.'
							AND answer_position>'.$answer_position.'';
					if(!$r = F_db_query($sql, $db)) {
						F_display_db_error(false);
						F_db_query('ROLLBACK', $db); // rollback transaction
					}
				}

				$sql = 'COMMIT';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
					break;
				}

				F_print_error('MESSAGE', $l['m_deleted']);
			}
		}
		break;
	}

	case 'update':{ // Update
		if($formstatus = F_check_form_fields()) {
			// get previous answer position (if defined)
			$prev_answer_position = 0;
			$sql = 'SELECT answer_position
				FROM '.K_TABLE_ANSWERS.'
				WHERE answer_id='.$answer_id.'
				LIMIT 1';
			if($r = F_db_query($sql, $db)) {
				if($m = F_db_fetch_array($r)) {
					$prev_answer_position = intval($m['answer_position']);
				}
			} else {
				F_display_db_error();
			}

			// check referential integrity (NOTE: mysql do not support "ON UPDATE" constraint)
			if(!F_check_unique(K_TABLE_LOG_ANSWER, 'logansw_answer_id='.$answer_id.'')) {
				F_print_error('WARNING', $l['m_update_restrict']);

				// when the answer is disabled, the position is discarded
				if (!$answer_enabled) {
					$answer_position = 0;
				} else {
					$answer_position = $prev_answer_position;
				}
				// enable or disable record
				$sql = 'UPDATE '.K_TABLE_ANSWERS.' SET
					answer_enabled=\''.$answer_enabled.'\',
					answer_position='.F_zero_to_null($answer_position).'
					WHERE answer_id='.$answer_id.'';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				} else {
					$strmsg = $l['w_record_status'].': ';
					if ($answer_enabled) {
						$strmsg .= $l['w_enabled'];
					} else {
						$strmsg .= $l['w_disabled'];
					}
					F_print_error('MESSAGE', $strmsg);
				}
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			// check if alternate key is unique
			if (K_DATABASE_TYPE == 'ORACLE') {
				$chksql = 'dbms_lob.instr(answer_description,\''.F_escape_sql($answer_description).'\',1,1)>0';
			} else {
				$chksql = 'answer_description=\''.F_escape_sql($answer_description).'\'';
			}
			if ($answer_position > 0) {
				$chksql .= ' AND answer_position='.$answer_position;
			}
			if(!F_check_unique(K_TABLE_ANSWERS, $chksql.' AND answer_question_id='.$answer_question_id, 'answer_id', $answer_id)) {
				F_print_error('WARNING', $l['m_duplicate_answer']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}

			$sql = 'START TRANSACTION';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				break;
			}

			// when the answer is disabled, the position is discarded
			if (!$answer_enabled) {
				$answer_position = 0;
			}
			if ($answer_position > $max_position) {
				$answer_position = $max_position;
			}
			// arrange positions if necessary
			if ($answer_position != $prev_answer_position) {
				if ($answer_position > 0) {
					if ($prev_answer_position > 0) {
						// swap positions
						$sql = 'UPDATE '.K_TABLE_ANSWERS.' SET
							answer_position='.$prev_answer_position.'
							WHERE answer_question_id='.$answer_question_id.'
								AND answer_position='.$answer_position.'';
					} elseif ($prev_answer_position == 0) {
						// right shift positions
						$sql = 'UPDATE '.K_TABLE_ANSWERS.' SET
							answer_position=answer_position+1
							WHERE answer_question_id='.$answer_question_id.'
								AND answer_position>='.$answer_position.'';
					}
				} else {
					// left shift positions
					$sql = 'UPDATE '.K_TABLE_ANSWERS.' SET
						answer_position=answer_position-1
						WHERE answer_question_id='.$answer_question_id.'
							AND answer_position>'.$prev_answer_position.'';
				}
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
					F_db_query('ROLLBACK', $db); // rollback transaction
				}
			}
			// update field
			$sql = 'UPDATE '.K_TABLE_ANSWERS.' SET
				answer_question_id='.$answer_question_id.',
				answer_description=\''.F_escape_sql($answer_description).'\',
				answer_explanation='.F_empty_to_null($answer_explanation).',
				answer_isright=\''.$answer_isright.'\',
				answer_enabled=\''.$answer_enabled.'\',
				answer_position='.F_zero_to_null($answer_position).',
				answer_keyboard_key='.F_empty_to_null($answer_keyboard_key).'
				WHERE answer_id='.$answer_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				F_db_query('ROLLBACK', $db); // rollback transaction
			} else {
				F_print_error('MESSAGE', $l['m_updated']);
			}

			$sql = 'COMMIT';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				break;
			}
		}
		break;
	}

	case 'add':{ // Add
		if($formstatus = F_check_form_fields()) {
			// check if alternate key is unique
			if (K_DATABASE_TYPE == 'ORACLE') {
				$chksql = 'dbms_lob.instr(answer_description,\''.F_escape_sql($answer_description).'\',1,1)>0';
			} else {
				$chksql = 'answer_description=\''.F_escape_sql($answer_description).'\'';
			}
			if ($answer_position > 0) {
				$chksql .= ' AND answer_position='.$answer_position;
			}
			if(!F_check_unique(K_TABLE_ANSWERS, $chksql.' AND answer_question_id='.$answer_question_id)) {
				F_print_error('WARNING', $l['m_duplicate_answer']);
				$formstatus = FALSE;
				F_stripslashes_formfields();
				break;
			}

			$sql = 'START TRANSACTION';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				break;
			}
			// adjust questions ordering
			if ($answer_position > 0) {
				$sql = 'UPDATE '.K_TABLE_ANSWERS.' SET
					answer_position=answer_position+1
					WHERE answer_question_id='.$answer_question_id.'
						AND answer_position>='.$answer_position.'';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
					F_db_query('ROLLBACK', $db); // rollback transaction
				}
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
				'.$answer_question_id.',
				\''.F_escape_sql($answer_description).'\',
				'.F_empty_to_null($answer_explanation).',
				\''.$answer_isright.'\',
				\''.$answer_enabled.'\',
				'.F_zero_to_null($answer_position).',
				'.F_empty_to_null($answer_keyboard_key).'
				)';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				F_db_query('ROLLBACK', $db); // rollback transaction
			} else {
				$answer_id = F_db_insert_id($db, K_TABLE_ANSWERS, 'answer_id');
			}
			$sql = 'COMMIT';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				break;
			}
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$answer_description = '';
		$answer_explanation = '';
		$answer_isright = false;
		$answer_enabled = true;
		$answer_position = 0;
		$answer_keyboard_key = '';
		break;
	}

	default :{
		break;
	}

} //end of switch

// select default module/subject (if not specified)
if(!(isset($subject_module_id) AND ($subject_module_id > 0))) {
	$sql = F_select_modules_sql().' LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$subject_module_id = $m['module_id'];
		} else {
			$subject_module_id = 0;
		}
	} else {
		F_display_db_error();
	}
}

// select default subject
if ((isset($changemodule) AND ($changemodule > 0))
	OR (!(isset($question_subject_id) AND ($question_subject_id > 0)))) {
	$sql = F_select_subjects_sql('subject_module_id='.$subject_module_id.'').' LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$question_subject_id = $m['subject_id'];
		} else {
			$question_subject_id = 0;
		}
	} else {
		F_display_db_error();
	}
}

// select default question
if ((isset($changesubject) AND ($changesubject > 0))
	OR (isset($changemodule) AND ($changemodule > 0))
	OR (!(isset($answer_question_id) AND ($answer_question_id > 0)))) {
	$sql = 'SELECT question_id
		FROM '.K_TABLE_QUESTIONS.'
		WHERE question_subject_id='.$question_subject_id.'
		ORDER BY ';
	if (K_DATABASE_TYPE == 'ORACLE') {
		$sql .= 'CAST(question_description as varchar2(100))';
	} else {
		$sql .= 'question_description LIMIT 1';
	}
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$answer_question_id = $m['question_id'];
		} else {
			$answer_question_id = 0;
		}
	} else {
		F_display_db_error();
	}
}

// --- Initialize variables
if ($formstatus) {
	if ($menu_mode != 'clear') {
		if ((isset($changemodule) AND ($changemodule > 0))
			OR (isset($changesubject) AND ($changesubject > 0))
			OR (isset($changecategory) AND ($changecategory > 0))
			OR (!isset($answer_id)) OR empty($answer_id)) {
			$sql = 'SELECT *
				FROM '.K_TABLE_ANSWERS.'
				WHERE answer_question_id='.$answer_question_id.'
				ORDER BY answer_enabled DESC, answer_position, answer_isright DESC,';
			if (K_DATABASE_TYPE == 'ORACLE') {
				$sql .= 'CAST(answer_description as varchar2(100))';
			} else {
				$sql .= 'answer_description LIMIT 1';
			}
		} else {
			$sql = 'SELECT *
				FROM '.K_TABLE_ANSWERS.'
				WHERE answer_id='.$answer_id.'
				LIMIT 1';
		}
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				$answer_id = $m['answer_id'];
				$answer_question_id = $m['answer_question_id'];
				$answer_description = $m['answer_description'];
				$answer_explanation = $m['answer_explanation'];
				$answer_isright = F_getBoolean($m['answer_isright']);
				$answer_enabled = F_getBoolean($m['answer_enabled']);
				$answer_position = $m['answer_position'];
				$answer_keyboard_key = $m['answer_keyboard_key'];
			} else {
				$answer_description = '';
				$answer_explanation = '';
				$answer_isright = false;
				$answer_enabled = true;
				$answer_position = 0;
				$answer_keyboard_key = '';
			}
		} else {
			F_display_db_error();
		}
	}
}

if (!isset($subject_module_id) OR ($subject_module_id <= 0) OR !isset($question_subject_id) OR ($question_subject_id <= 0) OR !isset($answer_question_id) OR ($answer_question_id <= 0)) {
	echo '<div class="container">'.K_NEWLINE;
	echo '<p><a href="tce_edit_question.php" title="'.$l['t_questions_editor'].'" class="xmlbutton">&lt; '.$l['t_questions_editor'].'</a></p>'.K_NEWLINE;
	echo '<div class="pagehelp">'.$l['hp_edit_answer'].'</div>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
	require_once('../code/tce_page_footer.php');
	exit;
}

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_answereditor">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="subject_module_id">'.$l['w_module'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="hidden" name="changemodule" id="changemodule" value="" />'.K_NEWLINE;
echo '<select name="subject_module_id" id="subject_module_id" size="0" onchange="document.getElementById(\'form_answereditor\').changemodule.value=1; document.getElementById(\'form_answereditor\').submit();" title="'.$l['w_module'].'">'.K_NEWLINE;
$sql = F_select_modules_sql();
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['module_id'].'"';
		if($m['module_id'] == $subject_module_id) {
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
echo '<input type="hidden" name="changesubject" id="changesubject" value="" />'.K_NEWLINE;
echo '<select name="question_subject_id" id="question_subject_id" size="0" onchange="document.getElementById(\'form_answereditor\').changesubject.value=1; document.getElementById(\'form_answereditor\').submit();" title="'.$l['h_subject'].'">'.K_NEWLINE;
$countitem = 1; //number of already inserted answers
$sql = F_select_subjects_sql('subject_module_id='.$subject_module_id);
if($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['subject_id'].'"';
		if($m['subject_id'] == $question_subject_id) {
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

echo getFormNoscriptSelect('selectsubject');

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="answer_question_id">'.$l['w_question'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="hidden" name="changecategory" id="changecategory" value="" />'.K_NEWLINE;
echo '<select name="answer_question_id" id="answer_question_id" size="0" onchange="document.getElementById(\'form_answereditor\').changecategory.value=1; document.getElementById(\'form_answereditor\').submit()" title="'.$l['h_question'].'">'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_QUESTIONS.' WHERE question_subject_id='.intval($question_subject_id).' ORDER BY question_subject_id,';
if (K_DATABASE_TYPE == 'ORACLE') {
	$sql .= 'CAST(question_description as varchar2(100))';
} else {
	$sql .= 'question_description';
}
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['question_id'].'"';
		if($m['question_id'] == $answer_question_id) {
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
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectcategory');

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="answer_id">'.$l['w_answer'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="answer_id" id="answer_id" size="0" onchange="document.getElementById(\'form_answereditor\').submit()" title="'.$l['h_answer'].'">'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_ANSWERS.' WHERE answer_question_id='.intval($answer_question_id).' ORDER BY answer_position, answer_enabled DESC, answer_isright DESC,';
	if (K_DATABASE_TYPE == 'ORACLE') {
		$sql .= 'CAST(answer_description as varchar2(100))';
	} else {
		$sql .= 'answer_description';
	}
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['answer_id'].'"';
		if($m['answer_id'] == $answer_id) {
			echo ' selected="selected"';
		}
		echo '>'.$countitem.'. ';
		if (!F_getBoolean($m['answer_enabled'])) {
			echo '-';
		} elseif (F_getBoolean($m['answer_isright'])) {
			echo 'T';
		} else {
			echo 'F';
		}
		echo ' '.htmlspecialchars(F_substr_utf8(F_remove_tcecode($m['answer_description']), 0, K_SELECT_SUBSTRING), ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
		$countitem++;
	}
	if ($countitem == 1) {
		echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
	}
} else {
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="answer_description">'.$l['w_answer'].'</label>'.K_NEWLINE;
echo '<br />'.K_NEWLINE;

echo '<a href="#" title="'.$l['h_preview'].'" class="xmlbutton" onclick="previewWindow=window.open(\'tce_preview_tcecode.php?tcexamcode=\'+encodeURIComponent(document.getElementById(\'form_answereditor\').answer_description.value),\'previewWindow\',\'dependent,height=500,width=500,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'.$l['w_preview'].'</a>'.K_NEWLINE;

echo '</span>'.K_NEWLINE;
echo '<span class="formw" style="border:1px solid #808080;">'.K_NEWLINE;
echo '<textarea cols="50" rows="10" name="answer_description" id="answer_description" onselect="FJ_update_selection(document.getElementById(\'form_answereditor\').answer_description)" title="'.$l['h_answer'].'">'.htmlspecialchars($answer_description, ENT_NOQUOTES, $l['a_meta_charset']).'</textarea>'.K_NEWLINE;
echo '<br />'.K_NEWLINE;
echo tcecodeEditorTagButtons('form_answereditor', 'answer_description', 0);
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

if (K_ENABLE_ANSWER_EXPLANATION) {
	echo '<div class="row">'.K_NEWLINE;
	echo '<span class="label">'.K_NEWLINE;
	echo '<label for="answer_explanation">'.$l['w_explanation'].'</label>'.K_NEWLINE;
	echo '<br />'.K_NEWLINE;
	$showexplanationarea = 'javascript:if(document.getElementById(\'explanationarea\').style.display==\'none\'){document.getElementById(\'explanationarea\').style.display=\'block\';document.getElementById(\'showexplanationarea\').style.display=\'none\';document.getElementById(\'hideexplanationarea\').style.display=\'block\';}';
	echo '<span id="showexplanationarea"><a class="xmlbutton" href="#" onclick="'.$showexplanationarea.'" title="'.$l['w_show'].'">'.$l['w_show'].' &rarr;</a></span>';
	$hideexplanationarea = 'javascript:if(document.getElementById(\'explanationarea\').style.display==\'block\'){document.getElementById(\'explanationarea\').style.display=\'none\';document.getElementById(\'showexplanationarea\').style.display=\'block\';document.getElementById(\'hideexplanationarea\').style.display=\'none\';}';
	echo '<span id="hideexplanationarea" style="display:none;">';
	echo '<a href="#" title="'.$l['h_preview'].'" class="xmlbutton" onclick="previewWindow=window.open(\'tce_preview_tcecode.php?tcexamcode=\'+encodeURIComponent(document.getElementById(\'form_answereditor\').answer_explanation.value),\'previewWindow\',\'dependent,height=500,width=500,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'.$l['w_preview'].'</a>'.K_NEWLINE;
	echo '<a class="xmlbutton" href="#" onclick="'.$hideexplanationarea.'" title="'.$l['w_hide'].'">'.$l['w_hide'].'</a> ';
	echo '</span>';
	echo '</span>'.K_NEWLINE;
	echo '<span id="explanationarea" class="formw" style="display:none;border:1px solid #808080;">'.K_NEWLINE;
	echo '<textarea cols="50" rows="10" name="answer_explanation" id="answer_explanation" onselect="FJ_update_selection(document.getElementById(\'form_answereditor\').answer_explanation)" title="'.$l['h_explanation'].'">'.htmlspecialchars($answer_explanation, ENT_NOQUOTES, $l['a_meta_charset']).'</textarea>'.K_NEWLINE;
	echo '<br />'.K_NEWLINE;
	echo tcecodeEditorTagButtons('form_answereditor', 'answer_explanation', 1);
	echo '</span>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
}

echo getFormRowCheckBox('answer_isright', $l['w_right'], $l['h_answer_isright'], '', 1, $answer_isright, false, '');
echo getFormRowCheckBox('answer_enabled', $l['w_enabled'], $l['h_enabled'], '', 1, $answer_enabled, false, '');

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="answer_position">'.$l['w_position'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="answer_position" id="answer_position" size="0" title="'.$l['h_position'].'">'.K_NEWLINE;
if (isset($answer_id) AND ($answer_id > 0)) {
	$max_position = (1 + F_count_rows(K_TABLE_ANSWERS, 'WHERE answer_question_id='.$answer_question_id.' AND answer_position>0 AND answer_id<>'.$answer_id.''));
} else {
	$max_position = 0;
}
echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
for ($pos=1; $pos <= $max_position; ++$pos) {
	echo '<option value="'.$pos.'"';
	if($pos == $answer_position) {
		echo ' selected="selected"';
	}
	echo '>'.$pos.'</option>'.K_NEWLINE;
}
echo '<option value="'.($max_position + 1).'" style="color:#ff0000">'.($max_position + 1).'</option>'.K_NEWLINE;
echo '</select>'.K_NEWLINE;
echo '<input type="hidden" name="max_position" id="max_position" value="'.$max_position.'" />'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="answer_keyboard_key">'.$l['w_keyboard_key'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="answer_keyboard_key" id="answer_keyboard_key" size="0" title="'.$l['h_answer_keyboard_key'].'">'.K_NEWLINE;
echo '<option value="">&nbsp;</option>'.K_NEWLINE;
for ($ascii = 32; $ascii <= 126; ++$ascii) {
	echo '<option value="'.$ascii.'"';
	if($ascii == $answer_keyboard_key) {
		echo ' selected="selected"';
	}
	echo '>';
	if ($ascii == 32) {
		echo 'SP';
	} else {
		echo htmlspecialchars(chr($ascii), ENT_NOQUOTES, $l['a_meta_charset']);
	}
	echo '</option>'.K_NEWLINE;
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($answer_id) AND ($answer_id > 0)) {
	F_submit_button('update', $l['w_update'], $l['h_update']);
	F_submit_button('delete', $l['w_delete'], $l['h_delete']);
}
F_submit_button('add', $l['w_add'], $l['h_add']);
F_submit_button('clear', $l['w_clear'], $l['h_clear']);

echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="left">'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;

if (isset($answer_question_id) AND ($answer_question_id > 0)) {
	echo '<a href="tce_edit_question.php?subject_module_id='.$subject_module_id.'&amp;question_subject_id='.$question_subject_id.'&amp;question_id='.$answer_question_id.'" title="'.$l['t_questions_editor'].'" class="xmlbutton">&lt; '.$l['t_questions_editor'].'</a>';
}

echo '</span>'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;
// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="answer_description" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_description'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo '<div class="rowl" title="'.$l['h_preview'].'">'.K_NEWLINE;
echo $l['w_preview'];
echo '<div class="preview">'.K_NEWLINE;

echo F_decode_tcecode($answer_description);

echo '&nbsp;'.K_NEWLINE;
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_edit_answer'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
