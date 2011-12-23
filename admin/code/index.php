<?php
//============================================================+
// File name   : index.php
// Begin       : 2004-04-29
// Last Update : 2010-06-16
//
// Description : Main page of administrator section.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               Manor Coach House, Church Hill
//               Aldershot, Hants, GU12 4RQ
//               UK
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
 * Main page of TCExam Administration Area.
 * @package com.tecnick.tcexam.admin
 * @brief TCExam Administration Area
 * @author Nicola Asuni
 * @since 2004-04-20
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = K_AUTH_INDEX;
require_once('../../shared/code/tce_authorization.php');
require_once('tce_page_header.php');

echo '<div style="border:1px solid black; padding:5px; margin:10px; background-color:#DDEEFF; color:#000000; width:95%; margin-left:auto; margin-right:auto; font-weight:bold; font-size:95%;">YOU CAN\'T REMOVE, MOVE OR HIDE THE ORIGINAL TCEXAM LOGO, COPYRIGHTS STATEMENTS AND LINKS TO <a href="http://www.tecnick.com" title="External link to Tecnick.com">TECNICK.COM</a> AND <a href="http://www.tcexam.org" title="External link to TCExam">TCEXAM</a> WEBSITES FROM THIS SOFTWARE. TCEXAM IS SUBJECT TO THE <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html" title="External link to GNU Affero General Public License">GNU-AGPL v.3 LICENSE.</a></div>'.K_NEWLINE;

echo '<div style="border:1px solid black; padding:5px; margin:10px; background-color:#DDEEFF; color:#000000; width:95%; margin-left:auto; margin-right:auto; font-weight:bold; font-size:95%;">CONTACT <a href="mailto:info@tecnick.com" title="mail to tecnick.com">info@tecnick.com</a> FOR COMMERCIAL USAGE.</div>'.K_NEWLINE;

echo '<div style="border:1px solid black; padding:5px; margin:10px; background-color:#DDEEFF; color:#000000; width:95%; margin-left:auto; margin-right:auto; font-weight:bold; font-size:95%;">PLEASE SUPPORT TCEXAM <a href="http://sourceforge.net/donate/index.php?group_id=159398" title="External link to make a donation for TCExam">MAKING A DONATION</a>.</div>'.K_NEWLINE;

echo $l['d_admin_index'];

require_once('tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
