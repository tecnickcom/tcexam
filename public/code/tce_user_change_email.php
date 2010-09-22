<?php
//============================================================+
// File name   : tce_user_change_email.php
// Begin       : 2010-09-17
// Last Update : 2010-09-20
//
// Description : Form to change user email
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
 * Form to change user email
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2010-09-17
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_USER_CHANGE_EMAIL;
$thispage_title = $l['t_user_change_email'];
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../code/tce_page_header.php');

$user_id = intval($_SESSION['session_user_id']);

// process submited data
switch($menu_mode) {

	case 'update':{ // Update user
		if($formstatus = F_check_form_fields()) {
			// check password
			if(!empty($user_email) OR !empty($user_email_repeat)) {
				if($user_email != $user_email_repeat) {
					//print message and exit
					F_print_error('WARNING', $l['m_different_emails']);
					$formstatus = FALSE;
					F_stripslashes_formfields();
					break;
				}
			}
			mt_srand((double) microtime() * 1000000);
			$user_verifycode = md5(uniqid(mt_rand(), true)); // verification code
			$sql = 'UPDATE '.K_TABLE_USERS.' SET
				user_email=\''.F_escape_sql($user_email).'\',
				user_level=\'0\',
				user_verifycode=\''.$user_verifycode.'\'
				WHERE user_id='.$user_id.' AND user_password=\''.md5($currentpassword).'\'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $l['m_email_updated']);
				// require email confirmation
				require_once('../../shared/code/tce_functions_user_registration.php');
				F_send_user_reg_email($user_id, $user_email, $user_verifycode);
				F_print_error('MESSAGE', $user_email.': '.$l['m_user_verification_sent']);
				echo '<div class="container">'.K_NEWLINE;
				echo '<strong><a href="index.php" title="'.$l['h_index'].'">'.$l['h_index'].' &gt;</a></strong>'.K_NEWLINE;
				echo '</div>'.K_NEWLINE;
				require_once('tce_page_footer.php');
				exit;
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
<label for="user_email"><?php echo $l['w_new_email']; ?></label>
</span>
<span class="formw">
<input type="text" name="user_email" id="user_email" value="" size="20" maxlength="255" title="<?php echo $l['h_email']; ?>" />
<input type="hidden" name="x_user_email" id="x_user_email" value="^([a-zA-Z0-9_\.\-]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$" />
<input type="hidden" name="xl_user_email" id="xl_user_email" value="<?php echo $l['w_email']; ?>" />
</span>
</div>

<div class="row">
<span class="label">
<label for="user_email_repeat"><?php echo $l['w_new_email']; ?></label>
</span>
<span class="formw">
<input type="text" name="user_email_repeat" id="user_email_repeat" value="" size="20" maxlength="255" title="<?php echo $l['h_email']; ?>" />
</span>
</div>

<div class="row">
<span class="label">
<label for="currentpassword"><?php echo $l['w_password']; ?></label>
</span>
<span class="formw">
<input type="password" name="currentpassword" id="currentpassword" size="20" maxlength="255" title="<?php echo $l['h_password']; ?>" />
</span>
</div>

<div class="row">
<?php
F_submit_button('update', $l['w_update'], $l['h_update']);
?>
<!-- comma separated list of required fields -->
<input type="hidden" name="ff_required" id="ff_required" value="user_email,user_email_repeat" />
<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="<?php echo htmlspecialchars($l['w_email'].','.$l['w_email'], ENT_COMPAT, $l['a_meta_charset']); ?>" />
</div>

</form>
</div>

<?php
echo '<div class="pagehelp">'.$l['hp_user_change_email'].'</div>';
echo '</div>';

require_once(dirname(__FILE__).'/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
