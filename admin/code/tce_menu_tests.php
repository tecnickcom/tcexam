<?php
//============================================================+
// File name   : tce_menu_tests.php
// Begin       : 2004-04-20
// Last Update : 2010-05-10
//
// Description : Output XHTML unordered list menu for tests.
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
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com S.r.l.
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
 * Output XHTML unordered list menu for tests.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2010-05-10
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = 1;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['w_tests'];
require_once('../code/tce_page_header.php');

echo '<div class="container">'.K_NEWLINE;

echo '<ul>'.K_NEWLINE;

echo '<li>'.F_menu_link('tce_edit_test.php', $l['t_tests_editor'], $l['w_tests'], K_AUTH_ADMIN_TESTS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_edit_rating.php', $l['t_rating_editor'], $l['w_rating'], K_AUTH_ADMIN_RATING).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_show_result_allusers.php', $l['t_result_all_users'], $l['w_results'], K_AUTH_ADMIN_RESULTS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_show_result_user.php', $l['t_result_user'], $l['w_users'], K_AUTH_ADMIN_RESULTS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_show_result_questions.php', $l['t_result_questions'], $l['w_stats'], K_AUTH_ADMIN_RESULTS).'</li>'.K_NEWLINE;

echo '</ul>'.K_NEWLINE;

//echo '<div class="pagehelp">'.$l['w_tests'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
