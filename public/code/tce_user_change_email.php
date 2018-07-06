<?php
//============================================================+
// File name   : tce_user_change_email.php
// Begin       : 2010-09-17
// Last Update : 2018-07-06
//
// Description : Form to change user email
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
 * Form to change user email
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2010-09-17
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_USER_CHANGE_EMAIL;
$thispage_title = $l['t_user_change_email'];
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../code/tce_page_header.php');

$user_id = intval($_SESSION['session_user_id']);

// comma separated list of required fields
$_REQUEST['ff_required'] = 'user_email,user_email_repeat';
$_REQUEST['ff_required_labels'] = htmlspecialchars($l['w_email'].','.$l['w_email'], ENT_COMPAT, $l['a_meta_charset']);

// process submitted data
switch ($menu_mode) {
    case 'update':{ // Update user
        if ($formstatus = F_check_form_fields()) {
            // check password
            if (empty($user_email) or empty($user_email_repeat) or ($user_email != $user_email_repeat)) {
                //print message and exit
                F_print_error('WARNING', $l['m_different_emails']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            $sql = 'SELECT user_password FROM '.K_TABLE_USERS.' WHERE user_id='.$user_id;
            if ($r = F_db_query($sql, $db)) {
                if (!($m = F_db_fetch_array($r)) or !checkPassword($currentpassword, $m['user_password'])) {
                    F_print_error('WARNING', $l['m_login_wrong']);
                    $formstatus = false;
                    F_stripslashes_formfields();
                    break;
                }
            } else {
                F_display_db_error(false);
                break;
            }
            $user_verifycode = getNewSessionID(); // verification code
            $sql = 'UPDATE '.K_TABLE_USERS.' SET
				user_email=\''.F_escape_sql($db, $user_email).'\',
				user_level=\'0\',
				user_verifycode=\''.$user_verifycode.'\'
				WHERE user_id='.$user_id;
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                F_print_error('MESSAGE', $l['m_email_updated']);
                // require email confirmation
                require_once('../../shared/code/tce_functions_user_registration.php');
                F_send_user_reg_email($user_id, $user_email, $user_verifycode);
                F_print_error('MESSAGE', $user_email.': '.$l['m_user_verification_sent']);
                echo '<div class="container">'.K_NEWLINE;
                echo '<strong><a href="index.php" title="'.$l['h_index'].'">'.$l['h_index'].' &gt;</a></strong>'.K_NEWLINE;
                echo '</div>'.K_NEWLINE;
                require_once('tce_page_footer.php');
                exit;
            }
        }
        break;
    }

    default :{
        break;
    }
} //end of switch

echo '<div class="container">'.K_NEWLINE;

echo '<div class="gsoformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_editor">'.K_NEWLINE;

echo getFormRowTextInput('user_email', $l['w_new_email'], $l['h_email'], '', '', K_EMAIL_RE_PATTERN, 255, false, false, false, '');
echo getFormRowTextInput('user_email_repeat', $l['w_new_email'], $l['h_email'], ' ('.$l['w_repeat'].')', '', '', 255, false, false, false, '');
echo getFormRowTextInput('currentpassword', $l['w_password'], $l['h_password'], '', '', '', 255, false, false, true, '');

echo '<div class="row">'.K_NEWLINE;

F_submit_button('update', $l['w_update'], $l['h_update']);

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;


echo '<div class="pagehelp">'.$l['hp_user_change_email'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once(dirname(__FILE__).'/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
