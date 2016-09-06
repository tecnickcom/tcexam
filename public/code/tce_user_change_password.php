<?php
//============================================================+
// File name   : tce_user_change_password.php
// Begin       : 2010-09-17
// Last Update : 2012-06-07
//
// Description : Form to change user password
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
//    Copyright (C) 2004-2012 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Form to change user password
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2010-09-17
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_USER_CHANGE_PASSWORD;
$thispage_title = $l['t_user_change_password'];
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/config/tce_user_registration.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../code/tce_page_header.php');

$user_id = intval($_SESSION['session_user_id']);

// process submited data
switch ($menu_mode) {
    case 'update':{ // Update user
        if ($formstatus = F_check_form_fields()) {
            // check password
            if (!empty($newpassword) or !empty($newpassword_repeat)) {
                if ($newpassword == $newpassword_repeat) {
                    $user_password = getPasswordHash($newpassword);
                } else { //print message and exit
                    F_print_error('WARNING', $l['m_different_passwords']);
                    $formstatus = false;
                    F_stripslashes_formfields();
                    break;
                }
            }
            $sql = 'UPDATE '.K_TABLE_USERS.' SET
				user_password=\''.F_escape_sql($db, $user_password).'\'
				WHERE user_id='.$user_id.' AND user_password=\''.getPasswordHash($currentpassword).'\'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                F_print_error('MESSAGE', $l['m_password_updated']);
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

echo getFormRowTextInput('currentpassword', $l['w_current_password'], $l['h_password'], '', '', '', 255, false, false, true, '');
echo getFormRowTextInput('newpassword', $l['w_new_password'], $l['h_password'], ' ('.$l['d_password_lenght'].')', '', K_USRREG_PASSWORD_RE, 255, false, false, true, '');
echo getFormRowTextInput('newpassword_repeat', $l['w_new_password'], $l['h_password_repeat'], ' ('.$l['w_repeat'].')', '', '', 255, false, false, true, '');

echo '<div class="row">'.K_NEWLINE;

F_submit_button('update', $l['w_update'], $l['h_update']);

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="currentpassword,newpassword,newpassword_repeat" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_current_password'].','.$l['w_new_password'].','.$l['w_new_password'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_user_change_password'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once(dirname(__FILE__).'/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
