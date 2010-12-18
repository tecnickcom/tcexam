<?php
//============================================================+
// File name   : tce_edit_subject.php
// Begin       : 2004-04-26
// Last Update : 2010-06-16
//
// Description : Display form to edit exam subject_id (topics).
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
 * Display form to edit exam subject_id (topics).
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-04-27
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_SUBJECTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_subjects_editor'];
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
if(!isset($subject_enabled) OR (empty($subject_enabled))) {
	$subject_enabled = 0;
} else {
	$subject_enabled = intval($subject_enabled);
}
if (isset($subject_id)) {
	$subject_id = intval($subject_id);
}
if (isset($subject_module_id)) {
	$subject_module_id = intval($subject_module_id);
}
if (isset($selectcategory)) {
	$changecategory = 1;
}
if (isset($subject_name)) {
	$subject_name = utrim($subject_name);
}
if (isset($subject_description)) {
	$subject_description = utrim($subject_description);
}
if (isset($_REQUEST['subject_id']) AND ($_REQUEST['subject_id'] > 0)) {
	$subject_id = intval($_REQUEST['subject_id']);
	// check user's authorization for subject
	if (!F_isAuthorizedUser(K_TABLE_SUBJECTS, 'subject_id', $subject_id, 'subject_user_id')) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		exit;
	}
	if (!isset($changecategory) OR ($changecategory == 0)) {
		$sql = 'SELECT subject_module_id FROM '.K_TABLE_SUBJECTS.' WHERE subject_id='.$subject_id.' LIMIT 1';
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				$subject_module_id = $m['subject_module_id'];
				// check user's authorization for parent module
				if (!F_isAuthorizedUser(K_TABLE_MODULES, 'module_id', $subject_module_id, 'module_user_id')) {
					F_print_error('ERROR', $l['m_authorization_denied']);
					exit;
				}
			}
		} else {
			F_display_db_error();
		}
	}
}

switch($menu_mode) {

	case 'delete':{
		F_stripslashes_formfields();
		// check if this record is used (test_log)
		if(!F_check_unique(K_TABLE_SUBJECT_SET, 'subjset_subject_id='.$subject_id.'')) {
			//this record will be only disabled and not deleted because it's used
			$sql = 'UPDATE '.K_TABLE_SUBJECTS.' SET
				subject_enabled=\'0\'
				WHERE subject_id='.$subject_id.'';
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
			<input type="hidden" name="subject_id" id="subject_id" value="<?php echo $subject_id; ?>" />
			<input type="hidden" name="subject_module_id" id="subject_module_id" value="<?php echo $subject_module_id; ?>" />
			<input type="hidden" name="subject_name" id="subject_name" value="<?php echo htmlspecialchars($subject_name, ENT_COMPAT, $l['a_meta_charset']); ?>" />
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
		F_stripslashes_formfields();
		if($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
			$sql = 'DELETE FROM '.K_TABLE_SUBJECTS.' WHERE subject_id='.$subject_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$subject_id=FALSE;
				F_print_error('MESSAGE', $subject_name.': '.$l['m_deleted']);
			}
		}
		break;
	}

	case 'update':{ // Update
		if($formstatus = F_check_form_fields()) {
			// check referential integrity (NOTE: mysql do not support "ON UPDATE" constraint)
			if(!F_check_unique(K_TABLE_SUBJECT_SET, "subjset_subject_id=".$subject_id."")) {
				F_print_error('WARNING', $l['m_update_restrict']);
				// enable or disable record
				$sql = 'UPDATE '.K_TABLE_SUBJECTS.' SET
					subject_enabled=\''.$subject_enabled.'\'
					WHERE subject_id='.$subject_id.'';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				} else {
					$strmsg = $l['w_record_status'].': ';
					if ($subject_enabled) {
						$strmsg .= $l['w_enabled'];
					} else {
						$strmsg .= $l['w_disabled'];
					}
					F_print_error('MESSAGE', $strmsg);
				}
				$formstatus = FALSE;
				F_stripslashes_formfields();
				break;
			}
			// check if name is unique for selected module
			if(!F_check_unique(K_TABLE_SUBJECTS, 'subject_name=\''.F_escape_sql($subject_name).'\' AND subject_module_id='.$subject_module_id.'', 'subject_id', $subject_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE;
				F_stripslashes_formfields();
				break;
			}
			$sql = 'UPDATE '.K_TABLE_SUBJECTS.' SET
				subject_name=\''.F_escape_sql($subject_name).'\',
				subject_description='.F_empty_to_null(F_escape_sql($subject_description)).',
				subject_enabled=\''.$subject_enabled.'\',
				subject_module_id='.$subject_module_id.'
				WHERE subject_id='.$subject_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $l['m_updated']);
			}
		}
		break;
	}

	case 'add':{ // Add
		if($formstatus = F_check_form_fields()) {
			// check if name is unique
			if(!F_check_unique(K_TABLE_SUBJECTS, 'subject_name=\''.F_escape_sql($subject_name).'\' AND subject_module_id='.$subject_module_id.'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_SUBJECTS.' (
				subject_name,
				subject_description,
				subject_enabled,
				subject_user_id,
				subject_module_id
				) VALUES (
				\''.F_escape_sql($subject_name).'\',
				'.F_empty_to_null(F_escape_sql($subject_description)).',
				\''.$subject_enabled.'\',
				\''.$_SESSION['session_user_id'].'\',
				'.$subject_module_id.'
				)';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$subject_id = F_db_insert_id($db, K_TABLE_SUBJECTS, 'subject_id');
			}
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$subject_name = '';
		$subject_description = '';
		$subject_enabled = true;
		break;
	}

	default :{
		break;
	}

} //end of switch

// select default module (if not specified)
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

// --- Initialize variables
if($formstatus) {
	if ($menu_mode != 'clear') {
		if ((isset($changecategory) AND ($changecategory > 0))
			OR (!isset($subject_id)) OR empty($subject_id)) {
			$sql = F_select_subjects_sql('subject_module_id='.$subject_module_id).' LIMIT 1';
		} else {
			$sql = F_select_subjects_sql('subject_id='.$subject_id.' AND subject_module_id='.$subject_module_id).' LIMIT 1';
		}
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				$subject_id = $m['subject_id'];
				$subject_name = $m['subject_name'];
				$subject_description = $m['subject_description'];
				$subject_enabled = F_getBoolean($m['subject_enabled']);
				$subject_module_id = $m['subject_module_id'];
			} else {
				$subject_name = '';
				$subject_description = '';
				$subject_enabled = true;
			}
		} else {
			F_display_db_error();
		}
	}
}
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_subjecteditor">

<div class="row">
<span class="label">
<label for="subject_module_id"><?php echo $l['w_module']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="changecategory" id="changecategory" value="" />
<select name="subject_module_id" id="subject_module_id" size="0" onchange="document.getElementById('form_subjecteditor').changecategory.value=1; document.getElementById('form_subjecteditor').submit();" title="<?php echo $l['w_module']; ?>">
<?php
$sql = F_select_modules_sql();
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['module_id'].'"';
		if($m['module_id'] == $subject_module_id) {
			echo ' selected="selected"';
		}
		echo '>'.$countitem.". ";
		if (F_getBoolean($m['module_enabled'])) {
			echo "+";
		} else {
			echo "-";
		}
		echo " ".htmlspecialchars($m['module_name'], ENT_NOQUOTES, $l['a_meta_charset']).'&nbsp;</option>'.K_NEWLINE;
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
<label for="subject_id"><?php echo $l['w_subject']; ?></label>
</span>
<span class="formw">
<select name="subject_id" id="subject_id" size="0" onchange="document.getElementById('form_subjecteditor').submit()" title="<?php echo $l['h_subject']; ?>">
<?php
$sql = F_select_subjects_sql('subject_module_id='.$subject_module_id);
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['subject_id'].'"';
		if($m['subject_id'] == $subject_id) {
			echo ' selected="selected"';
		}
		echo '>'.$countitem.'. ';
		if (F_getBoolean($m['subject_enabled'])) {
			echo '+';
		} else {
			echo '-';
		}
		echo ' '.htmlspecialchars($m['subject_name'], ENT_NOQUOTES, $l['a_meta_charset']).'&nbsp;</option>'.K_NEWLINE;
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
<input type="submit" name="selectrecord" id="selectrecord" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row"><hr /></div>

<div class="row">
<span class="label">
<label for="subject_name"><?php echo $l['w_name']; ?></label>
</span>
<span class="formw">
<input type="text" name="subject_name" id="subject_name" value="<?php echo htmlspecialchars($subject_name, ENT_COMPAT, $l['a_meta_charset']); ?>" size="30" maxlength="255" title="<?php echo $l['h_subject_name']; ?>" />
</span>
</div>

<div class="row">
<span class="label">
<label for="subject_description"><?php echo $l['w_description']; ?></label>
<br />
<?php
echo '<a href="#" title="'.$l['h_preview'].'" class="xmlbutton" onclick="previewWindow=window.open(\'tce_preview_tcecode.php?tcexamcode=\'+encodeURIComponent(document.getElementById(\'form_subjecteditor\').subject_description.value),\'previewWindow\',\'dependent,height=500,width=500,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'.$l['w_preview'].'</a>';
?>
</span>
<span class="formw" style="border:1px solid #808080;">
<textarea cols="50" rows="5" name="subject_description" id="subject_description" onselect="FJ_update_selection(document.getElementById('form_subjecteditor').subject_description)" title="<?php echo $l['h_subject_description']; ?>"><?php echo htmlspecialchars($subject_description, ENT_NOQUOTES, $l['a_meta_charset']); ?></textarea>
<br />
<?php echo tcecodeEditorTagButtons('form_subjecteditor', 'subject_description'); ?>
</span>
</div>

<div class="row">
<span class="label">
<label for="subject_enabled"><?php echo $l['w_enabled']; ?></label>
</span>
<span class="formw">
<?php
echo '<input type="checkbox" name="subject_enabled" id="subject_enabled" value="1"';
if($subject_enabled) {echo ' checked="checked"';}
echo ' title="'.$l['h_enabled'].'" />';
?>
</span>
</div>

<div class="row">
<?php
// show buttons by case
if (isset($subject_id) AND ($subject_id > 0)) {
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
if (isset($subject_module_id) AND ($subject_module_id > 0)) {
	echo '<a href="tce_edit_module.php?module_id='.$subject_module_id.'" title="'.$l['t_modules_editor'].'" class="xmlbutton">&lt; '.$l['t_modules_editor'].'</a>';
}
?>
</span>
<span class="right">
<?php
if (isset($subject_id) AND ($subject_id > 0)) {
	echo '<a href="tce_edit_question.php?subject_module_id='.$subject_module_id.'&amp;question_subject_id='.$subject_id.'" title="'.$l['t_questions_editor'].'" class="xmlbutton">'.$l['t_questions_editor'].' &gt;</a>';
}
?>
&nbsp;
</span>
&nbsp;
<!-- comma separated list of required fields -->
<input type="hidden" name="ff_required" id="ff_required" value="subject_name" />
<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="<?php echo htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']); ?>" />

</div>

<div class="row"><hr /></div>

<div class="rowl" title="<?php echo $l['h_preview']; ?>">
<?php echo $l['w_preview']; ?>
<div class="preview">
<?php
echo F_decode_tcecode($subject_description);
?>
&nbsp;
</div>
</div>

</form>
</div>

<?php
echo '<div class="pagehelp">'.$l['hp_edit_subject'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
