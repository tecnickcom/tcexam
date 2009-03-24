<?php
//============================================================+
// File name   : tce_edit_answer.php
// Begin       : 2004-04-27
// Last Update : 2009-03-24
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
//    Copyright (C) 2004-2009  Nicola Asuni - Tecnick.com S.r.l.
//    
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//    
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//     
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Display form to edit exam answers.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
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
	$sql = 'SELECT question_subject_id,answer_question_id
		FROM '.K_TABLE_SUBJECTS.', '.K_TABLE_QUESTIONS.', '.K_TABLE_ANSWERS.'
		WHERE subject_id=question_subject_id
			AND question_id=answer_question_id
			AND answer_id='.$answer_id.' 
		LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			if (!F_isAuthorizedUser(K_TABLE_SUBJECTS, 'subject_id', $m['question_subject_id'], 'subject_user_id')) {
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
			if(!F_check_unique(K_TABLE_ANSWERS, 'answer_description=\''.F_escape_sql($answer_description).'\' AND answer_question_id='.$answer_question_id.' AND answer_position='.$answer_position.'', "answer_id", $answer_id)) {
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
			if(!F_check_unique(K_TABLE_ANSWERS, 'answer_description=\''.F_escape_sql($answer_description).'\' AND answer_question_id='.$answer_question_id.' AND answer_position='.$answer_position.'')) {
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
	$sql = F_select_subjects_sql().' LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$subject_module_id = $m['subject_module_id'];
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
		ORDER BY question_description 
		LIMIT 1';
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
				ORDER BY answer_enabled DESC, answer_position, answer_isright DESC, answer_description 
				LIMIT 1';
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
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_answereditor">

<div class="row">
<span class="label">
<label for="subject_module_id"><?php echo $l['w_module']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="changemodule" id="changemodule" value="" />
<select name="subject_module_id" id="subject_module_id" size="0" onchange="document.getElementById('form_answereditor').changemodule.value=1; document.getElementById('form_answereditor').submit();" title="<?php echo $l['w_module']; ?>">
<?php
$sql = 'SELECT * FROM '.K_TABLE_MODULES.' ORDER BY module_name';
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
<input type="hidden" name="changesubject" id="changesubject" value="" />
<select name="question_subject_id" id="question_subject_id" size="0" onchange="document.getElementById('form_answereditor').changesubject.value=1; document.getElementById('form_answereditor').submit();" title="<?php echo $l['h_subject']; ?>">
<?php
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
?>
</select>
</span>
</div>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectsubject" id="selectsubject" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row">
<span class="label">
<label for="answer_question_id"><?php echo $l['w_question']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="changecategory" id="changecategory" value="" />
<select name="answer_question_id" id="answer_question_id" size="0" onchange="document.getElementById('form_answereditor').changecategory.value=1; document.getElementById('form_answereditor').submit()" title="<?php echo $l['h_question']; ?>">
<?php
$sql = "SELECT * 
	FROM ".K_TABLE_QUESTIONS." 
	WHERE question_subject_id=".$question_subject_id."
	ORDER BY question_subject_id, question_description";
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
<label for="answer_id"><?php echo $l['w_answer']; ?></label>
</span>
<span class="formw">
<select name="answer_id" id="answer_id" size="0" onchange="document.getElementById('form_answereditor').submit()" title="<?php echo $l['h_answer']; ?>">
<?php
$sql = 'SELECT * 
	FROM '.K_TABLE_ANSWERS.' 
	WHERE answer_question_id='.$answer_question_id.' 
	ORDER BY answer_position, answer_enabled DESC, answer_isright DESC, answer_description';
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
<label for="answer_description"><?php echo $l['w_answer']; ?></label>
<br />
<?php 
echo '<a href="#" title="'.$l['h_preview'].'" class="xmlbutton" onclick="previewWindow=window.open(\'tce_preview_tcecode.php?tcexamcode=\'+encodeURIComponent(document.getElementById(\'form_answereditor\').answer_description.value),\'previewWindow\',\'dependent,height=500,width=500,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'.$l['w_preview'].'</a>'.K_NEWLINE;
?>
</span>
<span class="formw" style="border:1px solid #808080;">
<textarea cols="50" rows="10" name="answer_description" id="answer_description" onselect="FJ_update_selection(document.getElementById('form_answereditor').answer_description)" title="<?php echo $l['h_answer']; ?>"><?php echo htmlspecialchars($answer_description, ENT_NOQUOTES, $l['a_meta_charset']); ?></textarea>
<br />
<?php echo tcecodeEditorTagButtons('form_answereditor', 'answer_description', 0); ?>
</span>
</div>

<?php
if (K_ENABLE_ANSWER_EXPLANATION) {
	echo '<div class="row">'.K_NEWLINE;
	echo '<span class="label">'.K_NEWLINE;
	echo '<label for="answer_explanation">'.$l['w_explanation'].'</label>'.K_NEWLINE;
	echo '<br />'.K_NEWLINE;
	echo '<a href="#" title="'.$l['h_preview'].'" class="xmlbutton" onclick="previewWindow=window.open(\'tce_preview_tcecode.php?tcexamcode=\'+encodeURIComponent(document.getElementById(\'form_answereditor\').answer_explanation.value),\'previewWindow\',\'dependent,height=500,width=500,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'.$l['w_preview'].'</a>'.K_NEWLINE;
	echo '</span>'.K_NEWLINE;
	echo '<span class="formw" style="border:1px solid #808080;">'.K_NEWLINE;
	echo '<textarea cols="50" rows="10" name="answer_explanation" id="answer_explanation" onselect="FJ_update_selection(document.getElementById(\'form_answereditor\').answer_explanation)" title="'.$l['h_explanation'].'">'.htmlspecialchars($answer_explanation, ENT_NOQUOTES, $l['a_meta_charset']).'</textarea>'.K_NEWLINE;
	echo '<br />'.K_NEWLINE;
	echo tcecodeEditorTagButtons('form_answereditor', 'answer_explanation', 1);
	echo '</span>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
}
?>

<div class="row">
<span class="label">
<label for="answer_isright"><?php echo $l['w_right']; ?></label>
</span>
<span class="formw">
<?php
echo '<input type="checkbox" name="answer_isright" id="answer_isright" value="1"';
if($answer_isright) {echo ' checked="checked"';}
echo ' title="'.$l['h_answer_isright'].'" />';
?>
</span>
</div>

<div class="row">
<span class="label">
<label for="answer_enabled"><?php echo $l['w_enabled']; ?></label>
</span>
<span class="formw">
<?php
echo '<input type="checkbox" name="answer_enabled" id="answer_enabled" value="1"';
if($answer_enabled) {echo ' checked="checked"';}
echo ' title="'.$l['h_enabled'].'" />';
?>
</span>
</div>

<div class="row">
<span class="label">
<label for="answer_position"><?php echo $l['w_position']; ?></label>
</span>
<span class="formw">
<select name="answer_position" id="answer_position" size="0" title="<?php echo $l['h_position']; ?>">
<?php
if (isset($answer_id) AND ($answer_id > 0)) {
$max_position = (1 + F_count_rows(K_TABLE_ANSWERS, 'WHERE answer_question_id='.$answer_question_id.' AND answer_position>0 AND answer_id<>'.$answer_id.''));
} else {
$max_position = 0;
}
echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
for($pos=1; $pos <= $max_position; $pos++) {
	echo '<option value="'.$pos.'"';
	if($pos == $answer_position) {
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
<label for="answer_keyboard_key"><?php echo $l['w_keyboard_key']; ?></label>
</span>
<span class="formw">
<select name="answer_keyboard_key" id="answer_keyboard_key" size="0" title="<?php echo $l['h_answer_keyboard_key']; ?>">
<?php
echo '<option value="">&nbsp;</option>'.K_NEWLINE;
for($ascii = 32; $ascii <= 126; $ascii++) {
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
	echo "</option>\n";
}
?>

</select>
</span>
</div>

<div class="row">
<?php
// show buttons by case
if (isset($answer_id) AND ($answer_id > 0)) {
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
if (isset($answer_question_id) AND ($answer_question_id > 0)) {
	echo '<a href="tce_edit_question.php?subject_module_id='.$subject_module_id.'&amp;question_subject_id='.$question_subject_id.'&amp;question_id='.$answer_question_id.'" title="'.$l['t_questions_editor'].'" class="xmlbutton">&lt; '.$l['t_questions_editor'].'</a>';
}
?>
</span>
&nbsp;
<!-- comma separated list of required fields -->
<input type="hidden" name="ff_required" id="ff_required" value="answer_description" />
<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="<?php echo htmlspecialchars($l['w_description'], ENT_COMPAT, $l['a_meta_charset']); ?>" />

</div>

<div class="row"><hr /></div>

<div class="rowl" title="<?php echo $l['h_preview']; ?>">
<?php echo $l['w_preview']; ?>
<div class="preview">
<?php
echo F_decode_tcecode($answer_description);
?>
&nbsp;
</div>
</div>

</form>
</div>

<?php

echo '<div class="pagehelp">'.$l['hp_edit_answer'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
