<?php
//============================================================+
// File name   : tce_email_config.php
// Begin       : 2001-10-20
// Last Update : 2009-11-05
//
// Description : Default values for public variables of
//				 C_mailer class
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * @file
 * Email configuration file.
 * @package com.tecnick.tcexam.shared.cfg
 * @author Nicola Asuni
 * @since 2005-02-24
 */

/**
 */

// Email priority (1 = High, 3 = Normal, 5 = Low). Default value is 3.
$emailcfg['Priority'] = 3;

// Sets the CharSet of the message. Default value is 'iso-8859-1'.
$emailcfg['CharSet'] = 'UTF-8';

// Sets the Content-type of the message. Default value is 'text/plain'.
$emailcfg['ContentType'] = 'text/plain';

// Sets the Encoding of the message. Options for this are '8bit' (default), '7bit', 'binary', 'base64', and 'quoted-printable'.
$emailcfg['Encoding'] = '8bit';

// Sets the Encoding of the attachments. Default value is 'base64'
$emailcfg['AttachmentsEncoding'] = 'base64';

// Sets the default Administrator email. The join requests and confirmations will be sent to this address.
$emailcfg['AdminEmail'] = '';

// Sets the From email address for the message. Default value is 'root@localhost'.
$emailcfg['From'] = '';

// Sets the From name of the message. Default value is 'Root User'.
$emailcfg['FromName'] = 'TCExam';

// Sets the Sender email of the message. If not empty, will be sent via -f to sendmail * or as 'MAIL FROM' in smtp mode. Default value is ''.
$emailcfg['Sender'] = '';

// Sets 'Reply-To' address.
$emailcfg['Reply'] = '';

// Sets 'Reply-To' name.
$emailcfg['ReplyName'] = '';

// Sets word wrapping on the message. Default value is false (off).
$emailcfg['WordWrap'] = false;

// Method to send mail: ('mail', 'sendmail', or 'smtp').
$emailcfg['Mailer'] = 'smtp';

// Sets the path of the sendmail program. Default value is '/usr/sbin/sendmail'.
$emailcfg['Sendmail'] = '/usr/sbin/sendmail';

// Turns Microsoft mail client headers on and off. Default value is false (off).
$emailcfg['UseMSMailHeaders'] = false;

// Sets default value for Header of messages.
$emailcfg['MsgHeader'] = "
<"."?xml version=\"1.0\" encoding=\"#CHARSET#\"?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"#LANG#\" lang=\"#LANG#\" dir=\"#LANGDIR#\">
<body>
";

//Sets default value for Footer of messages.
$emailcfg['MsgFooter'] = '</body></html>';

// -----------------------------------------------------------------------------
// --- SMTP VARIABLES ----------------------------------------------------------
// -----------------------------------------------------------------------------

// Sets the SMTP hosts. All hosts must be separated by a semicolon (e.g. Host("smtp1.domain.com;smtp2.domain.com"). Hosts will be tried in order.
$emailcfg['Host'] = 'smtp.gmail.com';

// Sets the SMTP server port. Default value is 25.
$emailcfg['Port'] = 465;

// Default value is 'localhost.localdomain'.
$emailcfg['Helo'] = '';

// Sets SMTP authentication. Remember to set the Username and Password. Default value is false (off).
$emailcfg['SMTPAuth'] = true;

// Sets the prefix to the server. Options are '', 'ssl' or 'tls'.
$emailcfg['SMTPSecure'] = 'ssl';

// Sets SMTP username. Default value is ''.
$emailcfg['Username'] = '';

// Sets SMTP password. Default value is ''.
$emailcfg['Password'] = '';

// Sets the SMTP server timeout in seconds.
$emailcfg['Timeout'] = 10;

// Sets SMTP class debugging on or off. Default value is false (off).
$emailcfg['SMTPDebug'] = false;

// Sets plugins directory path
$emailcfg['PluginDir'] = '../../shared/phpmailer/';

//============================================================+
// END OF FILE
//============================================================+
