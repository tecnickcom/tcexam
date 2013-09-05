<?php
//============================================================+
// File name   : tce_email_results.php
// Begin       : 2005-02-24
// Last Update : 2012-12-20
//
// Description : Interface to send test reports to users via
//               email.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
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
 * Interface to send email test reports to users.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2005-02-24
 */

 /**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_RESULTS;
$thispage_title = $l['t_email_result'];
$thispage_description = $l['hp_email_result'];
require_once('../../shared/code/tce_authorization.php');
require_once('tce_functions_user_select.php');

require_once('../code/tce_page_header.php');

echo '<div class="popupcontainer">'.K_NEWLINE;

if (isset($_REQUEST['test_id']) AND ($_REQUEST['test_id'] > 0)) {
	$test_id = intval($_REQUEST['test_id']);
	// check user's authorization
	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		echo '</div>'.K_NEWLINE;
		require_once('../code/tce_page_footer.php');
		exit;
	}
} else {
	$test_id = 0;
}
if (isset($_REQUEST['user_id'])) {
	$user_id = intval($_REQUEST['user_id']);
} else {
	$user_id = 0;
}
if (isset($_REQUEST['testuser_id']) AND ($_REQUEST['testuser_id'] > 0)) {
	$testuser_id = intval($_REQUEST['testuser_id']);
} else {
	$testuser_id = 0;
}
if (isset($_REQUEST['group_id']) AND !empty($_REQUEST['group_id'])) {
	$group_id = intval($_REQUEST['group_id']);
} else {
	$group_id = 0;
}
// filtering options
if (isset($_REQUEST['startdate'])) {
	$startdate = $_REQUEST['startdate'];
	$startdate_time = strtotime($startdate);
	$startdate = date(K_TIMESTAMP_FORMAT, $startdate_time);
} else {
	$startdate = '';
}
if (isset($_REQUEST['enddate'])) {
	$enddate = $_REQUEST['enddate'];
	$enddate_time = strtotime($enddate);
	$enddate = date(K_TIMESTAMP_FORMAT, $enddate_time);
} else {
	$enddate = '';
}
if (isset($_REQUEST['mode']) AND ($_REQUEST['mode'] > 0)) {
	$mode = intval($_REQUEST['mode']);
} else {
	$mode = 0;
}

require_once('tce_functions_email_reports.php');
echo '<div class="pagehelp">'.$l['hp_sending_in_progress'].'</div>'.K_NEWLINE;
flush(); // force browser output
F_send_report_emails($test_id, $user_id, $testuser_id, $group_id, $startdate, $enddate, $mode);
F_print_error('MESSAGE', $l['m_process_completed']);

echo '</div>'.K_NEWLINE;
require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
