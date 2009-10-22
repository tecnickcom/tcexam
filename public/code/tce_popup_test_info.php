<?php
//============================================================+
// File name   : tce_popup_test_info.php
// Begin       : 2004-05-28
// Last Update : 2009-09-30
// 
// Description : Output test information using popup page
//               headers.
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
 * Output test information using popup page headers.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2004-05-28
 * @uses F_printTestInfo
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = 1;
$thispage_title = $l['t_test_info'];
$thispage_description = $l['hp_test_info'];
require_once('../../shared/code/tce_authorization.php');

require_once('../code/tce_page_header_popup.php');

echo '<div class="popupcontainer">'.K_NEWLINE;
if (isset($_REQUEST['testid']) AND ($_REQUEST['testid'] > 0)) {
	require_once('../../shared/code/tce_functions_test.php');
	echo F_printTestInfo(intval($_REQUEST['testid']), false);
}

echo '<div class="row">'.K_NEWLINE;
require_once('../../shared/code/tce_functions_form.php');
echo F_close_button();
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer_popup.php');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
