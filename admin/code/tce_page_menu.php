<?php
//============================================================+
// File name   : tce_page_menu.php
// Begin       : 2004-04-20
// Last Update : 2010-05-10
//
// Description : Output XHTML unordered list menu.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Output XHTML unordered list menu.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2004-04-20
 */

/**
 */

require_once('../config/tce_auth.php');

$ie6_iframe = '<!--[if lte IE 6]><iframe class="menu"></iframe><![endif]-->'.K_NEWLINE;

echo '<a name="menusection" id="menusection"></a>'.K_NEWLINE;

// link to skip navigation
echo '<div class="hidden">';
echo '<a href="#topofdoc" accesskey="2" title="[2] '.$l['w_skip_navigation'].'">'.$l['w_skip_navigation'].'</a>';
echo '</div>'.K_NEWLINE;

echo '<ul class="menu">'.K_NEWLINE;
echo '<li>'.F_menu_link('index.php', $l['h_index'], $l['w_index'], K_AUTH_INDEX, '1').'</li>'.K_NEWLINE;

echo '<li>'.F_menu_link('tce_menu_users.php', '', $l['w_users'], K_AUTH_ADMIN_USERS).$ie6_iframe.'<ul>'.K_NEWLINE;

echo '<li>'.F_menu_link('tce_edit_user.php', $l['t_user_editor'], $l['w_users'], K_AUTH_ADMIN_USERS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_edit_group.php', $l['t_group_editor'], $l['w_groups'], K_AUTH_ADMIN_USERS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_select_users.php', $l['t_user_select'], $l['w_select'], K_AUTH_ADMIN_USERS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_show_online_users.php', $l['t_online_users'], $l['w_online'], K_AUTH_ADMIN_USERS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_import_xml_users.php', $l['t_user_importer'], $l['w_import'], K_AUTH_ADMIN_USERS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_show_allresults_users.php', $l['t_all_results_user'], $l['w_results'], K_AUTH_ADMIN_RESULTS).'</li>'.K_NEWLINE;
echo '</ul></li>'.K_NEWLINE;

echo '<li>'.F_menu_link('tce_menu_modules.php', '', $l['w_modules'], K_AUTH_ADMIN_MODULES).$ie6_iframe.'<ul>'.K_NEWLINE;

echo '<li>'.F_menu_link('tce_edit_module.php', $l['t_modules_editor'], $l['w_modules'], K_AUTH_ADMIN_MODULES).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_edit_subject.php', $l['t_subjects_editor'], $l['w_subjects'], K_AUTH_ADMIN_SUBJECTS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_edit_question.php', $l['t_questions_editor'], $l['w_questions'], K_AUTH_ADMIN_QUESTIONS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_edit_answer.php', $l['t_answers_editor'], $l['w_answers'], K_AUTH_ADMIN_ANSWERS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_show_all_questions.php', $l['t_questions_list'], $l['w_list'], K_AUTH_ADMIN_RESULTS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_import_xml_questions.php', $l['t_question_importer'], $l['w_import'], K_AUTH_ADMIN_SUBJECTS).'</li>'.K_NEWLINE;
echo '</ul></li>'.K_NEWLINE;

echo '<li>'.F_menu_link('tce_menu_tests.php', '', $l['w_tests'], K_AUTH_ADMIN_TESTS).$ie6_iframe.'<ul>'.K_NEWLINE;

echo '<li>'.F_menu_link('tce_edit_test.php', $l['t_tests_editor'], $l['w_tests'], K_AUTH_ADMIN_TESTS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_edit_rating.php', $l['t_rating_editor'], $l['w_rating'], K_AUTH_ADMIN_RATING).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_show_result_allusers.php', $l['t_result_all_users'], $l['w_results'], K_AUTH_ADMIN_RESULTS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_show_result_user.php', $l['t_result_user'], $l['w_users'], K_AUTH_ADMIN_RESULTS).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_show_result_questions.php', $l['t_result_questions'], $l['w_stats'], K_AUTH_ADMIN_RESULTS).'</li>'.K_NEWLINE;
echo '</ul></li>'.K_NEWLINE;
if ((K_DATABASE_TYPE == 'MYSQL') OR (K_DATABASE_TYPE == 'POSTGRESQL')) {
	echo '<li>'.F_menu_link('tce_edit_backup.php', $l['t_backup_editor'], $l['w_backup'], K_AUTH_ADMINISTRATOR).'</li>'.K_NEWLINE;
}
echo '<li>'.F_menu_link('http://www.tcexam.org', $l['h_guide'], $l['w_guide'], K_AUTH_ADMIN_INFO).'</li>'.K_NEWLINE;
echo '<li>'.F_menu_link('tce_page_info.php', $l['h_info'], $l['w_info'], K_AUTH_ADMIN_INFO).'</li>'.K_NEWLINE;
if ($_SESSION['session_user_level'] > 0) {
	echo '<li>'.F_menu_link('tce_logout.php', $l['h_logout_link'], $l['w_logout'], 0, ($_SESSION['session_user_level'] > 1)).'</li>'.K_NEWLINE;
} else {
	echo '<li>'.F_menu_link('index.php', $l['h_login_button'], $l['w_login'], 0, ($_SESSION['session_user_level'] > 1)).'</li>'.K_NEWLINE;
}
echo '</ul>'.K_NEWLINE;

/**
 * Returns a menu element link.
 * If the link refers to the current page, only the name will be returned.
 * @param string $link URL
 * @param string $title title attribute
 * @param string $name link caption
 * @param int $level required level to access the link
 * @param string $accesskey html accesskey attribute value
 * @param boolean $enabled set to false to disable the link
 * @return string a list element containing a menu element
 */
function F_menu_link($link, $title, $name, $level=0, $accesskey='', $enabled=true) {
	global $l, $db;
	require_once('../config/tce_config.php');
	if($_SESSION['session_user_level'] < $level) {
		$enabled = false;
	}
	$page_file = basename($link);
	if (($pos = strpos($page_file, '?')) !== FALSE) {
		$page_file = substr($page_file, 0, $pos);
	}
	if (($pos = strpos($page_file, '#')) !== FALSE) {
		$page_file = substr($page_file, 0, $pos);
	}
	if ($enabled AND (!empty($link)) AND ($page_file != basename($_SERVER['SCRIPT_NAME']))) {
		$str = '<a href="'.$link.'" title="'.$title.'"';
		if (!empty($accesskey)) {
			$str .= ' accesskey="'.$accesskey.'"';
		}
		$str .= '>'.$name.'</a>'.K_NEWLINE;
	} else {
		//disabled link
		$str = $name.K_NEWLINE;
	}
	return $str;
}

//============================================================+
// END OF FILE
//============================================================+
?>
