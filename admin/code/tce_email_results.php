<?php
//============================================================+
// File name   : tce_email_results.php
// Begin       : 2005-02-24
// Last Update : 2009-09-30
// 
// Description : Interface to send test reports to users via 
//               email.
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
 * Interface to send email test reports to users.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2005-02-24
 * @uses F_send_report_emails
 */

 /**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_RESULTS;
$thispage_title = $l['t_email_result'];
$thispage_description = $l['hp_email_result'];
require_once('../../shared/code/tce_authorization.php');

require_once('../code/tce_page_header.php');

echo '<div class="popupcontainer">'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_sending_in_progress'].'</div>'.K_NEWLINE;
flush(); // force browser output

if (isset($_REQUEST['testid']) AND ($_REQUEST['testid'] > 0)) {
	$test_id = intval($_REQUEST['testid']);
	
	// check user's authorization
	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		exit;
	}
	
	if (isset($_REQUEST['userid']) AND ($_REQUEST['userid'] > 0)) {
		$user_id = $_REQUEST['userid'];
	} else {
		$user_id = 0; // select all users
	}
	
	if (isset($_REQUEST['groupid']) AND ($_REQUEST['groupid'] > 0)) {
		$group_id = intval($_REQUEST['groupid']);
	} else {
		$group_id = 0;
	}
	
	require_once('tce_functions_email_reports.php');
	F_send_report_emails($test_id, $user_id, $group_id);
}

F_print_error('MESSAGE', $l['m_process_completed']);

echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
