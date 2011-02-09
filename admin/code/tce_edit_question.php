<?php
//============================================================+
// File name   : tce_edit_question.php
// Begin       : 2004-04-27
// Last Update : 2011-02-09
//
// Description : Edit questions
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
if(!isset($question_id)) {
	$question_id = 0;
}
if(!isset($question_type) OR (empty($question_type))) {
	$question_type = 1;
} else {
	$question_type = intval($question_type);
}
if(!isset($question_difficulty)) {
	$question_difficulty = 1;
} else {
	$question_difficulty = intval($question_difficulty);
}
if(!isset($question_enabled) OR (empty($question_enabled))) {
	$question_enabled = 0;
} else {
	$question_enabled = intval($question_enabled);
}
if (isset($selectmodule)) {
	$changemodule = 1;
}
if (isset($selectcategory)) {
	$changecategory = 1;
}
if (isset($subject_id)) {
	$subject_id = intval($subject_id);
}
if (isset($question_id)) {
	$question_id = intval($question_id);
}
if (isset($question_subject_id)) {
	$question_subject_id = intval($question_subject_id);
}
if (!isset($max_position) OR empty($max_position)) {
	$max_position = 0;
} else {
	$max_position = intval($max_position);
}
if (!isset($question_position) OR empty($question_position)) {
	$question_position = 0;
} else {
	$question_position = intval($question_position);
}
if(!isset($question_timer) OR (empty($question_timer))) {
	$question_timer = 0;
} else {
	$question_timer = intval($question_timer);
}
if(!isset($question_fullscreen) OR (empty($question_fullscreen))) {
	$question_fullscreen = 0;
} else {
	$question_fullscreen = intval($question_fullscreen);
}
if(!isset($question_inline_answers) OR (empty($question_inline_answers))) {
	$question_inline_answers = 0;
} else {
	$question_inline_answers = intval($question_inline_answers);
}
if(!isset($question_auto_next) OR (empty($question_auto_next))) {
	$question_auto_next = 0;
} else {
	$question_auto_next = intval($question_auto_next);
}
if (isset($question_description)) {
	$question_description = utrim($question_description);
}
if (isset($question_explanation)) {
	$question_explanation = utrim($question_explanation);
} else {
	$question_explanation = '';
}
$qtype = array('S', 'M', 'T', 'O'); // question types

// check user's authorization
if (isset($_REQUEST['question_id']) AND ($_REQUEST['question_id'] > 0)) {
	$question_id = intval($_REQUEST['question_id']);
	$sql = 'SELECT subject_module_id,question_subject_id
		FROM '.K_TABLE_SUBJECTS.', '.K_TABLE_QUESTIONS.'
		WHERE subject_id=question_subject_id
			AND question_id='.$question_id.'
		LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			// check user's authorization for parent subject
			if (!F_isAuthorizedUser(K_TABLE_SUBJECTS, 'subject_id', $m['question_subject_id'], 'subject_user_id')) {
				F_print_error('ERROR', $l['m_authorization_denied']);
				exit;
			} else {
				// check user's authorization for parent module
				if (!F_isAuthorizedUser(K_TABLE_MODULES, 'module_id', $m['subject_module_id'], 'module_user_id')) {
					F_print_error('ERROR', $l['m_authorization_denied']);
					exit;
				}
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
		if(!F_check_unique(K_TABLE_TESTS_LOGS, 'testlog_question_id='.$question_id.'')) {
			//this record will be only disabled and not deleted because it's used
			$sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
				question_enabled=\'0\'
				WHERE question_id='.$question_id.'';
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
			<input type="hidden" name="question_id" id="question_id" value="<?php echo $question_id; ?>" />
			<input type="hidden" name="subject_module_id" id="subject_module_id" value="<?php echo $subject_module_id; ?>" />
			<input type="hidden" name="question_subject_id" id="question_subject_id" value="<?php echo $question_subject_id; ?>" />
			<input type="hidden" name="question_description" id="question_description" value="<?php echo $question_description; ?>" />
			<input type="hidden" name="question_explanation" id="question_explanation" value="<?php echo $question_explanation; ?>" />
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

			// get question position (if defined)
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
			// delete question
			$sql = 'DELETE FROM '.K_TABLE_QUESTIONS.' WHERE question_id='.$question_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				F_db_query('ROLLBACK', $db); // rollback transaction
			} else {
				$question_id=FALSE;
				// adjust questions ordering
				if ($question_position > 0) {
					$sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
						question_position=question_position-1
						WHERE question_subject_id='.$question_subject_id.'
							AND question_position>'.$question_position.'';
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
			// get previous question position (if defined)
			$prev_question_position = 0;
			$sql = 'SELECT question_position
				FROM '.K_TABLE_QUESTIONS.'
				WHERE question_id='.$question_id.'
				LIMIT 1';
			if($r = F_db_query($sql, $db)) {
				if($m = F_db_fetch_array($r)) {
					$prev_question_position = intval($m['question_position']);
				}
			} else {
				F_display_db_error();
			}

			// check referential integrity (NOTE: mysql do not support "ON UPDATE" constraint)
			if(!F_check_unique(K_TABLE_TESTS_LOGS, 'testlog_question_id='.$question_id.'')) {
				F_print_error('WARNING', $l['m_update_restrict']);
				// when the question is disabled, the position is discarded
				if (!$question_enabled) {
					$question_position = 0;
				} else {
					$question_position = $prev_question_position;
				}
				// enable or disable record
				$sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
					question_enabled=\''.$question_enabled.'\',
					question_position='.F_zero_to_null($question_position).'
					WHERE question_id='.$question_id.'';
				if(!$r = F_db_query($sql, $db)) {
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

				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			// check if alternate key is unique
			if (K_DATABASE_TYPE == 'ORACLE') {
				$chksql = 'dbms_lob.instr(question_description,\''.F_escape_sql($question_description).'\',1,1)>0';
			} else {
				$chksql = 'question_description=\''.F_escape_sql($question_description).'\'';
			}
			if(!F_check_unique(K_TABLE_QUESTIONS, $chksql.' AND question_subject_id='.$question_subject_id.'', 'question_id', $question_id)) {
				F_print_error('WARNING', $l['m_duplicate_question']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}

			$sql = 'START TRANSACTION';
			if(!$r = F_db_query($sql, $db)) {
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
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
					F_db_query('ROLLBACK', $db); // rollback transaction
				}
			}
			$sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
				question_subject_id='.$question_subject_id.',
				question_description=\''.F_escape_sql($question_description).'\',
				question_explanation='.F_empty_to_null($question_explanation).',
				question_type=\''.$question_type.'\',
				question_difficulty=\''.$question_difficulty.'\',
				question_enabled=\''.$question_enabled.'\',
				question_position='.F_zero_to_null($question_position).',
				question_timer=\''.$question_timer.'\',
				question_fullscreen=\''.$question_fullscreen.'\',
				question_inline_answers=\''.$question_inline_answers.'\',
				question_auto_next=\''.$question_auto_next.'\'
				WHERE question_id='.$question_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
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
				$chksql = 'dbms_lob.instr(question_description,\''.F_escape_sql($question_description).'\',1,1)>0';
			} else {
				$chksql = 'question_description=\''.F_escape_sql($question_description).'\'';
			}
			if(!F_check_unique(K_TABLE_QUESTIONS, $chksql.' AND question_subject_id='.$question_subject_id.'')) {
				F_print_error('WARNING', $l['m_duplicate_question']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'START TRANSACTION';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
				break;
			}
			// adjust questions ordering
			if ($question_position > 0) {
				$sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
					question_position=question_position+1
					WHERE question_subject_id='.$question_subject_id.'
						AND question_position>='.$question_position.'';
				if(!$r = F_db_query($sql, $db)) {
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
				\''.F_escape_sql($question_description).'\',
				'.F_empty_to_null($question_explanation).',
				\''.$question_type.'\',
				\''.$question_difficulty.'\',
				\''.$question_enabled.'\',
				'.F_zero_to_null($question_position).',
				\''.$question_timer.'\',
				\''.$question_fullscreen.'\',
				\''.$question_inline_answers.'\',
				\''.$question_auto_next.'\'
				)';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$question_id = F_db_insert_id($db, K_TABLE_QUESTIONS, 'question_id');
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

// select subject
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

// --- Initialize variables
if($formstatus) {
	if ($menu_mode != 'clear') {
		if ((isset($changemodule) AND ($changemodule > 0))
			OR (isset($changecategory) AND ($changecategory > 0))
			OR (!isset($question_id)) OR empty($question_id)) {
			$sql = 'SELECT *
				FROM '.K_TABLE_QUESTIONS.'
				WHERE question_subject_id='.$question_subject_id.'
				ORDER BY question_position,';
			if (K_DATABASE_TYPE == 'ORACLE') {
				$sql .= 'CAST(question_description as varchar2(100))';
			} else {
				$sql .= 'question_description LIMIT 1';
			}
		} else {
			$sql = 'SELECT *
				FROM '.K_TABLE_QUESTIONS.'
				WHERE question_id='.$question_id.'
				LIMIT 1';
		}
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
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

if (!isset($subject_module_id) OR ($subject_module_id <= 0) OR !isset($question_subject_id) OR ($question_subject_id <= 0)) {
	echo '<div class="container">'.K_NEWLINE;
	echo '<p><a href="tce_edit_subject.php" title="'.$l['t_subjects_editor'].'" class="xmlbutton">&lt; '.$l['t_subjects_editor'].'</a></p>'.K_NEWLINE;
	echo '<div class="pagehelp">'.$l['hp_edit_question'].'</div>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
	require_once('../code/tce_page_footer.php');
	exit;
}
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_questioneditor">

<div class="row">
<span class="label">
<label for="subject_module_id"><?php echo $l['w_module']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="changemodule" id="changemodule" value="" />
<select name="subject_module_id" id="subject_module_id" size="0" onchange="document.getElementById('form_questioneditor').changemodule.value=1; document.getElementById('form_questioneditor').submit();" title="<?php echo $l['w_module']; ?>">
<?php
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
?>
</select>
</span>
</div>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectmodule" id="selectmodule" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row">
<span class="label">
<label for="question_subject_id"><?php echo $l['w_subject']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="changecategory" id="changecategory" value="" />
<select name="question_subject_id" id="question_subject_id" size="0" onchange="document.getElementById('form_questioneditor').changecategory.value=1; document.getElementById('form_questioneditor').submit();" title="<?php echo $l['h_subject']; ?>">
<?php
$sql = F_select_subjects_sql('subject_module_id='.$subject_module_id);
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
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
?>
</select>
</span>
</div>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectcategory" id="selectcategory" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row">
<span class="label">
<label for="question_id"><?php echo $l['w_question']; ?></label>
</span>
<span class="formw">
<select name="question_id" id="question_id" size="0" onchange="document.getElementById('form_questioneditor').submit()" title="<?php echo $l['h_question']; ?>">
<?php
$sql = 'SELECT * FROM '.K_TABLE_QUESTIONS.' WHERE question_subject_id='.intval($question_subject_id).' ORDER BY question_enabled DESC, question_position,';
if (K_DATABASE_TYPE == 'ORACLE') {
	$sql .= 'CAST(question_description as varchar2(100))';
} else {
	$sql .= 'question_description';
}
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['question_id'].'"';
		if($m['question_id'] == $question_id) {
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
}
else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
?>
</select>
</span>
</div>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectrecord" id="selectrecord" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row"><hr /></div>

<div class="row">
<span class="label">
<label for="question_description"><?php echo $l['w_question']; ?></label>
<br />
<?php
echo '<a href="#" title="'.$l['h_preview'].'" class="xmlbutton" onclick="previewWindow=window.open(\'tce_preview_tcecode.php?tcexamcode=\'+encodeURIComponent(document.getElementById(\'form_questioneditor\').question_description.value),\'previewWindow\',\'dependent,height=500,width=500,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'.$l['w_preview'].'</a>'.K_NEWLINE;
?>
</span>
<span class="formw" style="border:1px solid #808080;">
<textarea cols="50" rows="10" name="question_description" id="question_description" onselect="FJ_update_selection(document.getElementById('form_questioneditor').question_description)" title="<?php echo $l['h_question_description']; ?>"><?php echo htmlspecialchars($question_description, ENT_NOQUOTES, $l['a_meta_charset']); ?></textarea>
<br />
<?php echo tcecodeEditorTagButtons('form_questioneditor', 'question_description', 0); ?>
</span>
</div>

<?php
if (K_ENABLE_QUESTION_EXPLANATION) {
	echo '<div class="row">'.K_NEWLINE;
	echo '<span class="label">'.K_NEWLINE;
	echo '<label for="question_explanation">'.$l['w_explanation'].'</label>'.K_NEWLINE;
	echo '<br />'.K_NEWLINE;
	$showexplanationarea = 'javascript:if(document.getElementById(\'explanationarea\').style.display==\'none\'){document.getElementById(\'explanationarea\').style.display=\'block\';document.getElementById(\'showexplanationarea\').style.display=\'none\';document.getElementById(\'hideexplanationarea\').style.display=\'block\';}';
	echo '<span id="showexplanationarea"><a class="xmlbutton" href="#" onclick="'.$showexplanationarea.'" title="'.$l['w_show'].'">'.$l['w_show'].' &rarr;</a></span>';
	$hideexplanationarea = 'javascript:if(document.getElementById(\'explanationarea\').style.display==\'block\'){document.getElementById(\'explanationarea\').style.display=\'none\';document.getElementById(\'showexplanationarea\').style.display=\'block\';document.getElementById(\'hideexplanationarea\').style.display=\'none\';}';
	echo '<span id="hideexplanationarea" style="display:none;">';
	echo '<a href="#" title="'.$l['h_preview'].'" class="xmlbutton" onclick="previewWindow=window.open(\'tce_preview_tcecode.php?tcexamcode=\'+encodeURIComponent(document.getElementById(\'form_questioneditor\').question_explanation.value),\'previewWindow\',\'dependent,height=500,width=500,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'.$l['w_preview'].'</a>'.K_NEWLINE;
	echo '<a class="xmlbutton" href="#" onclick="'.$hideexplanationarea.'" title="'.$l['w_hide'].'">'.$l['w_hide'].'</a> ';
	echo '</span>';
	echo '</span>'.K_NEWLINE;
	echo '<span id="explanationarea" class="formw" style="display:none;border:1px solid #808080;">'.K_NEWLINE;
	echo '<textarea cols="50" rows="10" name="question_explanation" id="question_explanation" onselect="FJ_update_selection(document.getElementById(\'form_questioneditor\').question_explanation)" title="'.$l['h_explanation'].'">'.htmlspecialchars($question_explanation, ENT_NOQUOTES, $l['a_meta_charset']).'</textarea>'.K_NEWLINE;
	echo '<br />'.K_NEWLINE;
	echo tcecodeEditorTagButtons('form_questioneditor', 'question_explanation', 1);
	echo '</span>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
}
?>

<div class="row">
<span class="label">
&nbsp;
</span>
<div class="formw">
<fieldset class="noborder">
<legend title="<?php echo $l['h_question_type']; ?>"><?php echo $l['w_type']; ?></legend>

<?php
echo '<input type="radio" name="question_type" id="single_answer" value="1"';
if($question_type==1) {echo ' checked="checked"';}
echo ' title="'.$l['h_enable_single_answer'].'" />';
?>
<label for="single_answer"><?php echo $l['w_single_answer']; ?></label>
<br />
<?php
echo '<input type="radio" name="question_type" id="multiple_answers" value="2"';
if($question_type==2) {echo ' checked="checked"';}
echo ' title="'.$l['h_enable_multiple_answers'].'" />';
?>
<label for="multiple_answers"><?php echo $l['w_multiple_answers']; ?></label>
<br />
<?php
echo '<input type="radio" name="question_type" id="free_answer" value="3"';
if($question_type==3) {echo ' checked="checked"';}
echo ' title="'.$l['h_enable_free_answer'].'" />';
?>
<label for="free_answer"><?php echo $l['w_free_answer']; ?></label>
<br />
<?php
echo '<input type="radio" name="question_type" id="ordering_answer" value="4"';
if($question_type==4) {echo ' checked="checked"';}
echo ' title="'.$l['h_enable_ordering_answer'].'" />';
?>
<label for="free_answer"><?php echo $l['w_ordering_answer']; ?></label>
</fieldset>
</div>
</div>

<div class="row">
<span class="label">
<label for="question_difficulty"><?php echo $l['w_question_difficulty']; ?></label>
</span>
<span class="formw">
<select name="question_difficulty" id="question_difficulty" size="0" title="<?php echo $l['h_question_difficulty']; ?>">
<?php
for ($i=0; $i<=K_QUESTION_DIFFICULTY_LEVELS; $i++) {
	echo '<option value="'.$i.'"';
	if($i == $question_difficulty) {
		echo ' selected="selected"';
	}
	echo '>'.$i.'</option>'.K_NEWLINE;
}
?>
</select>
</span>
</div>

<div class="row">
<span class="label">
<label for="question_position"><?php echo $l['w_position']; ?></label>
</span>
<span class="formw">
<select name="question_position" id="question_position" size="0" title="<?php echo $l['h_position']; ?>">
<?php
if (isset($question_id) AND ($question_id > 0)) {
$max_position = (1 + F_count_rows(K_TABLE_QUESTIONS, "WHERE question_subject_id=".$question_subject_id." AND question_position>0 AND question_id<>".$question_id.""));
} else {
$max_position = 0;
}
echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
for($pos=1; $pos <= $max_position; $pos++) {
	echo '<option value="'.$pos.'"';
	if($pos == $question_position) {
		echo ' selected="selected"';
	}
	echo '>'.$pos.'</option>'.K_NEWLINE;
}
echo '<option value="'.($max_position + 1).'" style="color:#ff0000">'.($max_position + 1).'</option>'.K_NEWLINE;
?>
</select>
<input type="hidden" name="max_position" id="max_position" value="<?php echo $max_position; ?>" />
</span>
</div>

<div class="row">
<span class="label">
<label for="question_timer"><?php echo $l['w_timer']; ?></label>
</span>
<span class="formw">
<input type="text" name="question_timer" id="question_timer" value="<?php echo $question_timer; ?>" size="7" maxlength="20" title="<?php echo $l['h_question_timer']; ?>" />
</span>
</div>

<div class="row">
<span class="label">
<label for="question_fullscreen"><?php echo $l['w_fullscreen']; ?></label>
</span>
<span class="formw">
<?php
echo '<input type="checkbox" name="question_fullscreen" id="question_fullscreen" value="1"';
if($question_fullscreen) {echo ' checked="checked"';}
echo ' title="'.$l['h_question_fullscreen'].'" />';
?>
</span>
</div>

<div class="row">
<span class="label">
<label for="question_inline_answers"><?php echo $l['w_inline_answers']; ?></label>
</span>
<span class="formw">
<?php
echo '<input type="checkbox" name="question_inline_answers" id="question_inline_answers" value="1"';
if($question_inline_answers) {echo ' checked="checked"';}
echo ' title="'.$l['h_question_inline_answers'].'" />';
?>
</span>
</div>

<div class="row">
<span class="label">
<label for="question_auto_next"><?php echo $l['w_auto_next']; ?></label>
</span>
<span class="formw">
<?php
echo '<input type="checkbox" name="question_auto_next" id="question_auto_next" value="1"';
if($question_auto_next) {echo ' checked="checked"';}
echo ' title="'.$l['h_question_auto_next'].'" />';
?>
</span>
</div>

<div class="row">
<span class="label">
<label for="question_enabled"><?php echo $l['w_enabled']; ?></label>
</span>
<span class="formw">
<?php
echo '<input type="checkbox" name="question_enabled" id="question_enabled" value="1"';
if($question_enabled) {echo ' checked="checked"';}
echo ' title="'.$l['h_enabled'].'" />';
?>
</span>
</div>

<div class="row">
<?php
// show buttons by case
if (isset($question_id) AND ($question_id > 0)) {
	F_submit_button('update', $l['w_update'], $l['h_update']);
	F_submit_button('delete', $l['w_delete'], $l['h_delete']);
}
F_submit_button('add', $l['w_add'], $l['h_add']);
F_submit_button('clear', $l['w_clear'], $l['h_clear']);
?>
</div>

<div class="row">
<span class="left">
&nbsp;
<?php
if (isset($question_subject_id) AND ($question_subject_id > 0)) {
	echo '<a href="tce_edit_subject.php?subject_module_id='.$subject_module_id.'&amp;subject_id='.$question_subject_id.'" title="'.$l['t_subjects_editor'].'" class="xmlbutton">&lt; '.$l['t_subjects_editor'].'</a>';
}
?>
</span>
<span class="right">
<?php
if (isset($question_id) AND ($question_id > 0)) {
	echo '<a href="tce_edit_answer.php?subject_module_id='.$subject_module_id.'&amp;question_subject_id='.$question_subject_id.'&amp;answer_question_id='.$question_id.'" title="'.$l['t_answers_editor'].'" class="xmlbutton">'.$l['t_answers_editor'].' &gt;</a>';
}
?>
&nbsp;
</span>
&nbsp;
<!-- comma separated list of required fields -->
<input type="hidden" name="ff_required" id="ff_required" value="question_description" />
<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="<?php echo htmlspecialchars($l['w_description'], ENT_COMPAT, $l['a_meta_charset']); ?>" />

</div>

<div class="row"><hr /></div>

<div class="rowl" title="<?php echo $l['h_preview']; ?>">
<?php echo $l['w_preview']; ?>
<div class="preview">
<?php
echo F_decode_tcecode($question_description);
?>
&nbsp;
</div>
</div>

</form>
</div>
<?php

echo '<div class="pagehelp">'.$l['hp_edit_question'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
