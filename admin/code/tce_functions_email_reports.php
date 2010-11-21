<?php
//============================================================+
// File name   : tce_functions_email_reports.php
// Begin       : 2005-02-24
// Last Update : 2010-11-21
//
// Description : Sends email test reports to users.
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
 * Functions to send email reports to users.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2005-02-24
 */

/**
 * Sends email test reports to users.
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2005-02-24
 * @param int $test_id TEST ID
 * @param int $user_id USER ID (0 means all users)
 * @param int $group_id GROUP ID (0 means all groups)
 * @param int $mode type of report to send: 0=detailed report; 1=summary report (without questions)
 */
function F_send_report_emails($test_id, $user_id=0, $group_id=0, $mode=0) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_test.php');
	require_once('../../shared/code/tce_functions_test_stats.php');
	require_once('../../shared/code/tce_class_mailer.php');
	require_once('tce_functions_user_select.php');

	$test_id = intval($test_id);
	$user_id = intval($user_id);
	$group_id = intval($group_id);
	$mode = intval($mode);

	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		return;
	}
	if (!F_isAuthorizedEditorForUser($user_id)) {
		return;
	}
	if (!F_isAuthorizedEditorForGroup($group_id)) {
		return;
	}

	// Instantiate C_mailer class
	$mail = new C_mailer;

	//Load default values
	$mail->language = $l;
	$mail->Priority = $emailcfg['Priority'];
	$mail->ContentType = $emailcfg['ContentType'];
	$mail->Encoding = $emailcfg['Encoding'];
	$mail->WordWrap = $emailcfg['WordWrap'];
	$mail->Mailer = $emailcfg['Mailer'];
	$mail->Sendmail = $emailcfg['Sendmail'];
	$mail->UseMSMailHeaders = $emailcfg['UseMSMailHeaders'];
	$mail->Host = $emailcfg['Host'];
	$mail->Port = $emailcfg['Port'];
	$mail->Helo = $emailcfg['Helo'];
	$mail->SMTPAuth = $emailcfg['SMTPAuth'];
	$mail->SMTPSecure = $emailcfg['SMTPSecure'];
	$mail->Username = $emailcfg['Username'];
	$mail->Password = $emailcfg['Password'];
	$mail->Timeout = $emailcfg['Timeout'];
	$mail->SMTPDebug = $emailcfg['SMTPDebug'];
	$mail->PluginDir = $emailcfg['PluginDir'];
	$mail->Sender = $emailcfg['Sender'];
	$mail->From = $emailcfg['From'];
	$mail->FromName = $emailcfg['FromName'];
	if ($emailcfg['Reply']) {
		$mail->AddReplyTo($emailcfg['Reply'], $emailcfg['ReplyName']);
	}

	$mail->CharSet = $l['a_meta_charset'];
	if(!$mail->CharSet) {
		$mail->CharSet = $emailcfg['CharSet'];
	}

	$mail->Subject = $l['t_result_user'];

	$mail->IsHTML(TRUE); // Set message type to HTML.

	$email_num = 0; // count emails;

	if ($user_id == 0) {
		// for each user on selected test
		$sql = 'SELECT user_id, user_name, user_email, user_firstname, user_lastname, testuser_creation_time
				FROM '.K_TABLE_TEST_USER.', '.K_TABLE_USERS.'
				WHERE testuser_user_id=user_id
					AND testuser_test_id='.$test_id.'
					AND testuser_status>0';
		if ($group_id > 0) {
			$sql .= ' AND testuser_user_id IN (SELECT usrgrp_user_id FROM '.K_TABLE_USERGROUP.' WHERE usrgrp_group_id='.$group_id.')';
		}
	} else {
		// select only one test of one user
		$sql = 'SELECT user_id, user_name, user_email, user_firstname, user_lastname, testuser_creation_time
				FROM '.K_TABLE_TEST_USER.', '.K_TABLE_USERS.'
				WHERE testuser_user_id=user_id
					AND testuser_user_id='.$user_id.'
					AND testuser_test_id='.$test_id.'
					AND testuser_status>0
				LIMIT 1';
	}

	// get test data
	$testdata = F_getTestData($test_id);

	if($r = F_db_query($sql, $db)) {
		while($m = F_db_fetch_array($r)) {
			if (strlen($m['user_email']) > 3) {

				// get user's test stats
				$usrtestdata = F_getUserTestStat($test_id, $m['user_id']);

				// set HTML header
				$mail->Body = $emailcfg['MsgHeader'];

				// compose alternate TEXT message
				$mail->AltBody = ''.$l['t_result_user'].' ['.$m['testuser_creation_time'].']'.K_NEWLINE;

				$mail->AltBody .= $l['w_test'].': '.$testdata['test_name'].K_NEWLINE;

				$passmsg = '';
				if ($testdata['test_score_threshold'] > 0) {
					$mail->AltBody .= $l['w_test_score_threshold'].': '.$testdata['test_score_threshold'];
					if ($usrtestdata['score'] >= $testdata['test_score_threshold']) {
						$passmsg = ' - '.$l['w_passed'];
					} else {
						$passmsg = ' - '.$l['w_not_passed'];
					}
					$mail->AltBody .= K_NEWLINE;
				}

				$mail->AltBody .= $l['w_score'].': '.$usrtestdata['score'].' ('.round(100 * $usrtestdata['score'] / $usrtestdata['max_score']).'%)'.$passmsg.K_NEWLINE;
				$mail->AltBody .= $l['w_answers_right'].': '.$usrtestdata['right'].' ('.round(100 * $usrtestdata['right'] / $usrtestdata['all']).'%)'.K_NEWLINE;
				$mail->AltBody .= $l['w_answers_wrong'].': '.$usrtestdata['wrong'].' ('.round(100 * $usrtestdata['wrong'] / $usrtestdata['all']).'%)'.K_NEWLINE;
				$mail->AltBody .= $l['w_questions_unanswered'].': '.$usrtestdata['unanswered'].' ('.round(100 * $usrtestdata['unanswered'] / $usrtestdata['all']).'%)'.K_NEWLINE;
				$mail->AltBody .= $l['w_questions_undisplayed'].': '.$usrtestdata['undisplayed'].' ('.round(100 * $usrtestdata['undisplayed'] / $usrtestdata['all']).'%)'.K_NEWLINE;

				if ($mode == 0) {
					// create PDF doc
					$pdf_content = file_get_contents(K_PATH_HOST.K_PATH_TCEXAM.'admin/code/tce_pdf_results.php?mode=3&testid='.$test_id.'&groupid=0&userid='.$m['user_id'].'&email='.md5(date('Y').K_RANDOM_SECURITY.$test_id.$m['user_id']));
					// attach doc
					$doc_name = 'test_'.date('Ymd', strtotime($m['testuser_creation_time'])).'_'.$test_id.'_'.$m['user_id'].'.pdf';
					$mail->AddStringAttachment($pdf_content, $doc_name, $emailcfg['AttachmentsEncoding'], 'application/octet-stream');
					$mail->AltBody .= K_NEWLINE.$l['w_attachment'].': '.$doc_name.K_NEWLINE;
				}

				// convert alternate text to HTML
				$mail->Body .= str_replace(K_NEWLINE, '<br />'.K_NEWLINE, $mail->AltBody);

				// add HTML footer
				$mail->Body .= $emailcfg['MsgFooter'];

				//--- Elaborate user Templates ---
				$mail->Body = str_replace('#CHARSET#', $l['a_meta_charset'], $mail->Body);
				$mail->Body = str_replace('#LANG#', $l['a_meta_language'], $mail->Body);
				$mail->Body = str_replace('#LANGDIR#', $l['a_meta_dir'], $mail->Body);
				$mail->Body = str_replace('#EMAIL#', $m['user_email'], $mail->Body);
				$mail->Body = str_replace('#USERNAME#', htmlspecialchars($m['user_name'], ENT_NOQUOTES, $l['a_meta_charset']), $mail->Body);
				$mail->Body = str_replace('#USERFIRSTNAME#', htmlspecialchars($m['user_firstname'], ENT_NOQUOTES, $l['a_meta_charset']), $mail->Body);
				$mail->Body = str_replace('#USERLASTNAME#', htmlspecialchars($m['user_lastname'], ENT_NOQUOTES, $l['a_meta_charset']), $mail->Body);

				// add a "To" address
				$mail->AddAddress($m['user_email'], $m['user_name']);

				$email_num++;
				$progresslog = ''.$email_num.'. '.$m['user_email'].' ['.$m['user_name'].']'; //output user data

				if(!$mail->Send()) { //send email to user
	    			$progresslog .= ' ['.$l['t_error'].']'; //display error message
				}

				$mail->ClearAddresses(); // Clear all addresses for next loop
				$mail->ClearAttachments(); // Clears all previously set filesystem, string, and binary attachments
			} else {
				$progresslog = '['.$l['t_error'].'] '.$m['user_name'].': '.$l['m_unknown_email'].''; //output user data
			}
			echo ''.$progresslog.'<br />'.K_NEWLINE; //output processed emails
			flush(); // force browser output
		}
	} else {
		F_display_db_error(false);
	}

 	$mail->ClearAddresses(); // Clear all addresses for next loop
	$mail->ClearCustomHeaders(); // Clears all custom headers
	$mail->ClearAllRecipients(); // Clears all recipients assigned in the TO, CC and BCC
 	$mail->ClearAttachments(); // Clears all previously set filesystem, string, and binary attachments
	$mail->ClearReplyTos(); // Clears all recipients assigned in the ReplyTo array

	return;
}

//============================================================+
// END OF FILE
//============================================================+
