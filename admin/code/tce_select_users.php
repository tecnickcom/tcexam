<?php
//============================================================+
// File name   : tce_select_users.php
// Begin       : 2001-09-13
// Last Update : 2009-10-07
//
// Description : Display user selection table.
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
 * Display user selection table.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-09-13
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_USERS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_user_select'];

require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_user_select.php');

// set default values
if(!isset($order_field)) {$order_field="user_lastname,user_firstname";}
if(!isset($orderdir)) {$orderdir=0;}
if(!isset($firstrow)) {$firstrow=0;}
if(!isset($rowsperpage)) {$rowsperpage=K_MAX_ROWS_PER_PAGE;}
if(!isset($group_id)) {$group_id=0;}
if(!isset($searchterms)) {$searchterms='';}
?>

<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_userselect">

<div class="row">
<span class="label">
<label for="group_id"><?php echo $l['w_group']; ?></label>
</span>
<span class="formw">
<select name="group_id" id="group_id" size="0" onchange="document.getElementById('form_userselect').submit()">
<?php
echo '<option value="0"';
if($group_id == 0) {
	echo ' selected="selected"';
}
echo '>&nbsp;</option>'.K_NEWLINE;
$sql = 'SELECT *
	FROM '.K_TABLE_GROUPS.'
	ORDER BY group_name';
if($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['group_id'].'"';
		if($m['group_id'] == $group_id) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($m['group_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
}
else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
?>
</select>

<?php
echo '<input type="text" name="searchterms" id="searchterms" value="'.htmlspecialchars($searchterms, ENT_COMPAT, $l['a_meta_charset']).'" size="20" maxlength="255" title="'.$l['w_search'].'" />';
F_submit_button('search', $l['w_search'], $l['w_search']);
echo '</span></div>'.K_NEWLINE;
// build a search query
$wherequery = '';
if (strlen($searchterms) > 0) {
	$wherequery = '';
	$terms = preg_split("/[\s]+/i", $searchterms); // Get all the words into an array
	foreach ($terms as $word) {
		$word = F_escape_sql($word);
		$wherequery .= ' AND ((user_name LIKE \'%'.$word.'%\')';
		$wherequery .= ' OR (user_email LIKE \'%'.$word.'%\')';
		$wherequery .= ' OR (user_firstname LIKE \'%'.$word.'%\')';
		$wherequery .= ' OR (user_lastname LIKE \'%'.$word.'%\')';
		$wherequery .= ' OR (user_regnumber LIKE \'%'.$word.'%\')';
		$wherequery .= ' OR (user_ssn LIKE \'%'.$word.'%\'))';
	}
	$wherequery = '('.substr($wherequery, 5).')';
}
?>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectrecord" id="selectrecord" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row"><hr /></div>

<?php
if(isset($_POST['addgroup'])) {
	$menu_mode = 'addgroup';
} elseif(isset($_POST['delgroup'])) {
	$menu_mode = 'delgroup';
} elseif(isset($_POST['move'])) {
	$menu_mode = 'move';
}
if (isset($menu_mode) AND (!empty($menu_mode))) {
	$istart = 1 + $firstrow;
	$iend = $rowsperpage + $firstrow;
	for ($i = $istart; $i <= $iend; $i++) {
		// for each selected question
		$keyname = 'userid'.$i;
		if (isset($$keyname)) {
			$user_id = $$keyname;
			switch($menu_mode) {
				case 'delete': {
					if (($user_id > 1) AND ($user_id != $_SESSION['session_user_id'])) {
						$sql = 'DELETE FROM '.K_TABLE_USERS.'
							WHERE user_id='.$user_id.'';
						if(!$r = F_db_query($sql, $db)) {
							F_display_db_error();
						}
					}
					break;
				}
				case 'addgroup': {
					if (isset($new_group_id) AND ($new_group_id > 0)) {
						$groups = F_get_user_groups($user_id);
						if (!in_array($new_group_id, $groups)) {
							$sql = 'INSERT INTO '.K_TABLE_USERGROUP.' (
								usrgrp_user_id,
								usrgrp_group_id
								) VALUES (
								\''.$user_id.'\',
								\''.$new_group_id.'\'
								)';
							if(!$r = F_db_query($sql, $db)) {
								F_display_db_error();
							}
						}
					}
					break;
				}
				case 'delgroup': {
					if (isset($new_group_id) AND ($new_group_id > 0)) {
						$sql = 'DELETE FROM '.K_TABLE_USERGROUP.'
							WHERE usrgrp_user_id='.$user_id.'
								AND usrgrp_group_id='.$new_group_id.'';
						if(!$r = F_db_query($sql, $db)) {
							F_display_db_error();
						}
					}
					break;
				}
				case 'move': {
					if (isset($from_group_id) AND ($from_group_id > 0) AND isset($to_group_id) AND ($to_group_id > 0)) {
						$groups = F_get_user_groups($user_id);
						if (!in_array($to_group_id, $groups)) {
							$sql = 'UPDATE '.K_TABLE_USERGROUP.' SET
								usrgrp_group_id='.$to_group_id.'
								WHERE usrgrp_user_id='.$user_id.'
									AND usrgrp_group_id='.$from_group_id.'
								LIMIT 1';
							if(!$r = F_db_query($sql, $db)) {
								F_display_db_error();
							}
						} else {
							$sql = 'DELETE FROM '.K_TABLE_USERGROUP.'
							WHERE usrgrp_user_id='.$user_id.'
								AND usrgrp_group_id='.$from_group_id.'';
							if(!$r = F_db_query($sql, $db)) {
								F_display_db_error();
							}
						}
					}
					break;
				}
			} // end of switch
		}
	}
	F_print_error('MESSAGE', $l['m_updated']);
}

F_select_user($order_field, $orderdir, $firstrow, $rowsperpage, $group_id, $wherequery, $searchterms);

echo '</form>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
