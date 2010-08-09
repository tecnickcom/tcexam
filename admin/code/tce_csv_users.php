<?php
//============================================================+
// File name   : tce_csv_users.php
// Begin       : 2006-03-30
// Last Update : 2009-09-30
//
// Description : Functions to export users using CSV format.
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
 * Display all users in CSV format.
 * (Tab Delimited Text File)
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2006-03-30
 */

/**
 */

// check user's authorization
require_once('../config/tce_config.php');
$pagelevel = K_AUTH_ADMIN_USERS;
require_once('../../shared/code/tce_authorization.php');

// send headers
header('Content-Description: TXT File Transfer');
header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
// force download dialog
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream', false);
header('Content-Type: application/download', false);
header('Content-Type: text/csv', false);
// use the Content-Disposition header to supply a recommended filename
header('Content-Disposition: attachment; filename=tcexam_users_'.date('YmdHis').'.txt;');
header('Content-Transfer-Encoding: binary');

echo F_csv_export_users();

/**
 * Export all users to CSV grouped by users' groups.
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2006-03-30
 * @return CSV data
 */
function F_csv_export_users() {
	global $l, $db;
	require_once('../config/tce_config.php');

	$csv = ''; // CSV data to be returned

	// print column names
	$csv .= 'user_id';
	$csv .= K_TAB.'user_name';
	$csv .= K_TAB.'user_password';
	$csv .= K_TAB.'user_email';
	$csv .= K_TAB.'user_regdate';
	$csv .= K_TAB.'user_ip';
	$csv .= K_TAB.'user_firstname';
	$csv .= K_TAB.'user_lastname';
	$csv .= K_TAB.'user_birthdate';
	$csv .= K_TAB.'user_birthplace';
	$csv .= K_TAB.'user_regnumber';
	$csv .= K_TAB.'user_ssn';
	$csv .= K_TAB.'user_level';
	$csv .= K_TAB.'user_verifycode';
	$csv .= K_TAB.'user_groups';

	$sql = 'SELECT *
		FROM '.K_TABLE_USERS.'
		ORDER BY user_lastname,user_firstname,user_name';
	if($r = F_db_query($sql, $db)) {
		while($m = F_db_fetch_array($r)) {
			$csv .= K_NEWLINE.$m['user_id'];
			$csv .= K_TAB.$m['user_name'];
			$csv .= K_TAB; // password cannot be exported because is encrypted
			$csv .= K_TAB.$m['user_email'];
			$csv .= K_TAB.$m['user_regdate'];
			$csv .= K_TAB.$m['user_ip'];
			$csv .= K_TAB.$m['user_firstname'];
			$csv .= K_TAB.$m['user_lastname'];
			$csv .= K_TAB.substr($m['user_birthdate'],0,10);
			$csv .= K_TAB.$m['user_birthplace'];
			$csv .= K_TAB.$m['user_regnumber'];
			$csv .= K_TAB.$m['user_ssn'];
			$csv .= K_TAB.$m['user_level'];
			$csv .= K_TAB.$m['user_verifycode'];
			$csv .= K_TAB;
			$grp = '';
			// comma separated list of user's groups
			$sqlg = 'SELECT *
				FROM '.K_TABLE_GROUPS.', '.K_TABLE_USERGROUP.'
				WHERE usrgrp_group_id=group_id
					AND usrgrp_user_id='.$m['user_id'].'
				ORDER BY group_name';
			if($rg = F_db_query($sqlg, $db)) {
				while($mg = F_db_fetch_array($rg)) {
					$grp .= $mg['group_name'].',';
				}
			} else {
				F_display_db_error();
			}
			if (!empty($grp)) {
				// add user's groups removing last comma
				$csv .= substr($grp,0,-1);
			}
		}
	} else {
		F_display_db_error();
	}
	return $csv;
}

//============================================================+
// END OF FILE
//============================================================+
