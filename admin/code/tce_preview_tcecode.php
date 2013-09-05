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
