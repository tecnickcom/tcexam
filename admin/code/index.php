<?php
//============================================================+
// File name   : index.php
// Begin       : 2004-04-29
// Last Update : 2009-10-22
// 
// Description : Main page of administrator section.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * main page of administration section 
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2004-04-20
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = K_AUTH_INDEX;
require_once('../../shared/code/tce_authorization.php');
require_once('tce_page_header.php');

echo '<div style="border:1px solid black; padding:5px; margin:10px; background-color:#DDEEFF; color:#000000; width:95%; margin-left:auto; margin-right:auto; font-weight:bold; font-size:95%;">YOU CAN\'T REMOVE OR MOVE THE ORIGINAL TCEXAM LOGO, COPYRIGHTS STATEMENTS AND LINKS TO <a href="http://www.tecnick.com" title="External link to Tecnick.com">TECNICK.COM</a> AND <a href="http://www.tcexam.org" title="External link to TCExam">TCEXAM</a> WEBSITES FROM THIS SOFTWARE. TCEXAM IS SUBJECT TO THE <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html" title="External link to GNU Affero General Public License">GNU-AGPL v.3 LICENSE.</a></div>'.K_NEWLINE;

echo '<div style="border:1px solid black; padding:5px; margin:10px; background-color:#DDEEFF; color:#000000; width:95%; margin-left:auto; margin-right:auto; font-weight:bold; font-size:95%;">PLEASE CONTACT <a href="mailto:info@tecnick.com" title="mail to tecnick.com">info@tecnick.com</a> FOR COMMERCIAL USAGE.</div>'.K_NEWLINE;

echo '<div style="border:1px solid black; padding:5px; margin:10px; background-color:#DDEEFF; color:#000000; width:95%; margin-left:auto; margin-right:auto; font-weight:bold; font-size:95%;">IF YOU\'D LIKE TO SUPPORT TCEXAM, PLEASE CONSIDER <a href="http://sourceforge.net/donate/index.php?group_id=159398" title="External link to make a donation for TCExam">MAKING A DONATION</a>.</div>'.K_NEWLINE;

echo $l['d_admin_index'];

require_once('tce_page_footer.php');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
