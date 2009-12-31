<?php
//============================================================+
// File name   : tce_edit_module.php
// Begin       : 2008-11-28
// Last Update : 2009-09-30
// 
// Description : Display form to edit modules.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Display form to edit modules.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2008-11-28
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_MODULES;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_modules_editor'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../code/tce_functions_auth_sql.php');

// set default values
if(!isset($module_enabled) OR (empty($module_enabled))) {
	$module_enabled = 0;
} else {
	$module_enabled = intval($module_enabled);
}
if (isset($_REQUEST['module_id']) AND ($_REQUEST['module_id'] > 0)) {
	$module_id = intval($_REQUEST['module_id']);
}
if (isset($module_name)) {
	$module_name = utrim($module_name);
}

switch($menu_mode) {

	case 'delete':{
		F_stripslashes_formfields();
		// check if this record is used (test_log)
		if(!F_check_unique(K_TABLE_SUBJECTS.','.K_TABLE_SUBJECT_SET, 'subjset_subject_id=subject_id AND subject_module_id='.$module_id.'')) {
			//this record will be only disabled and not deleted because it's used
			$sql = 'UPDATE '.K_TABLE_MODULES.' SET 
				module_enabled=\'0\'
				WHERE module_id='.$module_id.'';
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
			<input type="hidden" name="module_id" id="module_id" value="<?php echo $module_id; ?>" />
			<input type="hidden" name="module_name" id="module_name" value="<?php echo htmlspecialchars($module_name, ENT_COMPAT, $l['a_meta_charset']); ?>" />
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
			$sql = 'DELETE FROM '.K_TABLE_MODULES.' WHERE module_id='.$module_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {				
				$module_id=FALSE;
				F_print_error('MESSAGE', $module_name.': '.$l['m_deleted']);
			}
		}
		break;
	}

	case 'update':{ // Update
		if($formstatus = F_check_form_fields()) {
			// check referential integrity (NOTE: mysql do not support "ON UPDATE" constraint)
			if(!F_check_unique(K_TABLE_SUBJECTS.','.K_TABLE_SUBJECT_SET, 'subjset_subject_id=subject_id AND subject_module_id='.$module_id.'')) {
				F_print_error('WARNING', $l['m_update_restrict']);
				
				// enable or disable record
				$sql = 'UPDATE '.K_TABLE_MODULES.' SET 
					module_enabled=\''.$module_enabled.'\'
					WHERE module_id='.$module_id.'';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				} else {
					$strmsg = $l['w_record_status'].': ';
					if ($module_enabled) {
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
			// check if name is unique
			if(!F_check_unique(K_TABLE_MODULES, 'module_name=\''.F_escape_sql($module_name).'\'', 'module_id', $module_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE;
				F_stripslashes_formfields();
				break;
			}
			
			$sql = 'UPDATE '.K_TABLE_MODULES.' SET 
				module_name=\''.F_escape_sql($module_name).'\',
				module_enabled=\''.$module_enabled.'\'
				WHERE module_id='.$module_id.'';
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
			if(!F_check_unique(K_TABLE_MODULES, 'module_name=\''.F_escape_sql($module_name).'\'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_MODULES.' (
				module_name,
				module_enabled
				) VALUES (
				\''.F_escape_sql($module_name).'\',
				\''.$module_enabled.'\'
				)';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$module_id = F_db_insert_id($db, K_TABLE_MODULES, 'module_id');
			}
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$module_name = '';
		$module_enabled = true;
		break;
	}

	default :{ 
		break;
	}

} //end of switch

// --- Initialize variables
if($formstatus) {
	if ($menu_mode != 'clear') {
		if(!isset($module_id) OR empty($module_id)) {
			$sql = 'SELECT * 
				FROM '.K_TABLE_MODULES.'
				ORDER BY module_name
				LIMIT 1';
		} else {
			$sql = 'SELECT * 
				FROM '.K_TABLE_MODULES.'
				WHERE module_id='.$module_id.'
				LIMIT 1';
		}
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				$module_id = $m['module_id'];
				$module_name = $m['module_name'];
				$module_enabled = F_getBoolean($m['module_enabled']);
			} else {
				$module_name = '';
				$module_enabled = true;
			}
		} else {
			F_display_db_error();
		}
	}
}
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_moduleeditor">

<div class="row">
<span class="label">
<label for="module_id"><?php echo $l['w_module']; ?></label>
</span>
<span class="formw">
<select name="module_id" id="module_id" size="0" onchange="document.getElementById('form_moduleeditor').submit()" title="<?php echo $l['h_module_name']; ?>">
<?php
$sql = 'SELECT * FROM '.K_TABLE_MODULES.' ORDER BY module_name';
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['module_id'].'"';
		if($m['module_id'] == $module_id) {
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
<input type="submit" name="selectrecord" id="selectrecord" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row"><hr /></div>

<div class="row">
<span class="label">
<label for="module_name"><?php echo $l['w_name']; ?></label>
</span>
<span class="formw">
<input type="text" name="module_name" id="module_name" value="<?php echo htmlspecialchars($module_name, ENT_COMPAT, $l['a_meta_charset']); ?>" size="30" maxlength="255" title="<?php echo $l['h_module_name']; ?>" />
</span>
</div>

<div class="row">
<span class="label">
<label for="module_enabled"><?php echo $l['w_enabled']; ?></label>
</span>
<span class="formw">
<?php
echo '<input type="checkbox" name="module_enabled" id="module_enabled" value="1"';
if($module_enabled) {echo ' checked="checked"';}
echo ' title="'.$l['h_enabled'].'" />';
?>
</span>
</div>

<div class="row">
<?php
// show buttons by case
if (isset($module_id) AND ($module_id > 0)) {
	F_submit_button('update', $l['w_update'], $l['h_update']);
	F_submit_button('delete', $l['w_delete'], $l['h_delete']);
}
F_submit_button('add', $l['w_add'], $l['h_add']);
F_submit_button('clear', $l['w_clear'], $l['h_clear']);
?>
</div>

<div class="row">
<span class="right">
<?php
if (isset($module_id) AND ($module_id > 0)) {
	echo '<a href="tce_edit_subject.php?subject_module_id='.$module_id.'" title="'.$l['t_subjects_editor'].'" class="xmlbutton">'.$l['t_subjects_editor'].' &gt;</a>';
}
?>
&nbsp;
</span>
&nbsp;
<!-- comma separated list of required fields -->
<input type="hidden" name="ff_required" id="ff_required" value="module_name" />
<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="<?php echo htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']); ?>" />

</div>
</form>
</div>

<?php

echo '<div class="pagehelp">'.$l['hp_edit_module'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
