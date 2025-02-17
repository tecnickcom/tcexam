<?php

//============================================================+
// File name   : tce_password_reset.php
// Begin       : 2012-04-14
// Last Update : 2023-11-30
//
// Description : Password Reset form.
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
//    Copyright (C) 2004-2025 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display password reset form.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2008-03-30
 */



require_once('../config/tce_config.php');

if (! defined('K_PASSWORD_RESET') || ! K_PASSWORD_RESET) {
    // password reset is disabled, redirect to main page
    header('Location: ' . K_PATH_HOST . K_PATH_TCEXAM);
    exit;
}

$pagelevel = 0;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_password_assistance'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');

// comma separated list of required fields
$_REQUEST['ff_required'] = 'user_email';
$_REQUEST['ff_required_labels'] = htmlspecialchars($l['w_email'], ENT_COMPAT, $l['a_meta_charset']);
;


// process submitted data
if (isset($_POST['resetpassword']) && ($formstatus = F_check_form_fields())) {
    // check submitted form fields
    mt_srand((float) microtime() * 1_000_000);
    $user_verifycode = md5(uniqid(random_int(0, mt_getrandmax()), true));
    // verification code
    $user_verifycode[0] = '@';
    // get user ID
    $user_id = 0;
    $sql = 'SELECT user_id FROM ' . K_TABLE_USERS . " WHERE user_email='" . F_escape_sql($db, $user_email) . "'";
    if ($r = F_db_query($sql, $db)) {
        if ($m = F_db_fetch_array($r)) {
            $user_id = $m['user_id'];
        }
    } else {
        F_display_db_error();
    }

    if ($user_id > 0) {
        // update verification code
        $sqlu = 'UPDATE ' . K_TABLE_USERS . " SET user_verifycode='" . F_escape_sql($db, $user_verifycode) . "' WHERE user_id=" . $user_id . '';
        if (! $ru = F_db_query($sqlu, $db)) {
            F_display_db_error();
        }

        // send email confirmation
        require_once('../../shared/code/tce_functions_user_registration.php');
        F_send_user_reg_email($user_id, $user_email, $user_verifycode);
    }

    F_print_error('MESSAGE', $user_email . ': ' . $l['m_user_verification_sent']);
    echo '<div class="container">' . K_NEWLINE;
    echo '<strong><a href="index.php" title="' . $l['h_index'] . '">' . $l['h_index'] . ' &gt;</a></strong>' . K_NEWLINE;
    echo '</div>' . K_NEWLINE;
    require_once('../code/tce_page_footer.php');
    exit;
} //end of add

echo '<div class="container">' . K_NEWLINE;

echo '<div class="tceformbox">' . K_NEWLINE;
echo '<form action="' . $_SERVER['SCRIPT_NAME'] . '" method="post" enctype="multipart/form-data" id="form_usereditor">' . K_NEWLINE;

echo '<p>' . $l['d_reset_password'] . '</p>' . K_NEWLINE;

echo getFormRowTextInput('user_email', $l['w_email'], $l['h_usered_email'], '', '', K_EMAIL_RE_PATTERN, 255, false, false, false, '');

echo '<div class="row">' . K_NEWLINE;

F_submit_button('resetpassword', $l['w_submit'], $l['h_submit']);

echo '</div>' . K_NEWLINE;
echo F_getCSRFTokenField() . K_NEWLINE;
echo '</form>' . K_NEWLINE;
echo '</div>' . K_NEWLINE;

echo '</div>' . K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
