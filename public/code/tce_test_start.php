<?php
//============================================================+
// File name   : tce_test_start.php
// Begin       : 2010-02-06
// Last Update : 2010-02-06
//
// Description : Display selected test description and buttons
//               to start or cancel the test.
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com S.r.l.
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
 * Display selected test description and buttons to start or cancel the test.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2010-02-06
 */

/**
 */

require_once('../config/tce_config.php');

$test_id = 0;
$pagelevel = 1;
$thispage_title = $l['t_test_info'];
$thispage_description = $l['hp_test_info'];
require_once('../../shared/code/tce_authorization.php');

require_once('../code/tce_page_header.php');

echo '<div class="popupcontainer">'.K_NEWLINE;

if (isset($_REQUEST['testid']) AND ($_REQUEST['testid'] > 0)) {
	$test_id = intval($_REQUEST['testid']);
	require_once('../../shared/code/tce_functions_test.php');
	echo F_printTestInfo($test_id, false);
	echo '<br />'.K_NEWLINE;
	echo '<div class="row">'.K_NEWLINE;

	echo '<a href="tce_test_execute.php?testid='.$test_id.'" title="'.$l['h_execute'].'" class="xmlbutton">'.$l['w_execute'].'</a>';

	echo ' <a href="index.php" title="'.$l['h_cancel'].'" class="xmlbutton">'.$l['w_cancel'].'</a>';

	echo '</div>'.K_NEWLINE;
}
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
?>
