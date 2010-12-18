<?php
//============================================================+
// File name   : tce_user_change_password.php
// Begin       : 2010-09-17
// Last Update : 2010-09-20
//
// Description : Form to change user password
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
 * @file
 * Form to change user password
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2010-09-17
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_USER_CHANGE_PASSWORD;
$thispage_title = $l['t_user_change_password'];
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../code/tce_page_header.php');

$user_id = intval($_SESSION['session_user_id']);

// process submited data
switch($menu_mode) {

	case 'update':{ // Update user
		if($formstatus = F_check_form_fields()) {
			// check password
			if(!empty($newpassword) OR !empty($newpassword_repeat)) {
				if($newpassword == $newpassword_repeat) {
					$user_password = md5($newpassword);
				} else { //print message and exit
					F_print_error('WARNING', $l['m_different_passwords']);
					$formstatus = FALSE; F_stripslashes_formfields();
					break;
				}
			}
			$sql = 'UPDATE '.K_TABLE_USERS.' SET
				user_password=\''.F_escape_sql($user_password).'\'
				WHERE user_id='.$user_id.' AND user_password=\''.md5($currentpassword).'\'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $l['m_password_updated']);
			}
		}
		break;
	}
	
	default :{
		break;
	}

} //end of switch
?>

<div class="container">

<div class="gsoformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_editor">

<div class="row">
<span class="label">
<label for="currentpassword"><?php echo $l['w_current_password']; ?></label>
</span>
<span class="formw">
<input type="password" name="currentpassword" id="currentpassword" size="20" maxlength="255" title="<?php echo $l['h_password']; ?>" />
</span>
</div>

<div class="row">
<span class="label">
<label for="newpassword"><?php echo $l['w_new_password']; ?></label>
</span>
<span class="formw">
<input type="password" name="newpassword" id="newpassword" size="20" maxlength="255" title="<?php echo $l['h_password']; ?>" /><?php echo ' ('.$l['d_password_lenght'].')'; ?>
<input type="hidden" name="x_newpassword" id="x_newpassword" value="^([a-zA-Z0-9]{8,32})$" />
<input type="hidden" name="xl_newpassword" id="xl_newpassword" value="<?php echo $l['w_password'].' ('.$l['d_password_lenght'].')'; ?>" />
</span>
</div>

<div class="row">
<span class="label">
<label for="newpassword_repeat"><?php echo $l['w_new_password']; ?></label>
</span>
<span class="formw">
<input type="password" name="newpassword_repeat" id="newpassword_repeat" size="20" maxlength="255" title="<?php echo $l['h_password']; ?>" />
</span>
</div>

<div class="row">
<?php
F_submit_button('update', $l['w_update'], $l['h_update']);
?>
<!-- comma separated list of required fields -->
<input type="hidden" name="ff_required" id="ff_required" value="currentpassword,newpassword,newpassword_repeat" />
<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="<?php echo htmlspecialchars($l['w_current_password'].','.$l['w_new_password'].','.$l['w_new_password'], ENT_COMPAT, $l['a_meta_charset']); ?>" />
</div>

</form>
</div>

<?php
echo '<div class="pagehelp">'.$l['hp_user_change_password'].'</div>';
echo '</div>';

require_once(dirname(__FILE__).'/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
