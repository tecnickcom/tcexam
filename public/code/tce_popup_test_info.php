<?php
//============================================================+
// File name   : tce_popup_test_info.php
// Begin       : 2004-05-28
// Last Update : 2010-09-17
//
// Description : Output test information using popup page
//               headers.
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
 * Output test information using popup page headers.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2004-05-28
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_PUBLIC_TEST_INFO;
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
