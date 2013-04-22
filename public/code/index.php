<?php
//============================================================+
// File name   : index.php
// Begin       : 2004-04-20
// Last Update : 2012-12-04
//
// Description : main user page - allows test selection
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
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
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
 * Main page of TCExam Public Area.
 * @package com.tecnick.tcexam.public
 * @brief TCExam Public Area
 * @author Nicola Asuni
 * @since 2004-04-20
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_PUBLIC_INDEX;
$thispage_title = $l['t_test_list'];
$thispage_description = $l['hp_public_index'];

require_once('../../shared/code/tce_authorization.php');
require_once('tce_page_header.php');

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tcecontentbox">'.K_NEWLINE;
require_once('../../shared/code/tce_functions_test.php');

echo F_getUserTests();
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$thispage_description.'</div>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

require_once('tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
