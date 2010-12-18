<?php
//============================================================+
// File name   : tce_user_verification.php
// Begin       : 2008-03-31
// Last Update : 2009-09-30
//
// Description : User verification.
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
 * @file
 * User verification.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2008-03-30
 */

/**
 */

require_once('../config/tce_config.php');

require_once('../../shared/config/tce_user_registration.php');
if (!K_USRREG_ENABLED) {
	// user registration is disabled, redirect to main page
	header('Location: '.K_PATH_HOST.K_PATH_TCEXAM);
	exit;
}

$email = $_REQUEST['a'];
$verifycode = $_REQUEST['b'];
$userid = intval($_REQUEST['c']);

$pagelevel = 0;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_user_registration'];
$thispage_description = '';
require_once('../code/tce_page_header.php');

$sql = 'SELECT *
	FROM '.K_TABLE_USERS.'
	WHERE (user_verifycode=\''.F_escape_sql($verifycode).'\'
		AND user_id=\''.$userid.'\'
		AND user_email=\''.F_escape_sql($email).'\')
		LIMIT 1';
if($r = F_db_query($sql, $db)) {
	if($m = F_db_fetch_array($r)) {
		// update user level
		$sqlu = 'UPDATE '.K_TABLE_USERS.' SET
				user_level=\'1\',
				user_verifycode=NULL
				WHERE user_id='.$userid.'';
			if(!$ru = F_db_query($sqlu, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $l['m_user_registration_ok']);
				echo K_NEWLINE;
				echo '<div class="container">'.K_NEWLINE;
				echo '<strong><a href="index.php" title="'.$l['h_index'].'">'.$l['h_index'].' &gt;</a></strong>'.K_NEWLINE;
				echo '</div>'.K_NEWLINE;
				require_once('../code/tce_page_footer.php');
				exit;
			}
	}
} else {
	F_display_db_error(false);
}

F_print_error('ERROR', 'USER VERIFICATION ERROR');
echo K_NEWLINE;
echo '<div class="container">'.K_NEWLINE;
echo '<strong><a href="index.php" title="'.$l['h_index'].'">'.$l['h_index'].' &gt;</a></strong>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
