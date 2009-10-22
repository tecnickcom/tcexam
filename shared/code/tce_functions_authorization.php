<?php
//============================================================+
// File name   : tce_functions_authorization.php
// Begin       : 2001-09-26
// Last Update : 2009-09-30
// 
// Description : Functions for Authorization / LOGIN
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
 * Functions for Authorization / LOGIN
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-09-26
 */

/**
 * Returns XHTML / CSS formatted string for login form.<br>
 * The CSS classes used are:
 * <ul>
 * <li>div.login_form : container for login box</li>
 * <li>div.login_form div.login_row : container for label + input field or button</li>
 * <li>div.login_form div.login_row span.label : container for input label</li>
 * <li>div.login_form div.login_row span.formw : container for input form</li>
 * </ul>
 * @param faction String action attribute
 * @param fid String form ID attribute
 * @param fmethod String method attribute (get/post)
 * @param fenctype String enctype attribute
 * @param username String user name
 * @param password String password
 * @param isize int enctype input fields horizontal size
 * @return XHTML string for login form
 */
function F_loginForm($faction, $fid, $fmethod, $fenctype, $username, $password, $isize=20) {
	global $l;
	require_once('../config/tce_config.php');
	require_once('../../shared/config/tce_user_registration.php');
	$str = '';
	$str .= '<div class="container">'.K_NEWLINE;
	if (K_USRREG_ENABLED) {
		$str .= '<small><a href="../../public/code/tce_user_registration.php" title="'.$l['t_user_registration'].'">'.$l['w_user_registration_link'].'</a></small>'.K_NEWLINE;
	}
	$str .= '<div class="tceformbox">'.K_NEWLINE;
	$str .= '<form action="'.$faction.'" method="'.$fmethod.'" id="'.$fid.'" enctype="'.$fenctype.'">'.K_NEWLINE;
	// user name
	$str .= '<div class="row">'.K_NEWLINE;
	$str .= '<span class="label">'.K_NEWLINE;
	$str .= '<label for="xuser_name">'.$l['w_username'].'</label>'.K_NEWLINE;
	$str .= '</span>'.K_NEWLINE;
	$str .= '<span class="formw">'.K_NEWLINE;
	$str .= '<input type="text" name="xuser_name" id="xuser_name" value="'.$username.'" size="'.$isize.'" maxlength="255" title="'.$l['h_login_name'].'" />'.K_NEWLINE;
	$str .= '</span>'.K_NEWLINE;
	$str .= '</div>'.K_NEWLINE;
	// password
	$str .= '<div class="row">'.K_NEWLINE;
	$str .= '<span class="label">'.K_NEWLINE;
	$str .= '<label for="xuser_password">'.$l['w_password'].'</label>'.K_NEWLINE;
	$str .= '</span>'.K_NEWLINE;
	$str .= '<span class="formw">'.K_NEWLINE;
	$str .= '<input type="password" name="xuser_password" id="xuser_password" value="" size="'.$isize.'" maxlength="255" title="'.$l['h_password'].'" />'.K_NEWLINE;
	$str .= '</span>'.K_NEWLINE;
	$str .= '</div>'.K_NEWLINE;
	// buttons
	$str .= '<div class="row">'.K_NEWLINE;
	// the following field is used to check if form has been submitted
	$str .= '<input type="submit" name="login" id="login" value="'.$l['w_login'].'" title="'.$l['h_login_button'].'" />'.K_NEWLINE;
	$str .= '<input type="hidden" name="logaction" id="logaction" value="login" />'.K_NEWLINE;
	$str .= '</div>'.K_NEWLINE;
	$str .= '</form>'.K_NEWLINE;
	$str .= '</div>'.K_NEWLINE;
	$str .= '<div class="pagehelp">'.$l['hp_login'].'</div>'.K_NEWLINE;
	$str .= '</div>'.K_NEWLINE;
	return $str;
}

/**
 * Display login page.
 * NOTE: This function calls exit() after execution.
 */
function F_login_form() {
	global $l, $thispage_title;
	global $xuser_name, $xuser_password;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	$thispage_title = $l['t_login_form']; //set page title
	require_once('../code/tce_page_header.php');
	echo F_loginForm($_SERVER['SCRIPT_NAME'], 'form_login', 'post', 'multipart/form-data', $xuser_name, $xuser_password, 20);
	require_once('../code/tce_page_footer.php');
	exit(); //break page here
}


/**
 * Display logout form.
 * @return XHTML string for logout form.
 */
function F_logout_form() {
	global $l;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	$str = K_NEWLINE;
	$str .= '<div class="container">'.K_NEWLINE;
	$str .= '<div class="tceformbox">'.K_NEWLINE;
	$str .= '<form action="../code/tce_logout.php" method="post" id="form_logout" enctype="multipart/form-data">'.K_NEWLINE;
	// description
	$str .= '<div class="row">'.K_NEWLINE;
	$str .= $l['d_logout_desc'].K_NEWLINE;
	$str .= '</div>'.K_NEWLINE;
	// buttons
	$str .= '<div class="row">'.K_NEWLINE;
	// the following field is used to check if form has been submitted
	$str .= '<input type="hidden" name="current_page" id="current_page" value="'.$_SERVER['SCRIPT_NAME'].'" />'.K_NEWLINE;
	$str .= '<input type="hidden" name="logaction" id="logaction" value="" />'.K_NEWLINE;
	$str .= '<input type="submit" name="login" id="login" value="'.$l['w_logout'].'" />'.K_NEWLINE;
	$str .= '</div>'.K_NEWLINE;
	$str .= '</form>'.K_NEWLINE;
	$str .= '</div>'.K_NEWLINE;
	return $str;
}

/**
 * Display logout page.
 * NOTE: This function calls exit() after execution.
 */
function F_logout_page() {
	global $l, $thispage_title;
	require_once('../config/tce_config.php');
	$thispage_title = $l['t_logout_form']; // set page title
	require_once('../code/tce_page_header.php');
	echo F_logout_form();
	require_once('../code/tce_page_footer.php');
	exit();
}

/**
 * Returns true if the current user is authorized to update and delete the selected database record.
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2006-03-11
 * @param string $table table to be modified
 * @param string $field_id_name name of the main ID field of the table
 * @param int $value_id value of the ID field of the table
 * @param string $field_user_id name of the foreign key to to user_id
 * @return boolean true if the user is authorized, false otherwise
 */
function F_isAuthorizedUser($table, $field_id_name, $value_id, $field_user_id) {
	global $l,$db;
	require_once('../config/tce_config.php');
	$table = F_escape_sql($table);
	$field_id_name = F_escape_sql($field_id_name);
	$value_id = intval($value_id);
	$field_user_id = F_escape_sql($field_user_id);
	$user_id = intval($_SESSION['session_user_id']);
	// check for administrator
	if ($_SESSION['session_user_level'] >= K_AUTH_ADMINISTRATOR) {
		return true;
	}
	// check for original author
	if (F_count_rows($table.' WHERE '.$field_id_name.'='.$value_id.' AND '.$field_user_id.'='.$user_id.' LIMIT 1') > 0) {
		return true;
	}
	// check for author's groups
	// get author ID
	$author_id = 0;
	$sql = 'SELECT '.$field_user_id.' FROM '.$table.' WHERE '.$field_id_name.'='.$value_id.' LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$author_id = $m[0];
		}
	} else {
		F_display_db_error();
	}
	if (($author_id > 1) 
		AND (F_count_rows(K_TABLE_USERGROUP.' AS ta, '.K_TABLE_USERGROUP.' AS tb
		WHERE ta.usrgrp_group_id=tb.usrgrp_group_id
			AND ta.usrgrp_user_id='.$author_id.'
			AND tb.usrgrp_user_id='.$user_id.'
			LIMIT 1') > 0)) {
		return true;
	}
	return false;
}

/**
 * Returns a comma separated string of ID of the users that belong to the same groups.
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2006-03-11
 * @param int $user_id user ID
 * @return string
 */
function F_getAuthorizedUsers($user_id) {
	global $l,$db;
	require_once('../config/tce_config.php');
	$str = ''; // string to return
	$user_id = intval($user_id);
	$sql = 'SELECT tb.usrgrp_user_id
		FROM '.K_TABLE_USERGROUP.' AS ta, '.K_TABLE_USERGROUP.' AS tb
		WHERE ta.usrgrp_group_id=tb.usrgrp_group_id
			AND ta.usrgrp_user_id='.$user_id.'';
	if($r = F_db_query($sql, $db)) {
		while($m = F_db_fetch_array($r)) {
			$str .= $m[0].',';
		}
	} else {
		F_display_db_error();
	}
	// add the user
	$str .= $user_id;
	return $str;
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
