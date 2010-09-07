<?php
//============================================================+
// File name   : tce_page_menu.php
// Begin       : 2004-04-20
// Last Update : 2010-09-07
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
//    Additionally, you can't remove, move or hide the original TCExam logo,
//    copyrights statements and links to Tecnick.com and TCExam websites.
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

$menu = array(	
	'index.php' => array('link' => 'index.php', 'title' => $l['h_index'], 'name' => $l['w_index'], 'level' => K_AUTH_INDEX, 'key' => '', 'enabled' => true),
	'tce_menu_users.php' => array('link' => 'tce_menu_users.php', 'title' => $l['w_users'], 'name' => $l['w_users'], 'level' => K_AUTH_ADMIN_USERS, 'key' => '', 'enabled' => true),
	'tce_menu_modules.php' => array('link' => 'tce_menu_modules.php', 'title' => $l['w_modules'], 'name' => $l['w_modules'], 'level' => K_AUTH_ADMIN_MODULES, 'key' => '', 'enabled' => true),
	'tce_menu_tests.php' => array('link' => 'tce_menu_tests.php', 'title' => $l['w_tests'], 'name' => $l['w_tests'], 'level' => K_AUTH_ADMIN_TESTS, 'key' => '', 'enabled' => true),
	'tce_edit_backup.php' => array('link' => 'tce_edit_backup.php', 'title' => $l['t_backup_editor'], 'name' => $l['w_backup'], 'level' => K_AUTH_BACKUP, 'key' => '', 'enabled' => ((K_DATABASE_TYPE == 'MYSQL') OR (K_DATABASE_TYPE == 'POSTGRESQL'))),
	'tcexam.org' => array('link' => 'http://www.tcexam.org', 'title' => $l['h_guide'], 'name' => $l['w_guide'], 'level' => K_AUTH_ADMIN_INFO, 'key' => '', 'enabled' => true),
	'tce_page_info.php' => array('link' => 'tce_page_info.php', 'title' => $l['h_info'], 'name' => $l['w_info'], 'level' => K_AUTH_ADMIN_INFO, 'key' => '', 'enabled' => true),
	'tce_logout.php' => array('link' => 'tce_logout.php', 'title' => $l['h_logout_link'], 'name' => $l['w_logout'], 'level' => 1, 'key' => '', 'enabled' => ($_SESSION['session_user_level'] > 0)),
	'tce_login.php' => array('link' => 'tce_login.php', 'title' => $l['h_login_button'], 'name' => $l['w_login'], 'level' => 0, 'key' => '', 'enabled' => ($_SESSION['session_user_level'] < 1))
);

$menu['tce_menu_users.php']['sub'] = array(
	'tce_edit_user.php' => array('link' => 'tce_edit_user.php', 'title' => $l['t_user_editor'], 'name' => $l['w_users'], 'level' => K_AUTH_ADMIN_USERS, 'key' => '', 'enabled' => true),
	'tce_edit_group.php' => array('link' => 'tce_edit_group.php', 'title' => $l['t_group_editor'], 'name' => $l['w_groups'], 'level' => K_AUTH_ADMIN_USERS, 'key' => '', 'enabled' => true),
	'tce_select_users.php' => array('link' => 'tce_select_users.php', 'title' => $l['t_user_select'], 'name' => $l['w_select'], 'level' => K_AUTH_ADMIN_USERS, 'key' => '', 'enabled' => true),
	'tce_show_online_users.php' => array('link' => 'tce_show_online_users.php', 'title' => $l['t_online_users'], 'name' => $l['w_online'], 'level' => K_AUTH_ADMIN_USERS, 'key' => '', 'enabled' => true),
	'tce_import_xml_users.php' => array('link' => 'tce_import_xml_users.php', 'title' => $l['t_user_importer'], 'name' => $l['w_import'], 'level' => K_AUTH_ADMIN_USERS, 'key' => '', 'enabled' => true),
	'tce_show_allresults_users.php' => array('link' => 'tce_show_allresults_users.php', 'title' => $l['t_all_results_user'], 'name' => $l['w_results'], 'level' => K_AUTH_ADMIN_RESULTS, 'key' => '', 'enabled' => true)
);

$menu['tce_menu_modules.php']['sub'] = array(
	'tce_edit_module.php' => array('link' => 'tce_edit_module.php', 'title' => $l['t_modules_editor'], 'name' => $l['w_modules'], 'level' => K_AUTH_ADMIN_MODULES, 'key' => '', 'enabled' => true),
	'tce_edit_subject.php' => array('link' => 'tce_edit_subject.php', 'title' => $l['t_subjects_editor'], 'name' => $l['w_subjects'], 'level' => K_AUTH_ADMIN_SUBJECTS, 'key' => '', 'enabled' => true),
	'tce_edit_question.php' => array('link' => 'tce_edit_question.php', 'title' => $l['t_questions_editor'], 'name' => $l['w_questions'], 'level' => K_AUTH_ADMIN_QUESTIONS, 'key' => '', 'enabled' => true),
	'tce_edit_answer.php' => array('link' => 'tce_edit_answer.php', 'title' => $l['t_answers_editor'], 'name' => $l['w_answers'], 'level' => K_AUTH_ADMIN_ANSWERS, 'key' => '', 'enabled' => true),
	'tce_show_all_questions.php' => array('link' => 'tce_show_all_questions.php', 'title' => $l['t_questions_list'], 'name' => $l['w_list'], 'level' => K_AUTH_ADMIN_RESULTS, 'key' => '', 'enabled' => true),
	'tce_import_xml_questions.php' => array('link' => 'tce_import_xml_questions.php', 'title' => $l['t_question_importer'], 'name' => $l['w_import'], 'level' => K_AUTH_ADMIN_IMPORT, 'key' => '', 'enabled' => true)
);

$menu['tce_menu_tests.php']['sub'] = array(
	'tce_edit_test.php' => array('link' => 'tce_edit_test.php', 'title' => $l['t_tests_editor'], 'name' => $l['w_tests'], 'level' => K_AUTH_ADMIN_TESTS, 'key' => '', 'enabled' => true),
	'tce_edit_rating.php' => array('link' => 'tce_edit_rating.php', 'title' => $l['t_rating_editor'], 'name' => $l['w_rating'], 'level' => K_AUTH_ADMIN_RATING, 'key' => '', 'enabled' => true),
	'tce_show_result_allusers.php' => array('link' => 'tce_show_result_allusers.php', 'title' => $l['t_result_all_users'], 'name' => $l['w_results'], 'level' => K_AUTH_ADMIN_RESULTS, 'key' => '', 'enabled' => true),
	'tce_show_result_user.php' => array('link' => 'tce_show_result_user.php', 'title' => $l['t_result_user'], 'name' => $l['w_users'], 'level' => K_AUTH_ADMIN_RESULTS, 'key' => '', 'enabled' => true),
	'tce_show_result_questions.php' => array('link' => 'tce_show_result_questions.php', 'title' => $l['t_result_questions'], 'name' => $l['w_stats'], 'level' => K_AUTH_ADMIN_RESULTS, 'key' => '', 'enabled' => true)
);

echo '<a name="menusection" id="menusection"></a>'.K_NEWLINE;

// link to skip navigation
echo '<div class="hidden">';
echo '<a href="#topofdoc" accesskey="2" title="[2] '.$l['w_skip_navigation'].'">'.$l['w_skip_navigation'].'</a>';
echo '</div>'.K_NEWLINE;

echo '<ul class="menu">'.K_NEWLINE;
foreach ($menu as $link => $data) {
	echo F_menu_link($link, $data, 0);
}
echo '</ul>'.K_NEWLINE; // end of menu


/**
 * Returns a menu element link wit subitems.
 * If the link refers to the current page, only the name will be returned.
 * @param string $link URL
 * @param array $data link data
 * @param int $level item level
 */
function F_menu_link($link, $data, $level=0) {
	global $l, $db;
	require_once('../config/tce_config.php');
	if (!$data['enabled'] OR ($_SESSION['session_user_level'] < $data['level'])) {
		// this item is disabled
		return;
	}
	$str = '<li>';
	if ($link != basename($_SERVER['SCRIPT_NAME'])) {
		$str .= '<a href="'.$data['link'].'" title="'.$data['title'].'"';
		if (!empty($data['key'])) {
			$str .= ' accesskey="'.$data['key'].'"';
		}
		if (isset($data['sub']) AND (!empty($data['sub'])) AND (array_key_exists(basename($_SERVER['SCRIPT_NAME']), $data['sub']))) {
			$str .= ' class="active"';
		}
		$str .= '>'.$data['name'].'</a>';
	} else {
		// active link
		$str .= '<span class="active">'.$data['name'].'</span>';
	}
	if (isset($data['sub']) AND !empty($data['sub'])) {
		// print sub-items
		$sublevel = ($level + 1);
		$str .= K_NEWLINE.'<!--[if lte IE 6]><iframe class="menu"></iframe><![endif]-->'.K_NEWLINE;
		$str .= '<ul>'.K_NEWLINE;
		foreach ($data['sub'] as $sublink => $subdata) {
			$str .= F_menu_link($sublink, $subdata, $sublevel);
		}
		$str .= '</ul>'.K_NEWLINE;
	}
	$str .= '</li>'.K_NEWLINE;
	return $str;
}

//============================================================+
// END OF FILE
//============================================================+
