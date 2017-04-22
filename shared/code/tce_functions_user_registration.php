<?php
//============================================================+
// File name   : tce_functions_user_registration.php
// Begin       : 2008-03-31
// Last Update : 2011-05-20
//
// Description : Support functions for user registration.
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Support functions for user registration.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2003-03-31
 */

/**
 * Send a registration verification email to user.
 * @param $user_id (int) User ID
 * @param $user_email (string) User email
 * @param $user_verifycode (string) user verification code
 */
function F_send_user_reg_email($user_id, $user_email, $user_verifycode)
{
    global $l, $db;
    require_once('../config/tce_config.php');
    require_once('../../shared/code/tce_class_mailer.php');
    require_once('../../shared/config/tce_email_config.php');
    require_once('../../shared/config/tce_user_registration.php');
    require_once('../../shared/code/tce_functions_html2txt.php');

    $user_id = intval($user_id);

    // Instantiate C_mailer class
    $mail = new C_mailer;

    //Load default values
    $mail->setLanguageData($l);
    $mail->Priority = $emailcfg['Priority'];
    $mail->ContentType = $emailcfg['ContentType'];
    $mail->Encoding = $emailcfg['Encoding'];
    $mail->WordWrap = $emailcfg['WordWrap'];
    $mail->Mailer = $emailcfg['Mailer'];
    $mail->Sendmail = $emailcfg['Sendmail'];
    $mail->UseMSMailHeaders = $emailcfg['UseMSMailHeaders'];
    $mail->Host = $emailcfg['Host'];
    $mail->Port = $emailcfg['Port'];
    $mail->Helo = $emailcfg['Helo'];
    $mail->SMTPAuth = $emailcfg['SMTPAuth'];
    $mail->SMTPSecure = $emailcfg['SMTPSecure'];
    $mail->Username = $emailcfg['Username'];
    $mail->Password = $emailcfg['Password'];
    $mail->Timeout = $emailcfg['Timeout'];
    $mail->SMTPDebug = $emailcfg['SMTPDebug'];
    $mail->PluginDir = $emailcfg['PluginDir'];
    $mail->Sender = $emailcfg['Sender'];
    $mail->From = $emailcfg['From'];
    $mail->FromName = $emailcfg['FromName'];
    if ($emailcfg['Reply']) {
        $mail->AddReplyTo($emailcfg['Reply'], $emailcfg['ReplyName']);
    }

    $mail->CharSet = $l['a_meta_charset'];
    if (!$mail->CharSet) {
        $mail->CharSet = $emailcfg['CharSet'];
    }

    $mail->Subject = $l['w_registration_verification'];
    $mail->Body = $l['m_email_registration'];

    $mail->IsHTML(true); // Sets message type to HTML.

    //$userverifycode
    //compose confirmation URL
    $subscribe_url = ''.K_PATH_PUBLIC_CODE.'tce_user_verification.php?a='.$user_email.'&amp;b='.$user_verifycode.'&amp;c='.$user_id.'';

    //--- Elaborate Templates ---
    $mail->Body = str_replace('#EMAIL#', $user_email, $mail->Body);
    $mail->Body = str_replace('#USERIP#', $_SERVER['REMOTE_ADDR'], $mail->Body);
    $mail->Body = str_replace('#SUBSCRIBEURL#', $subscribe_url, $mail->Body);
    $mail->Body = str_replace('#TCEXAMURL#', K_PATH_HOST.K_PATH_TCEXAM, $mail->Body);

    //compose alternative TEXT message body
    $mail->AltBody = F_html_to_text($mail->Body, false, true);

    $mail->AddAddress($user_email, ''); //Adds a "To" address
    if (strlen(K_USRREG_ADMIN_EMAIL) > 0) {
        // add administrator to BCC field
        $mail->AddBCC(K_USRREG_ADMIN_EMAIL);
    }
    if (!$mail->Send()) { //send email to user
        F_print_error('ERROR', 'EMAIL ERROR');
    }

    $mail->ClearAddresses(); // Clear all addresses for next loop
    $mail->ClearCustomHeaders(); // Clears all custom headers
    $mail->ClearAllRecipients(); // Clears all recipients assigned in the TO, CC and BCC
    $mail->ClearAttachments(); // Clears all previously set filesystem, string, and binary attachments
    $mail->ClearReplyTos(); // Clears all recipients assigned in the ReplyTo array
}

//============================================================+
// END OF FILE
//============================================================+
