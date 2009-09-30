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
 * Renders TCExam code using popup headers.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2002-01-30
 * @uses F_decode_tcecode
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
?>
