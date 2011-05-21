<?php
//============================================================+
// File name   : tce_edit_user.php
// Begin       : 2002-02-08
// Last Update : 2011-05-21
//
// Description : Edit user data.
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
 * Display form to edit users.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2002-02-08
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_USERS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_user_editor'];
$enable_calendar = true;
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_user_select.php');

if (isset($_REQUEST['user_id'])) {
	$user_id = intval($_REQUEST['user_id']);
	if (!F_isAuthorizedEditorForUser($user_id)) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		exit;
	}
}
if (isset($_REQUEST['group_id'])) {
	$group_id = intval($_REQUEST['group_id']);
	if (!F_isAuthorizedEditorForGroup($group_id)) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		exit;
	}
}
if (isset($_REQUEST['user_level'])) {
	$user_level = intval($_REQUEST['user_level']);
	if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
		if ($user_id == $_SESSION['session_user_id']) {
			// you cannot change your own level
			$user_level = $_SESSION['session_user_level'];
		} else {
			// you cannot create a user with a level equal or higher than yours
			$user_level = min(max(0, ($_SESSION['session_user_level'] - 1)), $user_level);
		}
	}
}

switch($menu_mode) { // process submitted data

	case 'delete':{
		F_stripslashes_formfields(); // ask confirmation
		if (($_SESSION['session_user_level'] < K_AUTH_DELETE_USERS) OR ($user_id == $_SESSION['session_user_id']) OR ($user_id == 1)) {
			F_print_error('ERROR', $l['m_authorization_denied']);
			break;
		}
		F_print_error('WARNING', $l['m_delete_confirm']);
		?>
		<div class="confirmbox">
		<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_delete">
		<div>
		<input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id; ?>" />
		<input type="hidden" name="user_name" id="user_name" value="<?php echo stripslashes($user_name); ?>" />
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
		if (($_SESSION['session_user_level'] < K_AUTH_DELETE_USERS) OR ($user_id == $_SESSION['session_user_id']) OR ($user_id == 1)) {
			F_print_error('ERROR', $l['m_authorization_denied']);
			break;
		}
		if ($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
			if ($user_id==1) { //can't delete anonymous user
				F_print_error('WARNING', $l['m_delete_anonymous']);
			} else {
				$sql = 'DELETE FROM '.K_TABLE_USERS.' WHERE user_id='.$user_id.'';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				} else {
					$user_id=FALSE;
					F_print_error('MESSAGE', '['.stripslashes($user_name).'] '.$l['m_user_deleted']);
				}
			}
		}
		break;
	}

	case 'update':{ // Update user
		if ($formstatus = F_check_form_fields()) {
			// check if name is unique
			if (!F_check_unique(K_TABLE_USERS, 'user_name=\''.F_escape_sql($user_name).'\'', 'user_id', $user_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			// check if registration number is unique
			if (isset($user_regnumber) AND (strlen($user_regnumber) > 0) AND (!F_check_unique(K_TABLE_USERS, 'user_regnumber=\''.F_escape_sql($user_regnumber).'\'', 'user_id', $user_id))) {
				F_print_error('WARNING', $l['m_duplicate_regnumber']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			// check if ssn is unique
			if (isset($user_ssn) AND (strlen($user_ssn) > 0) AND (!F_check_unique(K_TABLE_USERS, 'user_ssn=\''.F_escape_sql($user_ssn).'\'', 'user_id', $user_id))) {
				F_print_error('WARNING', $l['m_duplicate_ssn']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			// check password
			if (!empty($newpassword) OR !empty($newpassword_repeat)) {
				if ($newpassword == $newpassword_repeat) {
					$user_password = md5($newpassword);
				} else { //print message and exit
					F_print_error('WARNING', $l['m_different_passwords']);
					$formstatus = FALSE; F_stripslashes_formfields();
					break;
				}
			}
			$sql = 'UPDATE '.K_TABLE_USERS.' SET
				user_regdate=\''.F_escape_sql($user_regdate).'\',
				user_ip=\''.F_escape_sql($user_ip).'\',
				user_name=\''.F_escape_sql($user_name).'\',
				user_email='.F_empty_to_null($user_email).',
				user_password=\''.F_escape_sql($user_password).'\',
				user_regnumber='.F_empty_to_null($user_regnumber).',
				user_firstname='.F_empty_to_null($user_firstname).',
				user_lastname='.F_empty_to_null($user_lastname).',
				user_birthdate='.F_empty_to_null($user_birthdate).',
				user_birthplace='.F_empty_to_null($user_birthplace).',
				user_ssn='.F_empty_to_null($user_ssn).',
				user_level=\''.$user_level.'\'
				WHERE user_id='.$user_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $user_name.': '.$l['m_user_updated']);
			}
			// remove old groups
			$old_user_groups = F_get_user_groups($user_id);
			foreach ($old_user_groups as $group_id) {
				if (F_isAuthorizedEditorForGroup($group_id)) {
					// delete previous groups
					$sql = 'DELETE FROM '.K_TABLE_USERGROUP.'
						WHERE usrgrp_user_id='.$user_id.' AND usrgrp_group_id='.$group_id.'';
					if (!$r = F_db_query($sql, $db)) {
						F_display_db_error(false);
					}
				}
			}
			// update user's groups
			if (!empty($user_groups)) {
				foreach ($user_groups as $group_id) {
					if (F_isAuthorizedEditorForGroup($group_id)) {
						$sql = 'INSERT INTO '.K_TABLE_USERGROUP.' (
							usrgrp_user_id,
							usrgrp_group_id
							) VALUES (
							\''.$user_id.'\',
							\''.$group_id.'\'
							)';
						if (!$r = F_db_query($sql, $db)) {
							F_display_db_error(false);
						}
					}
				}
			}
		}
		break;
	}

	case 'add':{ // Add user
		if ($formstatus = F_check_form_fields()) { // check submittef form fields
			// check if name is unique
			if (!F_check_unique(K_TABLE_USERS, 'user_name=\''.$user_name.'\'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			// check if registration number is unique
			if (isset($user_regnumber) AND (strlen($user_regnumber) > 0) AND (!F_check_unique(K_TABLE_USERS, 'user_regnumber=\''.F_escape_sql($user_regnumber).'\''))) {
				F_print_error('WARNING', $l['m_duplicate_regnumber']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			// check if ssn is unique
			if (isset($user_ssn) AND (strlen($user_ssn) > 0) AND (!F_check_unique(K_TABLE_USERS, 'user_ssn=\''.F_escape_sql($user_ssn).'\''))) {
				F_print_error('WARNING', $l['m_duplicate_ssn']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			// check password
			if (!empty($newpassword) OR !empty($newpassword_repeat)) { // update password
				if ($newpassword == $newpassword_repeat) {
					$user_password = md5($newpassword);
				} else { //print message and exit
					F_print_error('WARNING', $l['m_different_passwords']);
					$formstatus = FALSE; F_stripslashes_formfields();
					break;
				}
			} else { //print message and exit
				F_print_error('WARNING', $l['m_empty_password']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}

			$user_ip = getNormalizedIP($_SERVER['REMOTE_ADDR']); // get the user's IP number
			$user_regdate = date(K_TIMESTAMP_FORMAT); // get the registration date and time

			$sql = 'INSERT INTO '.K_TABLE_USERS.' (
				user_regdate,
				user_ip,
				user_name,
				user_email,
				user_password,
				user_regnumber,
				user_firstname,
				user_lastname,
				user_birthdate,
				user_birthplace,
				user_ssn,
				user_level
				) VALUES (
				\''.F_escape_sql($user_regdate).'\',
				\''.F_escape_sql($user_ip).'\',
				\''.F_escape_sql($user_name).'\',
				'.F_empty_to_null($user_email).',
				\''.F_escape_sql($user_password).'\',
				'.F_empty_to_null($user_regnumber).',
				'.F_empty_to_null($user_firstname).',
				'.F_empty_to_null($user_lastname).',
				'.F_empty_to_null($user_birthdate).',
				'.F_empty_to_null($user_birthplace).',
				'.F_empty_to_null($user_ssn).',
				\''.$user_level.'\'
				)';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$user_id = F_db_insert_id($db, K_TABLE_USERS, 'user_id');
			}
			// add user's groups
			if (!empty($user_groups)) {
				foreach ($user_groups as $group_id) {
					if (F_isAuthorizedEditorForGroup($group_id)) {
						$sql = 'INSERT INTO '.K_TABLE_USERGROUP.' (
							usrgrp_user_id,
							usrgrp_group_id
							) VALUES (
							\''.$user_id.'\',
							\''.$group_id.'\'
							)';
						if (!$r = F_db_query($sql, $db)) {
							F_display_db_error(false);
						}
					}
				}
			}
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$user_regdate = '';
		$user_ip = '';
		$user_name = '';
		$user_email = '';
		$user_password = '';
		$user_regnumber = '';
		$user_firstname = '';
		$user_lastname = '';
		$user_birthdate = '';
		$user_birthplace = '';
		$user_ssn = '';
		$user_level = '';
		break;
	}

	default :{
		break;
	}

} //end of switch

// --- Initialize variables
if ($formstatus) {
	if ($menu_mode != 'clear') {
		if (!isset($user_id) OR empty($user_id)) {
			$sql = 'SELECT * FROM '.K_TABLE_USERS.' WHERE user_id='.$_SESSION['session_user_id'].' LIMIT 1';
		} else {
			$sql = 'SELECT * FROM '.K_TABLE_USERS.' WHERE user_id='.$user_id.' LIMIT 1';
		}
		if ($r = F_db_query($sql, $db)) {
			if ($m = F_db_fetch_array($r)) {
				$user_id = $m['user_id'];
				$user_regdate = $m['user_regdate'];
				$user_ip = $m['user_ip'];
				$user_name = $m['user_name'];
				$user_email = $m['user_email'];
				$user_password = $m['user_password'];
				$user_regnumber = $m['user_regnumber'];
				$user_firstname = $m['user_firstname'];
				$user_lastname = $m['user_lastname'];
				$user_birthdate = substr($m['user_birthdate'],0,10);
				$user_birthplace = $m['user_birthplace'];
				$user_ssn = $m['user_ssn'];
				$user_level = $m['user_level'];
			} else {
				$user_regdate = '';
				$user_ip = '';
				$user_name = '';
				$user_email = '';
				$user_password = '';
				$user_regnumber = '';
				$user_firstname = '';
				$user_lastname = '';
				$user_birthdate = '';
				$user_birthplace = '';
				$user_ssn = '';
				$user_level = '';
			}
		} else {
			F_display_db_error();
		}
	}
}

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_usereditor">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="user_id">'.$l['w_user'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="user_id" id="user_id" size="0" onchange="document.getElementById(\'form_usereditor\').submit()">'.K_NEWLINE;
$sql = 'SELECT user_id, user_lastname, user_firstname, user_name FROM '.K_TABLE_USERS.' WHERE (user_id>1)';
if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
	// filter for level
	$sql .= ' AND ((user_level<'.$_SESSION['session_user_level'].') OR (user_id='.$_SESSION['session_user_id'].'))';
	// filter for groups
	$sql .= ' AND user_id IN (SELECT tb.usrgrp_user_id
		FROM '.K_TABLE_USERGROUP.' AS ta, '.K_TABLE_USERGROUP.' AS tb
		WHERE ta.usrgrp_group_id=tb.usrgrp_group_id
			AND ta.usrgrp_user_id='.intval($_SESSION['session_user_id']).'
			AND tb.usrgrp_user_id=user_id)';
}
$sql .= ' ORDER BY user_lastname, user_firstname, user_name';
if ($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['user_id'].'"';
		if ($m['user_id'] == $user_id) {
			echo ' selected="selected"';
		}
		echo '>'.$countitem.'. '.htmlspecialchars($m['user_lastname'].' '.$m['user_firstname'].' - '.$m['user_name'].'', ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
		$countitem++;
	}
}
else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo getFormRowTextInput('user_name', $l['w_username'], $l['h_login_name'], '', $user_name, '', 255, false, false, false);
echo getFormRowTextInput('user_email', $l['w_email'], $l['h_usered_email'], '', $user_email, '^([a-zA-Z0-9_\.\-]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$', 255, false, false, false);
echo getFormRowTextInput('newpassword', $l['w_password'], $l['h_password'], ' ('.$l['d_password_lenght'].')', '', '^([a-zA-Z0-9]{8,32})$', 255, false, false, true);
echo getFormRowTextInput('newpassword_repeat', $l['w_password'], $l['h_password_repeat'], ' ('.$l['w_repeat'].')', '', '', 255, false, false, true);
echo getFormRowFixedValue('user_regdate', $l['w_regdate'], $l['h_regdate'], '', $user_regdate);
echo getFormRowFixedValue('user_ip', $l['w_ip'], $l['h_ip'], '', $user_ip);
echo getFormRowSelectBox('user_level', $l['w_level'], $l['h_level'], '', $user_level, array(0,1,2,3,4,5,6,7,8,9,10));
echo getFormRowTextInput('user_regnumber', $l['w_regcode'], $l['h_regcode'], '', $user_regnumber, '', 255, false, false, false);
echo getFormRowTextInput('user_firstname', $l['w_firstname'], $l['h_firstname'], '', $user_firstname, '', 255, false, false, false);
echo getFormRowTextInput('user_lastname', $l['w_lastname'], $l['h_lastname'], '', $user_lastname, '', 255, false, false, false);
echo getFormRowTextInput('user_birthdate', $l['w_birth_date'], $l['h_birth_date'].' '.$l['w_date_format'], '', $user_birthdate, '', 10, true, false, false);
echo getFormRowTextInput('user_birthplace', $l['w_birth_place'], $l['h_birth_place'], '', $user_birthplace, '', 255, false, false, false);
echo getFormRowTextInput('user_ssn', $l['w_fiscal_code'], $l['h_fiscal_code'], '', $user_ssn, '', 255, false, false, false);

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="user_groups">'.$l['w_groups'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="user_groups[]" id="user_groups" size="5" multiple="multiple">'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_GROUPS.' ORDER BY group_name';
if ($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['group_id'].'"';
		if (!F_isAuthorizedEditorForGroup($m['group_id'])) {
			echo ' style="text-decoration:line-through;"';
		}
		if (F_isUserOnGroup($user_id, $m['group_id'])) {
			echo ' selected="selected"';
			$m['group_name'] = '* '.$m['group_name'];
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
// show buttons by case
if (isset($user_id) AND ($user_id > 0)) {
	if (($user_level < $_SESSION['session_user_level']) OR ($user_id == $_SESSION['session_user_id']) OR ($_SESSION['session_user_level'] >= K_AUTH_ADMINISTRATOR)) {
		F_submit_button('update', $l['w_update'], $l['h_update']);
	}
	if (($user_id > 1) AND ($_SESSION['session_user_level'] >= K_AUTH_DELETE_USERS) AND ($user_id != $_SESSION['session_user_id'])) {
		// your account and anonymous user can't be deleted
		F_submit_button('delete', $l['w_delete'], $l['h_delete']);
	}
}
F_submit_button('add', $l['w_add'], $l['h_add']);
F_submit_button('clear', $l['w_clear'], $l['h_clear']);

echo '<input type="hidden" name="user_password" id="user_password" value="'.$user_password.'" />'.K_NEWLINE;

echo '<input type="hidden" name="ff_required" id="ff_required" value="user_name" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_edit_user'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
