<?php
//============================================================+
// File name   : index.php
// Begin       : 2004-04-20
// Last Update : 2009-09-30
// 
// Description : main user page - allows test selection
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
 * Main user page - allows test selection.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2004-04-20
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = 1;
$thispage_title = $l['t_public_index'];
$thispage_description = $l['hp_public_index'];

require_once('../../shared/code/tce_authorization.php');
require_once('tce_page_header.php');

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tcecontentbox">'.K_NEWLINE;
require_once('../../shared/code/tce_functions_test.php');

if (isset($_REQUEST['repeat']) AND ($_REQUEST['repeat'] == 1) AND isset($_REQUEST['testid'])) {
	// remove the specified test results
	F_removeTestResults($_REQUEST['testid']);
}

echo F_getUserTests();
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$thispage_description.'</div>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

require_once('tce_page_footer.php');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
