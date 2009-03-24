<?php
//============================================================+
// File name   : tce_user_registration.php
// Begin       : 2008-03-30
// Last Update : 2009-02-13
// 
// Description : User registration form.
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
 * Display user registration form.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2008-03-30
 */

/**
 */

require_once('../config/tce_config.php');

require_once('../../shared/config/tce_user_registration.php');
if (!K_USRREG_ENABLED) {
	// user registration is disabled, redirect to main page
	header('Location: '.K_PATH_HOST.K_PATH_TCEXAM);
	exit;
}

$pagelevel = 0;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_user_registration'];
$thispage_description = $l['hp_user_registration'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');

if ($menu_mode == "add") { // process submited data
	
	foreach ($regfields as $name => $enabled) {
		// disable unauthorized fields
		if (!$enabled) {
			$$name = '';
		}
	}

	if($formstatus = F_check_form_fields()) { // check submitted form fields
		// check if name is unique
		if(!F_check_unique(K_TABLE_USERS, 'user_name=\''.$user_name.'\'')) {
			F_print_error('WARNING', $l['m_duplicate_name']);
			$formstatus = FALSE;
			F_stripslashes_formfields();
		}
		// check if registration number is unique
		if(isset($user_regnumber) AND (strlen($user_regnumber) > 0) AND (!F_check_unique(K_TABLE_USERS, 'user_regnumber=\''.F_escape_sql($user_regnumber).'\''))) {
			F_print_error('WARNING', $l['m_duplicate_regnumber']);
			$formstatus = FALSE;
			 F_stripslashes_formfields();
		}
		// check if ssn is unique
		if(isset($user_ssn) AND (strlen($user_ssn) > 0) AND (!F_check_unique(K_TABLE_USERS, 'user_ssn=\''.F_escape_sql($user_ssn).'\''))) {
			F_print_error('WARNING', $l['m_duplicate_ssn']);
			$formstatus = FALSE;
			F_stripslashes_formfields();
		}
		// check password
		if(!empty($newpassword) OR !empty($newpassword_repeat)) {// update password
			if($newpassword == $newpassword_repeat) { 
				$user_password = md5($newpassword);
			} else { //print message and exit
				F_print_error('WARNING', $l['m_different_passwords']);
				$formstatus = FALSE;
				F_stripslashes_formfields();
			}
		} else { //print message and exit
			F_print_error('WARNING', $l['m_empty_password']);
			$formstatus = FALSE;
			F_stripslashes_formfields();
		}
		if ($formstatus) {
			mt_srand((double) microtime() * 1000000);
			$user_verifycode = md5(uniqid(mt_rand(), true)); // verification code
			$user_ip = getNormalizedIP($_SERVER['REMOTE_ADDR']); // get the user's IP number
			$user_regdate = date(K_TIMESTAMP_FORMAT); // get the registration date and time
			
			if (K_USRREG_EMAIL_CONFIRM) {
				$usrlevel = 0;
			} else {
				$usrlevel = 1;
			}
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
				user_level,
				user_verifycode
				) VALUES (
				\''.F_escape_sql($user_regdate).'\', 
				\''.F_escape_sql($user_ip).'\', 
				\''.F_escape_sql($user_name).'\', 
				'.F_empty_to_null(F_escape_sql($user_email)).', 
				\''.F_escape_sql($user_password).'\', 
				'.F_empty_to_null(F_escape_sql($user_regnumber)).', 
				'.F_empty_to_null(F_escape_sql($user_firstname)).', 
				'.F_empty_to_null(F_escape_sql($user_lastname)).', 
				'.F_empty_to_null(F_escape_sql($user_birthdate)).', 
				'.F_empty_to_null(F_escape_sql($user_birthplace)).', 
				'.F_empty_to_null(F_escape_sql($user_ssn)).', 
				\''.$usrlevel.'\',
				\''.$user_verifycode.'\'
				)';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$user_id = F_db_insert_id($db, K_TABLE_USERS, 'user_id');
			}
			// add user's groups
			if (empty($user_groups)) {
				$user_groups = array(K_USRREG_GROUP);
			} elseif(!in_array(K_USRREG_GROUP,$user_groups)) {
				$user_groups[] = K_USRREG_GROUP;
			}
			foreach ($user_groups as $group_id) {
				$sql = 'INSERT INTO '.K_TABLE_USERGROUP.' (
					usrgrp_user_id,
					usrgrp_group_id
					) VALUES (
					\''.$user_id.'\', 
					\''.$group_id.'\'
					)';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				}
			}
			if (K_USRREG_EMAIL_CONFIRM) {
				// require email confirmation
				require_once('../../shared/code/tce_functions_user_registration.php');
				F_send_user_reg_email($user_id, $user_email, $user_verifycode);
				F_print_error('MESSAGE', $user_email.': '.$l['m_user_verification_sent']);
			} else {
				F_print_error('MESSAGE', $l['m_user_registration_ok']);
				echo K_NEWLINE;
				echo '<div class="container">'.K_NEWLINE;
				echo '<strong><a href="index.php" title="'.$l['h_index'].'">'.$l['h_index'].' &gt;</a></strong>'.K_NEWLINE;
				echo '</div>'.K_NEWLINE;
			}
			require_once('../code/tce_page_footer.php');
			exit;
		}
	}
} //end of add


// --- Initialize variables
if (isset($_REQUEST['user_name'])) {
	$user_name = htmlspecialchars($_REQUEST['user_name'], ENT_COMPAT, $l['a_meta_charset']);
} else {
	$user_name = '';
}
if (isset($_REQUEST['user_email'])) {
	$user_email = htmlspecialchars($_REQUEST['user_email'], ENT_COMPAT, $l['a_meta_charset']);
} else {
	$user_email = '';
}
if (isset($_REQUEST['user_password'])) {
	$user_password = $_REQUEST['user_password'];
} else {
	$user_password = '';
}
if (isset($_REQUEST['user_regnumber'])) {
	$user_regnumber = htmlspecialchars($_REQUEST['user_regnumber'], ENT_COMPAT, $l['a_meta_charset']);
} else {
	$user_regnumber = '';
}
if (isset($_REQUEST['user_firstname'])) {
	$user_firstname = htmlspecialchars($_REQUEST['user_firstname'], ENT_COMPAT, $l['a_meta_charset']);
} else {
	$user_firstname = '';
}
if (isset($_REQUEST['user_lastname'])) {
	$user_lastname = htmlspecialchars($_REQUEST['user_lastname'], ENT_COMPAT, $l['a_meta_charset']);
} else {
	$user_lastname = '';
}
if (isset($_REQUEST['user_birthdate'])) {
	$user_birthdate = htmlspecialchars($_REQUEST['user_birthdate'], ENT_COMPAT, $l['a_meta_charset']);
} else {
	$user_birthdate = '';
}
if (isset($_REQUEST['user_birthplace'])) {
	$user_birthplace = htmlspecialchars($_REQUEST['user_birthplace'], ENT_COMPAT, $l['a_meta_charset']);
} else {
	$user_birthplace = '';
}
if (isset($_REQUEST['user_ssn'])) {
	$user_ssn = htmlspecialchars($_REQUEST['user_ssn'], ENT_COMPAT, $l['a_meta_charset']);
} else {
	$user_ssn = '';
}
if (isset($_REQUEST['user_groups'])) {
	$user_groups = $_REQUEST['user_groups'];
} else {
	$user_groups = array();
}

// some fields are always required
$regfields['user_name'] = 2;
$regfields['newpassword'] = 2;
$regfields['newpassword_repeat'] = 2;
if (K_USRREG_EMAIL_CONFIRM) {
	$regfields['user_email'] = 2;
}
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_usereditor">

<div class="row">
<span class="label">
<label for="user_name"><?php echo $l['w_username']; ?></label>
<?php echo showRequiredField($regfields['user_name']); ?></span>
<span class="formw">
<input type="text" name="user_name" id="user_name" value="<?php echo $user_name; ?>" size="20" maxlength="255" title="<?php echo $l['h_login_name']; ?>" />
</span>
</div>

<?php if (K_USRREG_EMAIL_CONFIRM OR $regfields['user_email']) { ?>
<div class="row">
<span class="label">
<label for="user_email"><?php echo $l['w_email']; ?></label>
<?php echo showRequiredField($regfields['user_email']); ?></span>
<span class="formw">
<input type="hidden" name="x_user_email" id="x_user_email" value="^([a-zA-Z0-9_\.\-]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$" />
<input type="hidden" name="xl_user_email" id="xl_user_email" value="<?php echo $l['w_email']; ?>" />
<input type="text" name="user_email" id="user_email" value="<?php echo $user_email; ?>" size="20" maxlength="255" title="<?php echo $l['h_usered_email']; ?>" />
</span>
</div>
<?php } ?>

<div class="row">
<span class="label">
<label for="newpassword"><?php echo $l['w_password']; ?></label>
<?php echo showRequiredField(2); ?></span>
<span class="formw">
<input type="password" name="newpassword" id="newpassword" size="20" maxlength="255" title="<?php echo $l['h_password']; ?>" />
<input type="hidden" name="user_password" id="user_password" value="<?php echo $user_password; ?>" />
</span>
</div>

<div class="row">
<span class="label">
<label for="newpassword_repeat"><?php echo $l['w_password']; ?></label>
<?php echo showRequiredField(2); ?></span>
<span class="formw">
<input type="password" name="newpassword_repeat" id="newpassword_repeat" size="20" maxlength="255" title="<?php echo $l['h_password_repeat']; ?>" />
</span>
</div>

<?php if ($regfields['user_regnumber']) { ?>
<div class="row">
<span class="label">
<label for="user_regnumber"><?php echo $l['w_regcode']; ?></label>
<?php echo showRequiredField($regfields['user_regnumber']); ?></span>
<span class="formw">
<input type="text" name="user_regnumber" id="user_regnumber" value="<?php echo $user_regnumber; ?>" size="20" maxlength="255" title="<?php echo $l['h_regcode']; ?>" />
</span>
</div>
<?php } ?>

<?php if ($regfields['user_firstname']) { ?>
<div class="row">
<span class="label">
<label for="user_firstname"><?php echo $l['w_firstname']; ?></label>
<?php echo showRequiredField($regfields['user_firstname']); ?></span>
<span class="formw">
<input type="text" name="user_firstname" id="user_firstname" value="<?php echo $user_firstname; ?>" size="20" maxlength="255" title="<?php echo $l['h_firstname']; ?>" />
</span>
</div>
<?php } ?>

<?php if ($regfields['user_lastname']) { ?>
<div class="row">
<span class="label">
<label for="user_lastname"><?php echo $l['w_lastname']; ?></label>
<?php echo showRequiredField($regfields['user_lastname']); ?></span>
<span class="formw">
<input type="text" name="user_lastname" id="user_lastname" value="<?php echo $user_lastname; ?>" size="20" maxlength="255" title="<?php echo $l['h_lastname']; ?>" />
</span>
</div>
<?php } ?>

<?php if ($regfields['user_birthdate']) { ?>
<div class="row">
<span class="label">
<label for="user_birthdate"><?php echo $l['w_birth_date']; ?></label>
<?php echo showRequiredField($regfields['user_birthdate']); ?></span>
<span class="formw">
<input type="hidden" name="x_user_birthdate" id="x_user_birthdate" value="^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$" />
<input type="hidden" name="xl_user_birthdate" id="xl_user_birthdate" value="<?php echo $l['w_birth_date']; ?>" />
<input type="text" name="user_birthdate" id="user_birthdate" value="<?php echo $user_birthdate; ?>" size="20" maxlength="10" title="<?php echo $l['h_birth_date']; ?> <?php echo $l['w_date_format']; ?>" />
</span>
</div>
<?php } ?>

<?php if ($regfields['user_birthplace']) { ?>
<div class="row">
<span class="label">
<label for="user_birthplace"><?php echo $l['w_birth_place']; ?></label>
<?php echo showRequiredField($regfields['user_birthplace']); ?></span>
<span class="formw">
<input type="text" name="user_birthplace" id="user_birthplace" value="<?php echo $user_birthplace; ?>" size="20" maxlength="255" title="<?php echo $l['h_birth_place']; ?>" />
</span>
</div>
<?php } ?>

<?php if ($regfields['user_ssn']) { ?>
<div class="row">
<span class="label">
<label for="user_ssn"><?php echo $l['w_fiscal_code']; ?></label>
<?php echo showRequiredField($regfields['user_ssn']); ?></span>
<span class="formw">
<input type="text" name="user_ssn" id="user_ssn" value="<?php echo $user_ssn; ?>" size="20" maxlength="255" title="<?php echo $l['h_fiscal_code']; ?>" />
</span>
</div>
<?php } ?>

<?php if ($regfields['user_groups']) { ?>
<div class="row">
<span class="label">
<label for="user_groups"><?php echo $l['w_groups']; ?></label>
<?php echo showRequiredField($regfields['user_groups']); ?></span>
<span class="formw">
<select name="user_groups[]" id="user_groups" size="5" multiple="multiple">
<?php
$sql = 'SELECT *
	FROM '.K_TABLE_GROUPS.'
	ORDER BY group_name';
if($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['group_id'].'"';
		if(in_array($m['group_id'],$user_groups) OR ($m['group_id']==K_USRREG_GROUP)) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($m['group_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
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
<?php } ?>

<?php if ($regfields['user_agreement'] > 0) { ?>
<div class="row">
<span class="label">
&nbsp;
</span>
<span class="formw">
<input type="checkbox" name="user_agreement" id="user_agreement" value="1" title="<?php echo "..."; ?>" />
<label for="user_agreement"><?php echo '<a href="'.K_USRREG_AGREEMENT.'" title="'.$l['m_new_window_link'].'">'.$l['w_i_agree'].'</a>'; ?></label></span>
</div>
<?php } ?>

<div class="row">
<?php
F_submit_button("add", $l['w_add'], $l['h_add']);

// set fields descriptions for error messages
$fielddesc = array (
	'user_name' => $l['w_name'],
	'newpassword' => $l['w_password'],
	'newpassword_repeat' => $l['w_password'],
	'user_email' => $l['w_email'],
	'user_regnumber' => $l['w_regcode'],
	'user_firstname' => $l['w_firstname'],
	'user_lastname' => $l['w_lastname'],
	'user_birthdate' => $l['w_birth_date'],
	'user_birthplace' => $l['w_birth_place'],
	'user_ssn' => $l['w_fiscal_code'],
	'user_groups' => $l['w_groups'],
	'user_agreement' => $l['w_i_agree']
);
$reqfields = '';
$reqdesc = '';
foreach ($regfields as $field => $required) {
	if ($required == 2) {
		$reqfields .= ','.$field;
		$reqdesc .= ','.htmlspecialchars($fielddesc[$field], ENT_COMPAT, $l['a_meta_charset']);
	}
}
$reqfields = substr($reqfields,1);
$reqdesc = substr($reqdesc,1);
?>
<!-- comma separated list of required fields -->
<input type="hidden" name="ff_required" id="ff_required" value="<?php echo $reqfields; ?>" />
<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="<?php echo $reqdesc; ?>" />
</div>

</form>
</div>

<?php

echo '<div class="pagehelp">'.$l['hp_user_registration'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
