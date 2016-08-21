<?php
//============================================================+
// File name   : tce_user_verification.php
// Begin       : 2008-03-31
// Last Update : 2012-11-22
//
// Description : User verification.
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
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * User verification.
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

$email = preg_replace('/[^a-zA-Z0-9_\.\-\@]/', '', $_REQUEST['a']);
$verifycode = preg_replace('/[^A-Fa-f0-9\@]/', '', $_REQUEST['b']);
$userid = intval($_REQUEST['c']);

$pagelevel = 0;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_user_registration'];
$thispage_description = '';
require_once('../code/tce_page_header.php');

$sql = 'SELECT *
	FROM '.K_TABLE_USERS.'
	WHERE (user_verifycode=\''.F_escape_sql($db, $verifycode).'\'
		AND user_id=\''.$userid.'\'
		AND user_email=\''.F_escape_sql($db, $email).'\')
		LIMIT 1';
if ($r = F_db_query($sql, $db)) {
    if ($m = F_db_fetch_array($r)) {
        // update user level
        if ($verifycode[0] == '@') {
            // password reset
            $new_password = substr(md5(uniqid(mt_rand(), true)), 0, 8);
            $sqlu = 'UPDATE '.K_TABLE_USERS.' SET user_password=\''.getPasswordHash($new_password).'\', user_verifycode=NULL WHERE user_id='.$userid.'';
        } else {
            // user registration
            $sqlu = 'UPDATE '.K_TABLE_USERS.' SET user_level=\'1\', user_verifycode=NULL WHERE user_id='.$userid.'';
        }
        if (!$ru = F_db_query($sqlu, $db)) {
            F_display_db_error(false);
        } else {
            if ($verifycode[0] == '@') {
                F_print_error('MESSAGE', $l['w_new_password'].': '.$new_password);
            } else {
                F_print_error('MESSAGE', $l['m_user_registration_ok']);
            }
            echo K_NEWLINE;
            echo '<div class="container">'.K_NEWLINE;
            if (K_OTP_LOGIN) {
                require_once('../../shared/tcpdf/tcpdf_barcodes_2d.php');
                $host = preg_replace('/[h][t][t][p][s]?[:][\/][\/]/', '', K_PATH_HOST);
                $qrcode = new TCPDF2DBarcode('otpauth://totp/'.$m['user_name'].'@'.$host.'?secret='.$m['user_otpkey'], 'QRCODE,H');
                echo '<p>'.$l['m_otp_qrcode'].'</p>'.K_NEWLINE;
                echo '<h2>'.$m['user_otpkey'].'</h2>'.K_NEWLINE;
                echo '<div style="margin:40px 40px 40px 40px;">'.K_NEWLINE;
                echo $qrcode->getBarcodeHTML(6, 6, 'black');
                echo '</div>'.K_NEWLINE;
            }
            echo '<strong><a href="index.php" title="'.$l['h_index'].'">'.$l['h_index'].' &gt;</a></strong>'.K_NEWLINE;
            echo '</div>'.K_NEWLINE;
            require_once('../code/tce_page_footer.php');
            exit;
        }
    }
} else {
    F_display_db_error(false);
}

F_print_error('ERROR', 'USER VERIFICATION ERROR');
echo K_NEWLINE;
echo '<div class="container">'.K_NEWLINE;
echo '<strong><a href="index.php" title="'.$l['h_index'].'">'.$l['h_index'].' &gt;</a></strong>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
