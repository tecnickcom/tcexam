<?php
//============================================================+
// File name   : tce_preview_tcecode.php
// Begin       : 2002-01-30
// Last Update : 2009-09-30
//
// Description : Renders TCExam code using popup headers.
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
 * Renders TCExam code using popup headers.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2002-01-30
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = K_AUTH_ADMIN_TCECODE;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = '';

require_once('../code/tce_page_header_popup.php');

require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_form.php');
$tcexamcode = str_replace('+', '~#PLUS#~', $_REQUEST['tcexamcode']);
$tcexamcode = stripslashes(urldecode($tcexamcode));
$tcexamcode = str_replace('~#PLUS#~', '+', $tcexamcode);
echo F_decode_tcecode($tcexamcode);

echo '<hr />'.K_NEWLINE;

echo F_close_button();

require_once('../code/tce_page_footer_popup.php');

//============================================================+
// END OF FILE
//============================================================+
