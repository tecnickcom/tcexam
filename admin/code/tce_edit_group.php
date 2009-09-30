<?php
//============================================================+
// File name   : tce_edit_group.php
// Begin       : 2006-03-11
// Last Update : 2009-09-30
// 
// Description : Edit users' groups.
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
 * Display form to edit users' groups.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2006-03-11
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_USERS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_group_editor'];
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');

$user_id = intval($_SESSION['session_user_id']);
$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);

if (isset($group_id)) {
	$group_id = intval($group_id);
}
if (isset($group_name)) {
	$group_name = utrim($group_name);
}

switch($menu_mode) { // process submited data

	case 'delete':{
		F_stripslashes_formfields(); // ask confirmation
		F_print_error('WARNING', $l['m_delete_confirm']);
		?>
		<div class="confirmbox">
		<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_delete">
		<div>
		
		<input type="hidden" name="group_id" id="group_id" value="<?php echo $group_id; ?>" />
		<input type="hidden" name="group_name" id="group_name" value="<?php echo stripslashes($group_name); ?>" />
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
		F_stripslashes_formfields(); // Delete specified user
		if($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
			$sql = 'DELETE FROM '.K_TABLE_GROUPS.' WHERE group_id='.$group_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$group_id=FALSE;
				F_print_error('MESSAGE', '['.stripslashes($group_name).'] '.$l['m_group_deleted']);
			}
		}
		break;
	}

	case 'update':{ // Update user
		if($formstatus = F_check_form_fields()) {
			// check if name is unique
			if(!F_check_unique(K_TABLE_GROUPS, 'group_name=\''.F_escape_sql($group_name).'\'', 'group_id', $group_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'UPDATE '.K_TABLE_GROUPS.' SET 
				group_name=\''.F_escape_sql($group_name).'\'
				WHERE group_id='.$group_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $group_name.': '.$l['m_group_updated']);
			}
		}
		break;
	}

	case 'add':{ // Add user
		if($formstatus = F_check_form_fields()) { // check submittef form fields
			// check if name is unique
			if(!F_check_unique(K_TABLE_GROUPS, 'group_name=\''.F_escape_sql($group_name).'\'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_GROUPS.' (
				group_name
				) VALUES (
				\''.F_escape_sql($group_name).'\')';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$group_id = F_db_insert_id($db, K_TABLE_GROUPS, 'group_id');
			}
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$group_name = '';
		break;
	}

	default :{ 
		break;
	}

} //end of switch

// --- Initialize variables
if($formstatus) {
	if ($menu_mode != 'clear') {
		if(!isset($group_id) OR empty($group_id)) {
			$sql = 'SELECT * FROM '.K_TABLE_GROUPS.' ORDER BY group_name LIMIT 1';
		} else {
			$sql = 'SELECT * FROM '.K_TABLE_GROUPS.' WHERE group_id='.$group_id.' LIMIT 1';
		}
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				$group_id = $m['group_id'];
				$group_name = $m['group_name'];
			} else {
				$group_name = '';
			}
		} else {
			F_display_db_error();
		}
	}
}
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_groupeditor">

<div class="row">
<span class="label">
<label for="group_id"><?php echo $l['w_group']; ?></label>
</span>
<span class="formw">
<select name="group_id" id="group_id" size="0" onchange="document.getElementById('form_groupeditor').submit()">
<?php
$sql = 'SELECT *
	FROM '.K_TABLE_GROUPS.' 
	ORDER BY group_name';
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['group_id'].'"';
		if($m['group_id'] == $group_id) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($m['group_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
		$countitem++;
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
<label for="group_name"><?php echo $l['w_name']; ?></label>
</span>
<span class="formw">
<input type="text" name="group_name" id="group_name" value="<?php echo htmlspecialchars($group_name, ENT_COMPAT, $l['a_meta_charset']); ?>" size="20" maxlength="255" title="<?php echo $l['h_group_name']; ?>" />
</span>
</div>

<div class="row">
<?php
// show buttons by case
if ($group_id) {
	F_submit_button('update', $l['w_update'], $l['h_update']);
	F_submit_button('delete', $l['w_delete'], $l['h_delete']);
}
F_submit_button('add', $l['w_add'], $l['h_add']);
F_submit_button('clear', $l['w_clear'], $l['h_clear']); 
?>

<!-- comma separated list of required fields -->
<input type="hidden" name="ff_required" id="ff_required" value="group_name" />
<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="<?php echo htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']); ?>" />

</div>

</form>
</div>

<?php

echo '<div class="pagehelp">'.$l['hp_edit_group'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
