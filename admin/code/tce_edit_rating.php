<?php
//============================================================+
// File name   : tce_edit_rating.php
// Begin       : 2004-06-09
// Last Update : 2009-12-15
//
// Description : Editor to manually rate free text answers.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Display form to manually rate free-text questions.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2004-06-09
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_RATING;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_rating_editor'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../code/tce_functions_auth_sql.php');

if (isset($selectcategory)) {
	$changecategory = 1;
}
if (isset($testlog_id)) {
	$testlog_id = intval($testlog_id);
}
if (!isset($testlog_comment)) {
	$testlog_comment = '';
}
if (isset($_REQUEST['test_id']) AND ($_REQUEST['test_id'] > 0)) {
	$test_id = intval($_REQUEST['test_id']);
	// check user's authorization
	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		exit;
	}
}

switch($menu_mode) {
	case 'update': { // Update
		if($formstatus = F_check_form_fields()) {
			if (isset($testlog_score) AND isset($max_score)) {
				// score cannot be greater than max_score
				$testlog_score = floatval($testlog_score);
				$max_score = floatval($max_score);
				if ($testlog_score > $max_score) {
					F_print_error('WARNING', $l['m_score_higher_than_max']);
					break;
				}
				$sql = 'UPDATE '.K_TABLE_TESTS_LOGS.' SET
					testlog_score='.$testlog_score.',
					testlog_comment=\''.F_escape_sql($testlog_comment).'\'
					WHERE testlog_id='.$testlog_id.'';
				if(!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				} else {
					F_print_error('MESSAGE', $l['m_updated']);
					$testlog_score = '';
					$testlog_id = '';
					$testlog_comment = '';
				}
			}
		}
		break;
	}
	default: {
		break;
	}
} //end of switch

// --- Initialize variables

// flag to display/hide user info
if (!isset($display_user_info)) {
	$display_user_info = 0;
}
// flag to select only unrated answers
if (!isset($display_all)) {
	$display_all = 0;
}

$sqlfilter = '';
if (empty($display_all)) {
	$sqlfilter = ' AND testlog_score IS NULL';
}

// set ordering mode
if (!isset($sqlordermode)) {
	$sqlordermode = 0;
}
switch ($sqlordermode) {
	case 2: {
		// ordered by test and question creation time
		$sqlorder = 'ORDER BY testuser_test_id, testlog_id';
		break;
	}
	case 1: {
		// ordered by test and question
		$sqlorder = 'ORDER BY testuser_test_id, testlog_question_id, testlog_testuser_id';
		break;
	}
	default:
	case 0: {
		// ordered by test and users
		$sqlorder = 'ORDER BY testuser_test_id, testlog_testuser_id, testlog_id';
		break;
	}
}

if(!isset($test_id) OR empty($test_id)) {
	// select one executed test
	$sql = F_select_executed_tests_sql().' LIMIT 1';
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$test_id = $m['test_id'];
		} else {
			$test_id = 0;
		}
	} else {
		F_display_db_error();
	}
}

if ((isset($changecategory) AND ($changecategory > 0)) OR (!isset($testlog_id)) OR empty($testlog_id)) {
	$sql = 'SELECT test_id, test_score_right, test_score_wrong, test_score_unanswered, testlog_id, testlog_score, testlog_answer_text, testlog_comment, question_description, question_difficulty, question_explanation
		FROM '.K_TABLE_TESTS.', '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_QUESTIONS.'
		WHERE testuser_test_id=test_id
			AND testlog_testuser_id=testuser_id
			AND testlog_question_id=question_id
			AND testuser_test_id='.$test_id.'
			AND testuser_status>0
			AND question_type=3
			'.$sqlfilter.'
		'.$sqlorder.'
		LIMIT 1';
} else {
	$sql = 'SELECT test_id, test_score_right, test_score_wrong, test_score_unanswered, testlog_id, testlog_score, testlog_answer_text, testlog_comment, question_description, question_difficulty, question_explanation
		FROM '.K_TABLE_TESTS.', '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_QUESTIONS.'
		WHERE testuser_test_id=test_id
			AND testlog_testuser_id=testuser_id
			AND testlog_question_id=question_id
			AND testlog_id='.$testlog_id.'
		LIMIT 1';
}

if($sql) {
	if($r = F_db_query($sql, $db)) {
		if($m = F_db_fetch_array($r)) {
			$testlog_id = $m['testlog_id'];
			$test_id = $m['test_id'];
			$testlog_score = $m['testlog_score'];
			$testlog_comment = $m['testlog_comment'];
			$test_score_right = round(($m['test_score_right'] * $m['question_difficulty']), 3);
			$test_score_wrong = round(($m['test_score_wrong'] * $m['question_difficulty']), 3);
			$test_score_unanswered = round(($m['test_score_unanswered'] * $m['question_difficulty']), 3);
			$question = F_decode_tcecode($m['question_description']);
			$explanation =  F_decode_tcecode($m['question_explanation']);
			$answer = F_decode_tcecode($m['testlog_answer_text']);
		} else {
			$testlog_id = '';
			$testlog_score = '';
			$test_score_right = 1;
			$test_score_wrong = 0;
			$test_score_unanswered = 0;
			$question = '';
			$explanation = '';
			$answer = '';
			$testlog_comment = '';
		}
	} else {
		F_display_db_error();
	}
}

?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_ratingeditor">

<div class="row">
<span class="label">
<label for="test_id"><?php echo $l['w_test']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="changecategory" id="changecategory" value="" />
<select name="test_id" id="test_id" size="0" onchange="document.getElementById('form_ratingeditor').changecategory.value=1;document.getElementById('form_ratingeditor').submit()" title="<?php echo $l['h_test']; ?>">
<?php
$sql = F_select_executed_tests_sql();
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['test_id'].'"';
		if($m['test_id'] == $test_id) {
			echo ' selected="selected"';
		}
		echo '>'.substr($m['test_begin_time'], 0, 10).' : '.htmlspecialchars($m['test_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
		$countitem++;
	}
	if ($countitem == 1) {
		echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
	}
} else {
	F_display_db_error();
}
?>
</select>
</span>
</div>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectcategory" id="selectcategory" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row">
<span class="label">
<label for="testlog_id"><?php echo $l['w_answer']; ?></label>
</span>
<span class="formw">
<select name="testlog_id" id="testlog_id" size="0" onchange="document.getElementById('form_ratingeditor').submit()" title="<?php echo $l['h_select_answer']; ?>">
<?php
$sql = 'SELECT testlog_id, testlog_score, user_lastname, user_firstname, user_name, question_description
	FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_USERS.', '.K_TABLE_QUESTIONS.'
	WHERE testlog_testuser_id=testuser_id
	AND testuser_user_id=user_id
	AND testlog_question_id=question_id
	AND testuser_test_id='.$test_id.'
	AND testuser_status>0
	AND question_type=3
	'.$sqlfilter.'
	'.$sqlorder.'';
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['testlog_id'].'"';
		if($m['testlog_id'] == $testlog_id) {
			echo ' selected="selected"';
		}
		echo '>';
		if (!empty($m['testlog_score'])) {
			echo '+';
		} else {
			echo '-';
		}
		echo ' '.$m['testlog_id'].'';
		if ($display_user_info) {
			echo ' :: '.htmlspecialchars($m['user_lastname'].' '.$m['user_firstname'].' - '.$m['user_name'].'', ENT_NOQUOTES, $l['a_meta_charset']).'';
		}
		echo '</option>'.K_NEWLINE;
		$countitem++;
	}
	if ($countitem == 1) {
		echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
	}
} else {
	F_display_db_error();
}
?>
</select>
</span>
</div>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectrecord" id="selectrecord" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row">
<span class="label">
<label for="sqlordermode"><?php echo $l['w_order']; ?></label>
</span>
<span class="formw">
<select name="sqlordermode" id="sqlordermode" size="0" onchange="document.getElementById('form_ratingeditor').submit()" title="<?php echo $l['w_order']; ?>">
<?php
echo '<option value="0"';
if($sqlordermode == 0) {
	echo ' selected="selected"';
}
echo '>'.$l['w_user'].'</option>'.K_NEWLINE;
echo '<option value="1"';
if($sqlordermode == 1) {
	echo ' selected="selected"';
}
echo '>'.$l['w_question'].'</option>'.K_NEWLINE;
echo '<option value="2"';
if($sqlordermode == 2) {
	echo ' selected="selected"';
}
echo '>'.$l['w_time'].'</option>'.K_NEWLINE;
?>
</select>
</span>
</div>

<noscript>
<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectmode" id="selectmode" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row">
<span class="label">
<label for="display_user_info"><?php echo $l['w_display_user_info']; ?></label>
</span>
<span class="formw">
<?php
echo '<input type="checkbox" name="display_user_info" id="display_user_info" value="1"';
if($display_user_info) {echo ' checked="checked"';}
echo ' onclick="document.getElementById(\'form_ratingeditor\').submit();" title="'.$l['h_display_user_info'].'" />';
?>
</span>
</div>

<div class="row">
<span class="label">
<label for="display_all"><?php echo $l['w_display_all']; ?></label>
</span>
<span class="formw">
<?php
echo '<input type="checkbox" name="display_all" id="display_all" value="1"';
if($display_all) {echo ' checked="checked"';}
echo ' onclick="document.getElementById(\'form_ratingeditor\').submit();" title="'.$l['h_display_all'].'" />';
?>
</span>
</div>

<div class="row"><hr /></div>

<div class="row">
<span class="label">
<span title="<?php echo $l['h_question_description']; ?>"><?php echo $l['w_question']; ?></span>
</span>
<span class="formw">
<?php echo $question; ?>&nbsp;
</span>
</div>

<?php
if (K_ENABLE_QUESTION_EXPLANATION AND !empty($explanation)) {
	echo '<div class="row">'.K_NEWLINE;
	echo '<span class="label">'.K_NEWLINE;
	echo '<span title="'.$l['w_explanation'].'">'.$l['w_explanation'].'</span>'.K_NEWLINE;
	echo '</span>'.K_NEWLINE;
	echo '<span class="formw">'.K_NEWLINE;
	echo $explanation.'&nbsp;'.K_NEWLINE;
	echo '</span>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
}
?>

<div class="row">
<span class="label">
<span title="<?php echo $l['h_answer']; ?>"><?php echo $l['w_answer']; ?></span>
</span>
<span class="formw">
<?php echo $answer; ?>&nbsp;<br />&nbsp;
</span>
</div>

<div class="row">
<span class="label">
<label for="testlog_score"><?php echo $l['w_score']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="x_testlog_score" id="x_testlog_score" value="^([0-9\+\-]*)([\.]?)([0-9]*)$" />
<input type="hidden" name="xl_testlog_score" id="xl_testlog_score" value="<?php echo $l['w_score']; ?>" />
<input type="text" name="testlog_score" id="testlog_score" value="<?php echo $testlog_score; ?>" size="10" maxlength="20" title="<?php echo $l['h_score']; ?>" />
</span>
</div>

<div class="row">
<span class="label">
&nbsp;
</span>
<span class="formw">
<input type="hidden" name="max_score" id="max_score" value="<?php echo $test_score_right; ?>" />
<input type="radio" name="default_score" id="default_score_correct" value="0" onclick="document.getElementById('form_ratingeditor').testlog_score.value='<?php echo $test_score_right; ?>'" title="<?php echo $l['h_score_right']; ?>" /><label for="default_score_correct"><?php echo $l['w_score_right']." [".$test_score_right."]"; ?></label>
</span>
</div>

<div class="row">
<span class="label">
&nbsp;
</span>
<span class="formw">
<input type="radio" name="default_score" id="default_score_wrong" value="0" onclick="document.getElementById('form_ratingeditor').testlog_score.value='<?php echo $test_score_wrong; ?>'" title="<?php echo $l['h_score_wrong']; ?>" /><label for="default_score_wrong"><?php echo $l['w_score_wrong']." [".$test_score_wrong."]"; ?></label>
</span>
</div>

<div class="row">
<span class="label">
&nbsp;
</span>
<span class="formw">
<input type="radio" name="default_score" id="default_score_unanswered" value="0" onclick="document.getElementById('form_ratingeditor').testlog_score.value='<?php echo $test_score_unanswered; ?>'" title="<?php echo $l['h_score_unanswered']; ?>" /><label for="default_score_unanswered"><?php echo $l['w_score_unanswered']." [".$test_score_unanswered."]"; ?></label>
</span>
</div>

<div class="row">
<br />
<span class="label">
<label for="testlog_comment"><?php echo $l['w_comment']; ?></label>
</span>
<span class="formw">
<textarea cols="50" rows="5" name="testlog_comment" id="testlog_comment" style="color:#FF0000" title="<?php echo $l['w_comment']; ?>"><?php echo htmlspecialchars($testlog_comment, ENT_NOQUOTES, $l['a_meta_charset']); ?></textarea>
</span>
</div>

<div class="row">
<?php
// show buttons by case
if (isset($testlog_id) AND ($testlog_id > 0)) {
	F_submit_button("update", $l['w_update'], $l['h_update']);
}
?>
<!-- comma separated list of required fields -->
<input type="hidden" name="ff_required" id="ff_required" value="testlog_score" />
<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="<?php echo $l['w_score']; ?>" />

</div>

</form>

</div>
<?php

echo '<div class="pagehelp">'.$l['hp_edit_rating'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
?>
