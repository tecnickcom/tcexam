<?php
//============================================================+
// File name   : tce_show_result_questions.php
// Begin       : 2004-06-10
// Last Update : 2009-02-17
// 
// Description : Display questions statistics for the selected
//               test.
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
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//    
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//     
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Display questions statistics for the selected test.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
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
require_once('../code/tce_functions_auth_sql.php');

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

if(!isset($order_field) OR empty($order_field)) {
	$order_field = 'recurrence DESC,average_score DESC';
}
else {
	$order_field = F_escape_sql($order_field);
}
if(!isset($orderdir) OR empty($orderdir)) {
	$orderdir=0; $nextorderdir=1; $full_order_field = $order_field;
}
else {
	$orderdir=1; $nextorderdir=0; $full_order_field = $order_field.' DESC';
}

if($formstatus) {
	if(!isset($test_id) OR empty($test_id)) {
		$sql = F_select_executed_tests_sql().' LIMIT 1';
	} else {
		$sql = 'SELECT *
			FROM '.K_TABLE_TESTS.'
			WHERE test_id='.$test_id.'
			LIMIT 1';
	}
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
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_resultquestions">

<div class="row">
<span class="label">
<label for="test_id"><?php echo $l['w_test']; ?></label>
</span>
<span class="formw">
<select name="test_id" id="test_id" size="0" onchange="document.getElementById('form_resultquestions').submit()" title="<?php echo $l['h_test']; ?>">
<?php
$sql = F_select_executed_tests_sql();
if($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['test_id'].'"';
		if($m['test_id'] == $test_id) {
			echo ' selected="selected"';
		}
		echo '>'.substr($m['test_begin_time'], 0, 10).' '.htmlspecialchars($m['test_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
}
else {
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

<div class="row"><hr /></div>

<div class="rowl">
<table class="userselect">
<tr>
<th>#</th>
<th>#</th>
<?php
echo F_stats_table_header_element($test_id, 'recurrence', $nextorderdir, $l['h_question_recurrence'], $l['w_recurrence'], $order_field);
echo F_stats_table_header_element($test_id, 'average_score', $nextorderdir, $l['h_score_average'], $l['w_score'], $order_field);
echo F_stats_table_header_element($test_id, 'average_time', $nextorderdir, $l['h_answer_time'], $l['w_answer_time'], $order_field);

echo '<th title="'.$l['h_answers_right'].'">'.$l['w_answers_right'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_answers_wrong'].'">'.$l['w_answers_wrong'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_unanswered'].'">'.$l['w_questions_unanswered'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_undisplayed'].'">'.$l['w_questions_undisplayed'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_unrated'].'">'.$l['w_questions_unrated'].'</th>'.K_NEWLINE;
?>
</tr>
<?php
// output questions stats
$sqlr = 'SELECT question_id, question_description, COUNT(question_id) AS recurrence, AVG(testlog_score) AS average_score, AVG(testlog_change_time - testlog_display_time) AS average_time
	FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_QUESTIONS.' 
	WHERE testlog_testuser_id=testuser_id
		AND testlog_question_id=question_id 
		AND testuser_test_id='.$test_id.'
	GROUP BY question_id, question_description 
	ORDER BY '.$full_order_field.'';
if($rr = F_db_query($sqlr, $db)) {
	$itemcount = 1;
	while($mr = F_db_fetch_array($rr)) {
		echo '<tr>';
		echo '<td rowspan="2" valign="top" class="questionid"><a href="tce_edit_question.php?question_id='.$mr['question_id'].'" title="'.$l['t_questions_editor'].'"><strong>'.$itemcount.'</strong></a></td>'.K_NEWLINE;
		echo '<td rowspan="2" class="questionid">&nbsp;</td>'.K_NEWLINE;
		echo '<td>'.$mr['recurrence'].'</td>'.K_NEWLINE;
		echo '<td>'.number_format($mr['average_score'], 3, '.', '').'</td>'.K_NEWLINE;
		if (stripos($mr['average_time'], ':') !== FALSE) {
			// PostgreSQL returns formatted time, while MySQL returns the number of seconds
			$mr['average_time'] = strtotime($mr['average_time']);
		}
		echo '<td>&nbsp;'.date('i:s', $mr['average_time']).'</td>'.K_NEWLINE;
		$qsttestdata = F_getQuestionTestStat($test_id, $mr['question_id']);
		echo '<td>'.$qsttestdata['right'].'</td>'.K_NEWLINE;
		echo '<td>'.$qsttestdata['wrong'].'</td>'.K_NEWLINE;
		echo '<td>'.$qsttestdata['unanswered'].'</td>'.K_NEWLINE;
		echo '<td>'.$qsttestdata['undisplayed'].'</td>'.K_NEWLINE;
		echo '<td>'.$qsttestdata['unrated'].'</td>'.K_NEWLINE;
		echo '</tr>'.K_NEWLINE;
		echo '<tr>';
		echo '<td colspan="8" align="'.$txtdir.'">'.F_decode_tcecode($mr['question_description']).'</td>';
		echo '</tr>'.K_NEWLINE;
		$itemcount++;
		
		// answers statistics
		
		$sqla = 'SELECT *
			FROM '.K_TABLE_ANSWERS.' 
			WHERE answer_question_id='.$mr['question_id'].'
			ORDER BY answer_id';
		if($ra = F_db_query($sqla, $db)) {
			$answcount = 1;
			while($ma = F_db_fetch_array($ra)) {
				echo '<tr>';
				echo '<td rowspan="2">&nbsp;</td>'.K_NEWLINE;
				echo '<td rowspan="2" valign="top"><a href="tce_edit_answer.php?answer_id='.$ma['answer_id'].'" title="'.$l['t_answers_editor'].'">'.$answcount.'</a></td>'.K_NEWLINE;
				
				$right_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER, 'WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND ((answer_isright=\'0\' AND logansw_selected=0) OR (answer_isright=\'1\' AND logansw_selected=1) OR (answer_position=logansw_position))');
				
				$wrong_answers = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER, 'WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND ((answer_isright=\'0\' AND logansw_selected=1) OR (answer_isright=\'1\' AND logansw_selected=0)) AND (answer_position!=logansw_position)');
				
				$unanswered = F_count_rows(K_TABLE_TEST_USER.', '.K_TABLE_TESTS_LOGS.', '.K_TABLE_ANSWERS.', '.K_TABLE_LOG_ANSWER, 'WHERE answer_id='.$ma['answer_id'].' AND logansw_answer_id=answer_id AND logansw_testlog_id=testlog_id AND testlog_testuser_id=testuser_id AND testuser_test_id='.$test_id.' AND testlog_question_id='.$mr['question_id'].' AND logansw_selected=-1');
				
				echo '<td>'.($right_answers + $wrong_answers + $unanswered).'</td>'.K_NEWLINE;
				echo '<td colspan="2">&nbsp;</td>'.K_NEWLINE;
				echo '<td>'.$right_answers.'</td>'.K_NEWLINE;
				echo '<td>'.$wrong_answers.'</td>'.K_NEWLINE;
				echo '<td>'.$unanswered.'</td>'.K_NEWLINE;
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
?>
</table>
</div>

<div class="row">
<?php
// show buttons by case
if (isset($test_id) AND ($test_id > 0)) {
	echo '<a href="'.pdfLink(2, $test_id, 0, '', $full_order_field).'" class="xmlbutton" title="'.$l['h_pdf'].'">'.$l['w_pdf'].'</a> ';
}
?>
<input type="hidden" name="order_field" id="order_field" value="<?php echo $order_field; ?>" />
<input type="hidden" name="orderdir" id="orderdir" value="<?php echo $orderdir; ?>" />
	
<!-- comma separated list of required fields -->
<input type="hidden" name="ff_required" id="ff_required" value="" />
<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />

</div>

</form>

</div>
<?php

echo '<div class="pagehelp">'.$l['hp_result_questions'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

// ------------------------------------------------------------

/**
 * Display table header element with order link.
 * @param string $test_id test ID
 * @param string $order_field name of table field
 * @param string $orderdir order direction
 * @param string $title title field of anchor link
 * @param string $name column name
 * @param string $current_order_field current order field name
 * @return table header element string
 */
function F_stats_table_header_element($test_id, $order_field, $orderdir, $title, $name, $current_order_field="") {
	global $l;
	require_once('../config/tce_config.php');
	
	$ord = '';
	if ($order_field == $current_order_field) {
		if ($orderdir) {
			$ord = '<acronym title="'.$l['w_ascent'].'">&gt;</acronym>';
		} else {
			$ord = '<acronym title="'.$l['w_descent'].'">&lt;</acronym>';
		}
	}
	$str = '<th><a href="'.$_SERVER['SCRIPT_NAME'].'?test_id='.$test_id.'&amp;firstrow=0&amp;order_field='.$order_field.'&amp;orderdir='.$orderdir.'" title="'.$title.'">'.$name.'</a> '.$ord.'</th>'.K_NEWLINE;
	return $str;
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
