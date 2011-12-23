<?php
//============================================================+
// File name   : tce_show_result_questions.php
// Begin       : 2004-06-10
// Last Update : 2011-07-11
//
// Description : Display questions statistics for the selected
//               test.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               Manor Coach House, Church Hill
//               Aldershot, Hants, GU12 4RQ
//               UK
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2011 Nicola Asuni - Tecnick.com LTD
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
 * Display questions statistics for the selected test.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-06-10
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_result_questions'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('../code/tce_functions_statistics.php');
require_once('../code/tce_functions_auth_sql.php');

$filter = '';

if ($l['a_meta_dir'] == 'rtl') {
	$txtdir = 'right';
} else {
	$txtdir = 'left';
}
// --- Initialize variables

if (isset($_REQUEST['test_id']) AND ($_REQUEST['test_id'] > 0)) {
	$test_id = intval($_REQUEST['test_id']);
	// check user's authorization
	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		exit;
	}
}

// order fields for SQL query
if (isset($_REQUEST['order_field']) AND !empty($_REQUEST['order_field']) AND (in_array($_REQUEST['order_field'], array('recurrence',  'average_score',  'average_time')))) {
	$order_field = $_REQUEST['order_field'];
} else {
	$order_field = 'recurrence DESC, average_score DESC';
}
if (!isset($_REQUEST['orderdir']) OR empty($_REQUEST['orderdir'])) {
	$orderdir=0;
	$nextorderdir=1;
	$full_order_field = $order_field;
} else {
	$orderdir=1;
	$nextorderdir=0;
	$full_order_field = $order_field.' DESC';
}

if (isset($_REQUEST['test_id']) AND !empty($_REQUEST['test_id'])) {
	$test_id = intval($_REQUEST['test_id']);
	$sql = 'SELECT * FROM '.K_TABLE_TESTS.' WHERE test_id='.$test_id.' LIMIT 1';
} else {
	$test_id = 0;
	$sql = F_select_executed_tests_sql().' LIMIT 1';
}
if ($r = F_db_query($sql, $db)) {
	if ($m = F_db_fetch_array($r)) {
		$test_id = $m['test_id'];
		$filter = '&amp;test_id='.$test_id.'';
	}
} else {
	F_display_db_error();
}

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_resultquestions">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="test_id">'.$l['w_test'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="test_id" id="test_id" size="0" onchange="document.getElementById(\'form_resultquestions\').submit()" title="'.$l['h_test'].'">'.K_NEWLINE;
$sql = F_select_executed_tests_sql();
if ($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['test_id'].'"';
		if ($m['test_id'] == $test_id) {
			echo ' selected="selected"';
		}
		echo '>'.substr($m['test_begin_time'], 0, 10).' '.htmlspecialchars($m['test_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
}
else {
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo '<div class="rowl">'.K_NEWLINE;
echo '<table class="userselect">'.K_NEWLINE;
echo '<tr>'.K_NEWLINE;
echo '<th>#</th>'.K_NEWLINE;
echo '<th>#</th>'.K_NEWLINE;
echo F_select_table_header_element('recurrence', $nextorderdir, $l['h_question_recurrence'], $l['w_recurrence'], $order_field, $filter);
echo F_select_table_header_element('average_score', $nextorderdir, $l['h_score_average'], $l['w_score'], $order_field, $filter);
echo F_select_table_header_element('average_time', $nextorderdir, $l['h_answer_time'], $l['w_answer_time'], $order_field, $filter);
echo '<th title="'.$l['h_answers_right'].'">'.$l['w_answers_right'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_answers_wrong'].'">'.$l['w_answers_wrong'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_unanswered'].'">'.$l['w_questions_unanswered'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_undisplayed'].'">'.$l['w_questions_undisplayed'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_unrated'].'">'.$l['w_questions_unrated'].'</th>'.K_NEWLINE;
echo '</tr>'.K_NEWLINE;

// get test data
$testdata = F_getTestData($test_id);

// get total number of questions for the selected test
$num_questions = F_count_rows(K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER, 'WHERE testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.'');

// output questions stats
$sqlr = 'SELECT
		question_id,
		COUNT(question_id) AS recurrence,
		AVG(testlog_score) AS average_score,
		AVG(testlog_change_time - testlog_display_time) AS average_time,
		min(question_difficulty) AS question_difficulty
	FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_QUESTIONS.'
	WHERE testlog_testuser_id=testuser_id
		AND testlog_question_id=question_id
		AND testuser_test_id='.$test_id.'
	GROUP BY question_id';
if (!empty($full_order_field) AND ($full_order_field != ' DESC')) {
	$sqlr .= ' ORDER BY '.$full_order_field.'';
}
if ($rr = F_db_query($sqlr, $db)) {
	$itemcount = 1;
	while($mr = F_db_fetch_array($rr)) {
		// get the question max score
		$question_max_score = $testdata['test_score_right'] * $mr['question_difficulty'];
		echo '<tr style="font-weight:bold;background-color:#FFFACD;">';
		echo '<td rowspan="2" valign="top" class="questionid"><a href="tce_edit_question.php?question_id='.$mr['question_id'].'" title="'.$l['t_questions_editor'].'"><strong>'.$itemcount.'</strong></a></td>'.K_NEWLINE;
		echo '<td rowspan="2" class="questionid">&nbsp;</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$mr['recurrence'].' '.F_formatPercentage($mr['recurrence'] / $num_questions).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.number_format($mr['average_score'], 3, '.', '').' '.F_formatPercentage($mr['average_score'] / $question_max_score).'</td>'.K_NEWLINE;
		if (stripos($mr['average_time'], ':') !== FALSE) {
			// PostgreSQL returns formatted time, while MySQL returns the number of seconds
			$mr['average_time'] = strtotime($mr['average_time']);
		}
		echo '<td class="numeric">&nbsp;'.date('i:s', $mr['average_time']).'</td>'.K_NEWLINE;
		$qsttestdata = F_getQuestionTestStat($test_id, $mr['question_id']);
		echo '<td class="numeric">'.$qsttestdata['right'].' '.F_formatPercentage($qsttestdata['right'] / $qsttestdata['num']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$qsttestdata['wrong'].' '.F_formatPercentage($qsttestdata['wrong'] / $qsttestdata['num']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$qsttestdata['unanswered'].' '.F_formatPercentage($qsttestdata['unanswered'] / $qsttestdata['num']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$qsttestdata['undisplayed'].' '.F_formatPercentage($qsttestdata['undisplayed'] / $qsttestdata['num']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$qsttestdata['unrated'].' '.F_formatPercentage($qsttestdata['unrated'] / $qsttestdata['num']).'</td>'.K_NEWLINE;
		echo '</tr>'.K_NEWLINE;
		echo '<tr>';
		$question_description = '';
		$sqlrq = 'SELECT question_description FROM '.K_TABLE_QUESTIONS.' WHERE question_id='.$mr['question_id'].'';
		if ($rrq = F_db_query($sqlrq, $db)) {
			if ($mrq = F_db_fetch_array($rrq)) {
				$question_description = F_decode_tcecode($mrq['question_description']);
			}
		} else {
			F_display_db_error();
		}
		echo '<td colspan="8" align="'.$txtdir.'" style="background-color:white;">'.$question_description.'</td>';
		echo '</tr>'.K_NEWLINE;
		$itemcount++;

		// answers statistics

		$sqla = 'SELECT *
			FROM '.K_TABLE_ANSWERS.'
			WHERE answer_question_id='.$mr['question_id'].'
			ORDER BY answer_id';
		if ($ra = F_db_query($sqla, $db)) {
			$answcount = 1;
			while($ma = F_db_fetch_array($ra)) {
				echo '<tr>';
				echo '<td rowspan="2">&nbsp;</td>'.K_NEWLINE;
				echo '<td rowspan="2" valign="top"><a href="tce_edit_answer.php?answer_id='.$ma['answer_id'].'" title="'.$l['t_answers_editor'].'">'.$answcount.'</a></td>'.K_NEWLINE;

				$num_all_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].'');

				$num_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].'');

				$right_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND ((answer_isright=\'0\' AND logansw_selected=0) OR (answer_isright=\'1\' AND logansw_selected=1) OR (answer_position IS NOT NULL AND logansw_position IS NOT NULL AND answer_position=logansw_position))');

				$wrong_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND ((answer_isright=\'0\' AND logansw_selected=1) OR (answer_isright=\'1\' AND logansw_selected=0) OR (answer_position IS NOT NULL AND answer_position!=logansw_position))');

				$unanswered = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER.' WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND logansw_selected=-1');

				$perc = 0;
				if ($num_all_answers > 0 ) {
					$perc = ($num_answers / $num_all_answers);
				}
				echo '<td class="numeric">'.$num_answers.' '.F_formatPercentage($perc).'</td>'.K_NEWLINE;

				echo '<td colspan="2">&nbsp;</td>'.K_NEWLINE;

				$perc = 0;
				if ($num_answers > 0 ) {
					$perc = ($right_answers / $num_answers);
				}
				echo '<td class="numeric">'.$right_answers.' '.F_formatPercentage($perc).'</td>'.K_NEWLINE;

				$perc = 0;
				if ($num_answers > 0 ) {
					$perc = round($wrong_answers / $num_answers);
				}
				echo '<td class="numeric">'.$wrong_answers.' '.F_formatPercentage($perc).'</td>'.K_NEWLINE;

				$perc = 0;
				if ($num_answers > 0 ) {
					$perc = round($unanswered / $num_answers);
				}
				echo '<td class="numeric">'.$unanswered.' '.F_formatPercentage($perc).'</td>'.K_NEWLINE;

				echo '<td colspan="2">&nbsp;</td>'.K_NEWLINE;
				echo '</tr>'.K_NEWLINE;
				echo '<tr>'.K_NEWLINE;
				echo '<td colspan="8" align="'.$txtdir.'">'.F_decode_tcecode($ma['answer_description']).'</td>'.K_NEWLINE;
				echo '</tr>'.K_NEWLINE;
				$answcount++;
			}
		} else {
			F_display_db_error();
		}
	}
} else {
	F_display_db_error();
}
echo '</table>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
// show buttons by case
if (isset($test_id) AND ($test_id > 0)) {
	echo '<a href="'.pdfLink(2, $test_id, 0, '', $order_field, $orderdir).'" class="xmlbutton" title="'.$l['h_pdf'].'">'.$l['w_pdf'].'</a> ';
	echo '<a href="tce_xml_question_stats.php?testid='.$test_id.'&amp;menu_mode=startlongprocess" class="xmlbutton" title="'.$l['h_xml_export'].'">XML</a> ';
}

echo '<input type="hidden" name="order_field" id="order_field" value="'.$order_field.'" />'.K_NEWLINE;
echo '<input type="hidden" name="orderdir" id="orderdir" value="'.$orderdir.'" />'.K_NEWLINE;

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;
echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_result_questions'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
