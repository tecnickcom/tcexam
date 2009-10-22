<?php
//============================================================+
// File name   : tce_functions_levels.php
// Begin       : 2001-10-18
// Last Update : 2009-10-10
// 
// Description : Functions to display online users' data.
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
 * Functions to display online users' data.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-10-18
 */

/**
 * Display online users form using F_list_online_users function.
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-10-18
 * @param string $wherequery users selection query
 * @param string $order_field order by column name
 * @param string $orderdir oreder direction
 * @param string $firstrow number of first row to display
 * @param string $rowsperpage number of rows per page
 * @return false in case of empty database, true otherwise
 * @uses F_list_online_users
 */
function F_show_online_users($wherequery, $order_field, $orderdir, $firstrow, $rowsperpage) {
	global $l;
	require_once('../config/tce_config.php');
	F_list_online_users($wherequery, $order_field, $orderdir, $firstrow, $rowsperpage);
	return true;
}

/**
 * Display online users.
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-10-18
 * @param string $wherequery users selection query
 * @param string $order_field order by column name
 * @param int $orderdir oreder direction
 * @param int $firstrow number of first row to display
 * @param int $rowsperpage number of rows per page
 * @return false in case of empty database, true otherwise
 */
function F_list_online_users($wherequery, $order_field, $orderdir, $firstrow, $rowsperpage) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_page.php');
	
	//initialize variables
	$order_field = F_escape_sql($order_field);
	$orderdir = intval($orderdir);
	if($orderdir == 0) {
		$nextorderdir = 1;
		$full_order_field = $order_field;
	} else {
		$nextorderdir = 0;
		$full_order_field = $order_field.' DESC';
	}
	
	if(!F_count_rows(K_TABLE_SESSIONS)) { //if the table is void (no items) display message
		echo '<h2>'.$l['m_databasempty'].'</h2>';
		return FALSE;
	}
	
	if (empty($wherequery)) {
		$sql = 'SELECT * FROM '.K_TABLE_SESSIONS.' ORDER BY '.$full_order_field.'';
	} else {
		$sql = 'SELECT * FROM '.K_TABLE_SESSIONS.' '.$wherequery.' ORDER BY '.$full_order_field.'';
	}
	if (K_DATABASE_TYPE == 'ORACLE') {
		$sql = 'SELECT * FROM ('.$sql.') WHERE rownum BETWEEN '.$firstrow.' AND '.($firstrow + $rowsperpage).'';
	} else {
		$sql .= ' LIMIT '.$rowsperpage.' OFFSET '.$firstrow.'';
	}
	
	echo '<div class="container">'.K_NEWLINE;
	echo '<table class="userselect">'.K_NEWLINE;
	echo '<tr>'.K_NEWLINE;
	echo '<th>'.$l['w_user'].'</th>'.K_NEWLINE;
	echo '<th>'.$l['w_level'].'</th>'.K_NEWLINE;
	echo '<th>'.$l['w_ip'].'</th>'.K_NEWLINE;
	echo '</tr>'.K_NEWLINE;
	
	if($r = F_db_query($sql, $db)) {
		while($m = F_db_fetch_array($r)) {
			
			$this_session = F_session_string_to_array($m['cpsession_data']);
			echo '<tr>';
			echo '<td align="left">';
			echo '<a href="tce_edit_user.php?user_id='.$this_session['session_user_id'].'">';
			if ($this_session['session_user_lastname']) {
				echo ''.urldecode($this_session['session_user_lastname']).', ';
			}
			if ($this_session['session_user_firstname']) {
				echo ''.urldecode($this_session['session_user_firstname']).'';
			}
			echo ' ('.$this_session['session_user_name'].')';
			echo '</a>';
			echo '</td>';
			echo '<td>';
			echo $this_session['session_user_level'];
			echo '</td>';
			echo '<td>';
			echo $this_session['session_user_ip'];
			echo '</td>';
			echo '</tr>'.K_NEWLINE;
		}
	} else {
		F_display_db_error();
	}
	echo '</table>'.K_NEWLINE;
	
	// --- ------------------------------------------------------
	// --- page jump
	$sql = 'SELECT count(*) AS total FROM '.K_TABLE_SESSIONS.' '.$wherequery.'';
	if (!empty($order_field)) {$param_array = '&amp;order_field='.urlencode($order_field).'';}
	if (!empty($orderdir)) {$param_array .= '&amp;orderdir='.$orderdir.'';}
	$param_array .= '&amp;submitted=1';
	F_show_page_navigator($_SERVER['SCRIPT_NAME'], $sql, $firstrow, $rowsperpage, $param_array);
	echo '<div class="pagehelp">'.$l['hp_online_users'].'</div>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
	return TRUE;
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
