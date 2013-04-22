<?php
//============================================================+
// File name   : tce_test_execute.php
// Begin       : 2004-05-29
// Last Update : 2012-12-04
//
// Description : execute a specific test
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
//    Copyright (C) 2004-2012 Nicola Asuni - Tecnick.com LTD
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
 * Execute a specific test.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2004-05-29
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_PUBLIC_TEST_EXECUTE;
$thispage_title = $l['t_test_execute'];
$thispage_description = $l['hp_test_execute'];
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_test.php');

$formname = 'testform';

$test_id = 0;
$testlog_id = 0;
$answer_id = 0;
$answer_text = '';
$test_comment = '';

if (isset($_REQUEST['testid']) AND ($_REQUEST['testid'] > 0)) {
	$test_id = intval($_REQUEST['testid']);
	if (isset($_REQUEST['repeat']) AND ($_REQUEST['repeat'] == 1)) {
		// mark previous test attempts as repeated
		F_repeatTest($test_id);
	}
	if (isset($_REQUEST['testlogid']) AND ($_REQUEST['testlogid'] > 0)) {
		$testlog_id = intval($_REQUEST['testlogid']);
	}
	if (isset($_REQUEST['answerid']) AND ($_REQUEST['answerid'] > 0)) {
		$answer_id = $_REQUEST['answerid'];
	}
	if (isset($_REQUEST['answertext']) AND (!empty($_REQUEST['answertext']))) {
		$answer_text = $_REQUEST['answertext'];
	}
	if (isset($_REQUEST['reaction_time']) AND ($_REQUEST['reaction_time'] > 0)) {
		$reaction_time = intval($_REQUEST['reaction_time']);
	} else {
		$reaction_time = 0;
	}
	// check for test password
	$tph = F_getTestPassword($test_id);
	if (!empty($tph) AND ($_SESSION['session_test_login'] != getPasswordHash($tph.$test_id.$_SESSION['session_user_id'].$_SESSION['session_user_ip']))) {
		// display login page
		require_once('../code/tce_page_header.php');
		echo F_testLoginForm($_SERVER['SCRIPT_NAME'], 'form_test_login', 'post', 'multipart/form-data', $test_id);
		require_once('../code/tce_page_footer.php');
		exit(); //break page here
	}

	if (F_executeTest($test_id)) {

		if (isset($_REQUEST['forceterminate']) AND (!empty($_REQUEST['forceterminate']))) {
			if ($_REQUEST['forceterminate'] == 'lasttimedquestion') {
				// update last question
				F_updateQuestionLog($test_id, $testlog_id, $answer_id, $answer_text, $reaction_time);
			}
			// terminate the test (lock the test to status=4)
			F_terminateUserTest($test_id);
			// redirect the user to the index page
			header('Location: index.php');
			echo '<'.'?xml version="1.0" encoding="'.$l['a_meta_charset'].'"?'.'>'.K_NEWLINE;
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.K_NEWLINE;
			echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$l['a_meta_language'].'" lang="'.$l['a_meta_language'].'" dir="'.$l['a_meta_dir'].'">'.K_NEWLINE;
			echo '<head>'.K_NEWLINE;
			echo '<title>REDIRECT</title>'.K_NEWLINE;
			echo '<meta http-equiv="refresh" content="0;url=index.php" />'.K_NEWLINE; //reload page
			echo '</head>'.K_NEWLINE;
			echo '<body>'.K_NEWLINE;
			echo '<a href="index.php">INDEX...</a>'.K_NEWLINE;
			echo '</body>'.K_NEWLINE;
			echo '</html>'.K_NEWLINE;
			exit;
		}

		// the user is authorized to execute the selected test
		$thispage_title .= ': '.F_getTestName($test_id);

		require_once('../code/tce_page_header.php');
		echo '<div class="container">'.K_NEWLINE;

		echo '<span class="infolink">'.F_testInfoLink($test_id, $l['w_info']).'<br /><br /></span>'.K_NEWLINE;

		if (!isset($_REQUEST['terminationform'])) {
			if (F_isRightTestlogUser($test_id, $testlog_id)) {
				// the form has been submitted, update testlogid data
				F_updateQuestionLog($test_id, $testlog_id, $answer_id, $answer_text, $reaction_time);

				// update user's test comment
				if (isset($_REQUEST['testcomment']) AND (!empty($_REQUEST['testcomment']))) {
					$test_comment = $_REQUEST['testcomment'];
					F_updateTestComment($test_id, $test_comment);
				}

				if ((isset($_REQUEST['nextquestion']) OR (isset($_REQUEST['autonext']) AND ($_REQUEST['autonext'] == 1))) AND ($_REQUEST['nextquestionid'] > 0)) {
					// go to next question
					$testlog_id = 0 + intval($_REQUEST['nextquestionid']);
				} elseif (isset($_REQUEST['prevquestion']) AND ($_REQUEST['prevquestionid'] > 0)) {
					// go to previous question
					$testlog_id = intval($_REQUEST['prevquestionid']);
				} else {
					// go to selected question
					while(list($key,$value) = each($_POST)) {
						if (preg_match('/jumpquestion_([0-9]+)/', $key, $matches) > 0) {
							$testlog_id = intval($matches[1]);
							break;
						}
					}
				}
			}
		}
		// confirmation form to terminate the test
		if (isset($_REQUEST['terminatetest']) AND (!empty($_REQUEST['terminatetest']))) {
			// check if some questions were omitted (undisplayed or unanswered).
			$num_omitted_questions = F_getNumOmittedQuestions($test_id);
			$omitted_msg = '';
			if ($num_omitted_questions > 0) {
				$omitted_msg = '<br /><span style="color:#990000;font-size:120%;">[ '.$l['h_questions_unanswered'].': '.$num_omitted_questions.' ]</span><br />';
			}
			F_print_error('WARNING', $omitted_msg.''.$l['m_confirm_test_termination']);
			?>
			<div class="confirmbox">
			<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_test_terminate">
			<div>
			<input type="hidden" name="testid" id="testid" value="<?php echo $test_id; ?>" />
			<input type="hidden" name="testlogid" id="testlogid" value="<?php echo $testlog_id; ?>" />
			<input type="hidden" name="terminationform" id="terminationform" value="1" />
			<input type="hidden" name="display_time" id="display_time" value="" />
			<input type="hidden" name="reaction_time" id="reaction_time" value="" />
			<?php
			F_submit_button('forceterminate', $l['w_terminate'], $l['w_terminate_exam']);
			F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
			?>
			</div>
			</form>
			</div>
			<?php
		} else {
			echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="'.$formname.'"';
			echo ' onsubmit="var submittime=new Date();document.getElementById(\'reaction_time\').value=submittime.getTime()-document.getElementById(\'display_time\').value;"';
			echo '>'.K_NEWLINE;
			echo '<div>'.K_NEWLINE;

			// display questions + navigation menu
			echo F_questionForm($test_id, $testlog_id, $formname);

			// the $finish variable is used to check if the form has been automatically submitted
			// at the end of the time.
			if (isset($_REQUEST['finish']) AND ($_REQUEST['finish'] > 0)) {
				$finish = 1;
			} else {
				$finish = 0;
			}
			echo '<input type="hidden" name="finish" id="finish" value="'.$finish.'" />'.K_NEWLINE;
			echo '<input type="hidden" name="display_time" id="display_time" value="" />'.K_NEWLINE;
			echo '<input type="hidden" name="reaction_time" id="reaction_time" value="" />'.K_NEWLINE;

			// textarea field for user's comment
			echo '<span class="testcomment">'.F_testComment($test_id).'</span>'.K_NEWLINE;

			// test termination button
			F_submit_button('terminatetest', $l['w_terminate_exam'], $l['w_terminate_exam']);

			echo K_NEWLINE;
			echo '</div>'.K_NEWLINE;
			echo '</form>'.K_NEWLINE;
		}

		// start the countdown if disabled
		if (isset($examtime)) {
			if (isset($timeout_logout) AND ($timeout_logout)) {
				$timeout_logout = 'true';
			} else {
				$timeout_logout = 'false';
			}
			echo '<script type="text/javascript">'.K_NEWLINE;
			echo '//<![CDATA['.K_NEWLINE;
			echo 'if(!enable_countdown) {'.K_NEWLINE;
			echo '	FJ_start_timer(\'true\', '.(time() - $examtime).', \''.addslashes($l['m_exam_end_time']).'\', '.$timeout_logout.');'.K_NEWLINE;
			echo '}'.K_NEWLINE;
			echo 'var loadtime=new Date();'.K_NEWLINE;
			echo 'document.getElementById(\'display_time\').value=loadtime.getTime();'.K_NEWLINE;
			echo '//]]>'.K_NEWLINE;
			echo '</script>'.K_NEWLINE;
		}
	} else {
		// redirect the user to the index page
		header('Location: index.php');
		echo '<'.'?xml version="1.0" encoding="'.$l['a_meta_charset'].'"?'.'>'.K_NEWLINE;
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.K_NEWLINE;
		echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$l['a_meta_language'].'" lang="'.$l['a_meta_language'].'" dir="'.$l['a_meta_dir'].'">'.K_NEWLINE;
		echo '<head>'.K_NEWLINE;
		echo '<title>REDIRECT</title>'.K_NEWLINE;
		echo '<meta http-equiv="refresh" content="0;url=index.php" />'.K_NEWLINE; //reload page
		echo '</head>'.K_NEWLINE;
		echo '<body>'.K_NEWLINE;
		echo '<a href="index.php">INDEX...</a>'.K_NEWLINE;
		echo '</body>'.K_NEWLINE;
		echo '</html>'.K_NEWLINE;
		exit;
	}
} else {
	require_once('../code/tce_page_header.php');
	echo '<div class="container">'.K_NEWLINE;
}

echo '<div class="pagehelp">'.$l['hp_test_execute'].'</div>'.K_NEWLINE;

echo '</div>'.K_NEWLINE; // container

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
