<?php
//============================================================+
// File name   : tce_user_registration.php
// Begin       : 2008-03-30
// Last Update : 2018-07-06
//
// Description : User registration form.
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
//    Copyright (C) 2004-2018 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display user registration form.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
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
require_once('../../shared/code/tce_functions_otp.php');

$thispage_title = $l['t_user_registration'];
$thispage_description = $l['hp_user_registration'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');

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
$reqfields = array();
$reqdesc = array();
foreach ($regfields as $field => $required) {
    if ($required == 2) {
        $reqfields[] = $field;
        $reqdesc[] = htmlspecialchars($fielddesc[$field], ENT_COMPAT, $l['a_meta_charset']);
    }
}
$_REQUEST['ff_required'] = implode(',', $reqfields);
$_REQUEST['ff_required_labels'] = implode(',', $reqdesc);

if ($menu_mode == 'add') { // process submitted data

    foreach ($regfields as $name => $enabled) {
        // disable unauthorized fields
        if (!$enabled) {
            $$name = '';
        }
    }

    if ($formstatus = F_check_form_fields()) { // check submitted form fields
        // check if name is unique
        if (!F_check_unique(K_TABLE_USERS, 'user_name=\''.F_escape_sql($db, $user_name).'\'')) {
            F_print_error('WARNING', $l['m_duplicate_name']);
            $formstatus = false;
            F_stripslashes_formfields();
        }
        // check if registration number is unique
        if (isset($user_regnumber) and (strlen($user_regnumber) > 0) and (!F_check_unique(K_TABLE_USERS, 'user_regnumber=\''.F_escape_sql($db, $user_regnumber).'\''))) {
            F_print_error('WARNING', $l['m_duplicate_regnumber']);
            $formstatus = false;
             F_stripslashes_formfields();
        }
        // check if ssn is unique
        if (isset($user_ssn) and (strlen($user_ssn) > 0) and (!F_check_unique(K_TABLE_USERS, 'user_ssn=\''.F_escape_sql($db, $user_ssn).'\''))) {
            F_print_error('WARNING', $l['m_duplicate_ssn']);
            $formstatus = false;
            F_stripslashes_formfields();
        }
        // check password
        if (!empty($newpassword) or !empty($newpassword_repeat)) {// update password
            if ($newpassword == $newpassword_repeat) {
                $user_password = getPasswordHash($newpassword);
                // update OTP key
                $user_otpkey = F_getRandomOTPkey();
            } else { //print message and exit
                F_print_error('WARNING', $l['m_different_passwords']);
                $formstatus = false;
                F_stripslashes_formfields();
            }
        } else { //print message and exit
            F_print_error('WARNING', $l['m_empty_password']);
            $formstatus = false;
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
				user_verifycode,
				user_otpkey
				) VALUES (
				\''.F_escape_sql($db, $user_regdate).'\',
				\''.F_escape_sql($db, $user_ip).'\',
				\''.F_escape_sql($db, $user_name).'\',
				'.F_empty_to_null($user_email).',
				\''.F_escape_sql($db, $user_password).'\',
				'.F_empty_to_null($user_regnumber).',
				'.F_empty_to_null($user_firstname).',
				'.F_empty_to_null($user_lastname).',
				'.F_empty_to_null($user_birthdate).',
				'.F_empty_to_null($user_birthplace).',
				'.F_empty_to_null($user_ssn).',
				\''.$usrlevel.'\',
				\''.$user_verifycode.'\',
				'.F_empty_to_null($user_otpkey).'
				)';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                $user_id = F_db_insert_id($db, K_TABLE_USERS, 'user_id');
            }
            // add user's groups
            if (empty($user_groups)) {
                $user_groups = array(K_USRREG_GROUP);
            } elseif (!in_array(K_USRREG_GROUP, $user_groups)) {
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
                if (!$r = F_db_query($sql, $db)) {
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
            }
            echo '<div class="container">'.K_NEWLINE;
            echo '<strong><a href="index.php" title="'.$l['h_index'].'">'.$l['h_index'].' &gt;</a></strong>'.K_NEWLINE;
            echo '</div>'.K_NEWLINE;
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

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_usereditor">'.K_NEWLINE;


echo getFormRowTextInput('user_name', $l['w_username'], $l['h_login_name'], '', $user_name, '', 255, false, false, false, showRequiredField($regfields['user_name']));
if (K_USRREG_EMAIL_CONFIRM or $regfields['user_email']) {
    echo getFormRowTextInput('user_email', $l['w_email'], $l['h_usered_email'], '', $user_email, K_EMAIL_RE_PATTERN, 255, false, false, false, showRequiredField($regfields['user_email']));
}
echo getFormRowTextInput('newpassword', $l['w_password'], $l['h_password'], ' ('.$l['d_password_lenght'].')', '', K_USRREG_PASSWORD_RE, 255, false, false, true, showRequiredField(2));
echo getFormRowTextInput('newpassword_repeat', $l['w_password'], $l['h_password_repeat'], ' ('.$l['w_repeat'].')', '', '', 255, false, false, true, showRequiredField(2));
if ($regfields['user_regnumber']) {
    echo getFormRowTextInput('user_regnumber', $l['w_regcode'], $l['h_regcode'], '', $user_regnumber, '', 255, false, false, false, showRequiredField($regfields['user_regnumber']));
}
if ($regfields['user_firstname']) {
    echo getFormRowTextInput('user_firstname', $l['w_firstname'], $l['h_firstname'], '', $user_firstname, '', 255, false, false, false, showRequiredField($regfields['user_firstname']));
}
if ($regfields['user_lastname']) {
    echo getFormRowTextInput('user_lastname', $l['w_lastname'], $l['h_lastname'], '', $user_lastname, '', 255, false, false, false, showRequiredField($regfields['user_lastname']));
}
if ($regfields['user_birthdate']) {
    echo getFormRowTextInput('user_birthdate', $l['w_birth_date'], $l['h_birth_date'].' '.$l['w_date_format'], '', $user_birthdate, '', 10, true, false, false, showRequiredField($regfields['user_birthdate']));
}
if ($regfields['user_birthplace']) {
    echo getFormRowTextInput('user_birthplace', $l['w_birth_place'], $l['h_birth_place'], '', $user_birthplace, '', 255, false, false, false, showRequiredField($regfields['user_birthplace']));
}
if ($regfields['user_ssn']) {
    echo getFormRowTextInput('user_ssn', $l['w_fiscal_code'], $l['h_fiscal_code'], '', $user_ssn, '', 255, false, false, false, showRequiredField($regfields['user_ssn']));
}
if ($regfields['user_groups']) {
    echo '<div class="row">'.K_NEWLINE;
    echo '<span class="label">'.K_NEWLINE;
    echo '<label for="user_groups">'.$l['w_groups'].'</label>'.K_NEWLINE;
    echo showRequiredField($regfields['user_groups']);
    echo '</span>'.K_NEWLINE;
    echo '<span class="formw">'.K_NEWLINE;
    echo '<select name="user_groups[]" id="user_groups" size="5" multiple="multiple">'.K_NEWLINE;
    $sql = 'SELECT *
		FROM '.K_TABLE_GROUPS.'
		ORDER BY group_name';
    if ($r = F_db_query($sql, $db)) {
        while ($m = F_db_fetch_array($r)) {
            echo '<option value="'.$m['group_id'].'"';
            if (in_array($m['group_id'], $user_groups) or ($m['group_id'] == K_USRREG_GROUP)) {
                echo ' selected="selected"';
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
}

if ($regfields['user_agreement'] > 0) {
    echo '<div class="row">'.K_NEWLINE;
    echo '<span class="label">&nbsp;</span>'.K_NEWLINE;
    echo '<span class="formw">'.K_NEWLINE;
    echo '<input type="checkbox" name="user_agreement" id="user_agreement" value="1" title="..." />'.K_NEWLINE;
    echo '<label for="user_agreement"><a href="'.K_USRREG_AGREEMENT.'" title="'.$l['m_new_window_link'].'">'.$l['w_i_agree'].'</a></label></span>'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;
}

echo '<div class="row">'.K_NEWLINE;

F_submit_button('add', $l['w_add'], $l['h_add']);

echo '</div>'.K_NEWLINE;
echo F_getCSRFTokenField().K_NEWLINE;
echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_user_registration'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
