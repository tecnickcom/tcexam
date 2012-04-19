<?php
//============================================================+
// File name   : tce_show_allresults_users.php
// Begin       : 2008-12-26
// Last Update : 2012-04-15
//
// Description : Display all test results for the selected users.
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
 * Display all test results for the selected users.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2008-12-26
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_all_results_user'];
$enable_calendar = true;
require_once('tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('tce_functions_auth_sql.php');
require_once('tce_functions_statistics.php');
require_once('tce_functions_user_select.php');


// filtering options
if (isset($_REQUEST['startdate'])) {
	$startdate = $_REQUEST['startdate'];
	$startdate_time = strtotime($startdate);
	$startdate = date(K_TIMESTAMP_FORMAT, $startdate_time);
} else {
	$startdate = date('Y').'-01-01 00:00:00';
}
if (isset($_REQUEST['enddate'])) {
	$enddate = $_REQUEST['enddate'];
	$enddate_time = strtotime($enddate);
	$enddate = date(K_TIMESTAMP_FORMAT, $enddate_time);
} else {
	$enddate = date('Y').'-12-31 23:59:59';
}
if (isset($_REQUEST['user_id'])) {
	$user_id = intval($_REQUEST['user_id']);
	if (!F_isAuthorizedEditorForUser($user_id)) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		exit;
	}
} else {
	$user_id = 0;
}
$filter = '&amp;user_id='.$user_id.'&amp;startdate='.$startdate.'&amp;enddate='.$enddate.'';

if (isset($_REQUEST['selectcategory'])) {
	$changecategory = 1;
}

if (isset($_POST['lock'])) {
	$menu_mode = 'lock';
} elseif (isset($_POST['unlock'])) {
	$menu_mode = 'unlock';
} elseif (isset($_POST['extendtime'])) {
	$menu_mode = 'extendtime';
}
if (isset($order_field) AND !empty($order_field) AND (in_array($order_field, array('testuser_creation_time', 'total_score')))) {
	$order_field = $order_field;
} else {
	$order_field = 'testuser_creation_time';
}
if (!isset($orderdir) OR empty($orderdir)) {
	$orderdir=0; $nextorderdir=1; $full_order_field = $order_field;
} else {
	$orderdir=1; $nextorderdir=0; $full_order_field = $order_field.' DESC';
}

if (isset($menu_mode) AND (!empty($menu_mode))) {
	for ($i = 1; $i <= $itemcount; $i++) {
		// for each selected item
		$keyname = 'testuserid'.$i;
		if (isset($$keyname)) {
			$testuser_id = intval($$keyname);
			switch($menu_mode) {
				case 'delete':{
					$sql = 'DELETE FROM '.K_TABLE_TEST_USER.'
						WHERE testuser_id='.$testuser_id.'';
					if (!$r = F_db_query($sql, $db)) {
						echo $sql; //debug
						F_display_db_error();
					}
					break;
				}
				case 'extendtime':{
					// extend the test time by 5 minutes
					// this time extension is obtained moving forward the test starting time
					$extseconds = K_EXTEND_TIME_MINUTES * K_SECONDS_IN_MINUTE;
					$sqlus = 'SELECT testuser_creation_time
						FROM '.K_TABLE_TEST_USER.'
						WHERE testuser_id='.$testuser_id.'
						LIMIT 1';
					if ($rus = F_db_query($sqlus, $db)) {
						if ($mus = F_db_fetch_array($rus)) {
							$newstarttime = date(K_TIMESTAMP_FORMAT, strtotime($mus['testuser_creation_time']) + $extseconds);
							$sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
								SET testuser_creation_time=\''.$newstarttime.'\'
								WHERE testuser_id='.$testuser_id.'';
							if (!$ru = F_db_query($sqlu, $db)) {
								F_display_db_error();
							}
						}
					} else {
						F_display_db_error();
					}
					break;
				}
				case 'lock':{
					// update test mode to 4 = test locked
					$sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
						SET testuser_status=4
						WHERE testuser_id='.$testuser_id.'';
					if (!$ru = F_db_query($sqlu, $db)) {
						F_display_db_error();
					}
					break;
				}
				case 'unlock':{
					// update test mode to 1 = test unlocked
					$sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
						SET testuser_status=1
						WHERE testuser_id='.$testuser_id.'';
					if (!$ru = F_db_query($sqlu, $db)) {
						F_display_db_error();
					}
					break;
				}
			} //end of switch
		}
	}
	F_print_error('MESSAGE', $l['m_updated']);
}


if ($formstatus) {
	if (!isset($user_id) OR empty($user_id)) {
		$user_id = 0;
		$sql = 'SELECT testuser_user_id
			FROM '.K_TABLE_TEST_USER.'
			WHERE testuser_status>0
				AND testuser_creation_time>=\''.$startdate.'\'
				AND testuser_creation_time<=\''.$enddate.'\'';
		if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
			$sql .= ' AND testuser_user_id IN ('.F_getAuthorizedUsers($_SESSION['session_user_id']).')';
		}
		$sql .= ' ORDER BY testuser_creation_time
			LIMIT 1';
		if ($r = F_db_query($sql, $db)) {
			if ($m = F_db_fetch_array($r)) {
				$user_id = $m['testuser_user_id'];
			}
		} else {
			F_display_db_error();
		}
	}
}

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_allresultsuser">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="user_id">'.$l['w_user'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="user_id" id="user_id" size="0" onchange="document.getElementById(\'form_allresultsuser\').submit()">'.K_NEWLINE;
$sql = 'SELECT user_id, user_lastname, user_firstname, user_name
	FROM '.K_TABLE_USERS.'
	WHERE user_id > 1';
if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
	// filter for level
	$sql .= ' AND ((user_level<'.$_SESSION['session_user_level'].') OR (user_id='.$_SESSION['session_user_id'].'))';
	// filter for groups
	$sql .= ' AND user_id IN (SELECT tb.usrgrp_user_id
		FROM '.K_TABLE_USERGROUP.' AS ta, '.K_TABLE_USERGROUP.' AS tb
		WHERE ta.usrgrp_group_id=tb.usrgrp_group_id
			AND ta.usrgrp_user_id='.intval($_SESSION['session_user_id']).'
			AND tb.usrgrp_user_id=user_id)';
}
$sql .= ' ORDER BY user_lastname, user_firstname, user_name';
if ($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['user_id'].'"';
		if ($m['user_id'] == $user_id) {
			echo ' selected="selected"';
		}
		echo '>'.$countitem.'. '.htmlspecialchars($m['user_lastname'].' '.$m['user_firstname'].' - '.$m['user_name'].'', ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
		$countitem++;
	}
} else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;

// link for user selection popup
$jsaction = 'selectWindow=window.open(\'tce_select_users_popup.php?cid=user_id\', \'selectWindow\', \'dependent, height=600, width=800, menubar=no, resizable=yes, scrollbars=yes, status=no, toolbar=no\');return false;';
echo '<a href="#" onclick="'.$jsaction.'" class="xmlbutton" title="'.$l['w_select'].'">...</a>';

echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormRowTextInput('startdate', $l['w_time_begin'], $l['w_time_begin'].' '.$l['w_datetime_format'], '', $startdate, '', 19, false, true, false);
echo getFormRowTextInput('enddate', $l['w_time_end'], $l['w_time_end'].' '.$l['w_datetime_format'], '', $enddate, '', 19, false, true, false);

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">&nbsp;</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="submit" name="selectcategory" id="selectcategory" value="'.$l['w_select'].'" />'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo '<div class="rowl">'.K_NEWLINE;
echo '<table class="userselect">'.K_NEWLINE;
echo '<tr>'.K_NEWLINE;

if ($l['a_meta_dir'] == 'rtl') {
	$tdalignr = 'left';
	$tdalign = 'right';
} else {
	$tdalignr = 'right';
	$tdalign = 'left';
}

// points for graph
$points = '';
echo '<th>&nbsp;</th>'.K_NEWLINE;
echo '<th>#</th>'.K_NEWLINE;
echo F_select_table_header_element('testuser_creation_time', $nextorderdir, $l['h_time_begin'], $l['w_time_begin'], $order_field, $filter);
//echo F_select_table_header_element('testuser_end_time', $nextorderdir, $l['h_time_end'], $l['w_time_end'], $order_field, $filter);
echo '<th title="'.$l['h_test_time'].'">'.$l['w_time'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['w_test'].'">'.$l['w_test'].'</th>'.K_NEWLINE;
echo F_select_table_header_element('total_score', $nextorderdir, $l['h_score_total'], $l['w_score'], $order_field, $filter);
echo '<th title="'.$l['h_answers_right'].'">'.$l['w_answers_right'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_answers_wrong'].'">'.$l['w_answers_wrong'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_unanswered'].'">'.$l['w_questions_unanswered'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_undisplayed'].'">'.$l['w_questions_undisplayed'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_unrated'].'">'.$l['w_questions_unrated'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['w_status'].'">'.$l['w_status'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_testcomment'].'">'.$l['w_comment'].'</th>'.K_NEWLINE;
echo '</tr>'.K_NEWLINE;

// output users stats
$sqlr = 'SELECT
	testuser_id,
	test_id,
	test_name,
	test_duration_time,
	testuser_creation_time,
	testuser_status,
	SUM(testlog_score) AS total_score,
	MAX(testlog_change_time) AS testuser_end_time
	FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_TESTS.'
	WHERE testuser_status>0
		AND testuser_creation_time>=\''.$startdate.'\'
		AND testuser_creation_time<=\''.$enddate.'\'
		AND testuser_user_id='.$user_id.'
		AND testlog_testuser_id=testuser_id
		AND testuser_test_id=test_id';
if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
	$sqlr .= ' AND test_user_id IN ('.F_getAuthorizedUsers($_SESSION['session_user_id']).')';
}
$sqlr .= ' GROUP BY testuser_id, test_id, test_name, test_duration_time, testuser_creation_time, testuser_status ORDER BY '.$full_order_field.'';
if ($rr = F_db_query($sqlr, $db)) {
	$itemcount = 0;
	$passed = 0;
	$statsdata = array();
	$statsdata['score'] = array();
	$statsdata['right'] = array();
	$statsdata['wrong'] = array();
	$statsdata['unanswered'] = array();
	$statsdata['undisplayed'] = array();
	$statsdata['unrated'] = array();
	while($mr = F_db_fetch_array($rr)) {
		$itemcount++;
		$usrtestdata = F_getUserTestStat($mr['test_id'], $user_id);
		$halfscore = ($usrtestdata['max_score'] / 2);
		echo '<tr>';
		echo '<td>';
		echo '<input type="checkbox" name="testuserid'.$itemcount.'" id="testuserid'.$itemcount.'" value="'.$mr['testuser_id'].'" title="'.$l['w_select'].'"';
		if (isset($_REQUEST['checkall']) AND ($_REQUEST['checkall'] == 1)) {
			echo ' checked="checked"';
		}
		echo ' />';
		echo '</td>'.K_NEWLINE;
		echo '<td><a href="tce_show_result_user.php?testuser_id='.$mr['testuser_id'].'&amp;test_id='.$mr['test_id'].'&amp;user_id='.$user_id.'" title="'.$l['h_view_details'].'">'.$itemcount.'</a></td>'.K_NEWLINE;

		echo '<td style="text-align:center;">'.$mr['testuser_creation_time'].'</td>'.K_NEWLINE;
		//echo '<td style="text-align:center;">'.$mr['testuser_end_time'].'</td>'.K_NEWLINE;
		$time_diff = strtotime($mr['testuser_end_time']) - strtotime($mr['testuser_creation_time']); //sec
		$time_diff = gmdate('H:i:s', $time_diff);
		echo '<td style="text-align:center;">'.$time_diff.'</td>'.K_NEWLINE;

		echo '<td style="text-align:'.$tdalign.';"><a href="tce_show_result_allusers.php?test_id='.$mr['test_id'].'">'.$mr['test_name'].'</a></td>'.K_NEWLINE;
		$passmsg = '';
		if ($usrtestdata['score_threshold'] > 0) {
			if ($usrtestdata['score'] >= $usrtestdata['score_threshold']) {
				$passmsg = ' title="'.$l['w_passed'].'" style="background-color:#BBFFBB;"';
				$passed++;
			} else {
				$passmsg = ' title="'.$l['w_not_passed'].'" style="background-color:#FFBBBB;"';
			}
		} elseif ($usrtestdata['score'] > $halfscore) {
			$passed++;
		}
		$tmpscore = ($usrtestdata['score'] / $usrtestdata['max_score']);
		$points .= 'x'.round($tmpscore * 100);
		echo '<td'.$passmsg.' class="numeric">'.F_formatFloat($mr['total_score']).'&nbsp;'.F_formatPercentage($tmpscore).'</td>'.K_NEWLINE;
		$tmpright = ($usrtestdata['right'] / $usrtestdata['all']);
		$points .= 'v'.round($tmpright * 100);
		echo '<td class="numeric">'.$usrtestdata['right'].'&nbsp;'.F_formatPercentage($tmpright).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['wrong'].'&nbsp;'.F_formatPercentage($usrtestdata['wrong'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['unanswered'].'&nbsp;'.F_formatPercentage($usrtestdata['unanswered'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['undisplayed'].'&nbsp;'.F_formatPercentage($usrtestdata['undisplayed'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['unrated'].'&nbsp;'.F_formatPercentage($usrtestdata['unrated'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		if ($mr['testuser_status'] == 4) {
			echo '<td style="background-color:#FFBBBB;">'.$l['w_locked'].'</td>'.K_NEWLINE;
		} else {
			echo '<td style="background-color:#BBFFBB;">'.$l['w_unlocked'].'</td>'.K_NEWLINE;
		}
		echo K_NEWLINE;
		if (!empty($usrtestdata['comment'])) {
			echo '<td title="'.substr(F_compact_string(htmlspecialchars($usrtestdata['comment'], ENT_NOQUOTES, $l['a_meta_charset'])),0,255).'">'.$l['w_yes'].'</td>'.K_NEWLINE;
		} else {
			echo '<td>&nbsp;</td>'.K_NEWLINE;
		}
		echo '</tr>'.K_NEWLINE;

		// collects data for descriptive statistics
		$statsdata['score'][] = $mr['total_score'] / $usrtestdata['max_score'];
		$statsdata['right'][] = $usrtestdata['right'] / $usrtestdata['all'];
		$statsdata['wrong'][] = $usrtestdata['wrong'] / $usrtestdata['all'];
		$statsdata['unanswered'][] = $usrtestdata['unanswered'] / $usrtestdata['all'];
		$statsdata['undisplayed'][] = $usrtestdata['undisplayed'] / $usrtestdata['all'];
		$statsdata['unrated'][] = $usrtestdata['unrated'] / $usrtestdata['all'];
	}
} else {
	F_display_db_error();
}

echo '<tr>';
echo '<td colspan="5" style="text-align:'.$tdalignr.';">'.$l['w_passed'].'</td>';
$passed_perc = 0;
if ($itemcount > 0) {
	$passed_perc = ($passed / $itemcount);
}
echo '<td class="numeric"';
if ($passed_perc > 0.5) {
	echo  ' style="background-color:#BBFFBB;"';
} else {
	echo  ' style="background-color:#FFBBBB;"';
}
echo '><strong>'.$passed.'&nbsp;'.F_formatPercentage($passed_perc).'</strong></td>'.K_NEWLINE;
echo '<td colspan="7">&nbsp;</td>';
echo '</tr>';

echo '</table>'.K_NEWLINE;

if ($itemcount > 0) {

	// check/uncheck all options
	echo '<span dir="ltr">';
	echo '<input type="radio" name="checkall" id="checkall1" value="1" onclick="document.getElementById(\'form_allresultsuser\').submit()" />';
	echo '<label for="checkall1">'.$l['w_check_all'].'</label> ';
	echo '<input type="radio" name="checkall" id="checkall0" value="0" onclick="document.getElementById(\'form_allresultsuser\').submit()" />';
	echo '<label for="checkall0">'.$l['w_uncheck_all'].'</label>';
	echo '</span>'.K_NEWLINE;
	echo '<br /><strong style="margin:5px">'.$l['m_with_selected'].'</strong><br />'.K_NEWLINE;
	F_submit_button('delete', $l['w_delete'], $l['h_delete']);
	F_submit_button('lock', $l['w_lock'], $l['w_lock']);
	F_submit_button('unlock', $l['w_unlock'], $l['w_unlock']);
	F_submit_button('extendtime', '+'.K_EXTEND_TIME_MINUTES.' min', $l['h_add_five_minutes']);

	echo '<br /><br />'.K_NEWLINE;

	// calculate statistics
	$stats = F_getArrayStatistics($statsdata);
	$excludestat = array('sum', 'variance');
	$calcpercent = array('mean', 'median', 'mode', 'minimum', 'maximum', 'range', 'standard_deviation');

	echo '<table class="userselect">'.K_NEWLINE;
	echo '<tr><td colspan="13" style="background-color:#DDDDDD;"><strong>'.$l['w_statistics'].'</strong></td></tr>'.K_NEWLINE;

	// print statistics (one line for each statistic data).
	if (($usrtestdata['score_threshold'] > 0) AND ($stats['number']['score'] > 0)) {
		echo '<tr><td style="text-align:'.$tdalignr.';">'.$l['w_passed'].'</td><td class="numeric">'.$passed.' '.F_formatPercentage($passed / $stats['number']['score']).'</td><td colspan="5">&nbsp;</td></tr>'.K_NEWLINE;
	}

	echo '<tr>'.K_NEWLINE;
	echo '<th>&nbsp;</th>'.K_NEWLINE;
	echo '<th title="'.$l['h_score'].'">'.$l['w_score'].'</th>'.K_NEWLINE;
	echo '<th title="'.$l['h_answers_right'].'">'.$l['w_answers_right'].'</th>'.K_NEWLINE;
	echo '<th title="'.$l['h_answers_wrong'].'">'.$l['w_answers_wrong'].'</th>'.K_NEWLINE;
	echo '<th title="'.$l['h_questions_unanswered'].'">'.$l['w_questions_unanswered'].'</th>'.K_NEWLINE;
	echo '<th title="'.$l['h_questions_undisplayed'].'">'.$l['w_questions_undisplayed'].'</th>'.K_NEWLINE;
	echo '<th title="'.$l['h_questions_unrated'].'">'.$l['w_questions_unrated'].'</th>'.K_NEWLINE;
	echo '</tr>'.K_NEWLINE;

	foreach ($stats as $row => $columns) {
		if (!in_array($row, $excludestat)) {
			echo '<tr>';
			echo '<td style="text-align:'.$tdalignr.';">'.$l['w_'.$row].'</td>'.K_NEWLINE;
			echo '<td class="numeric">'.F_formatFloat($columns['score']);
			if (in_array($row, $calcpercent)) {
				echo ' '.F_formatPercentage($columns['score'] / $usrtestdata['max_score']);
			}
			echo '</td>'.K_NEWLINE;
			echo '<td class="numeric">'.F_formatFloat($columns['right']);
			if (in_array($row, $calcpercent)) {
				echo ' '.F_formatPercentage($columns['right'] / $usrtestdata['all']);
			}
			echo '</td>'.K_NEWLINE;
			echo '<td class="numeric">'.F_formatFloat($columns['wrong']);
			if (in_array($row, $calcpercent)) {
				echo ' '.F_formatPercentage($columns['wrong'] / $usrtestdata['all']);
			}
			echo '</td>'.K_NEWLINE;
			echo '<td class="numeric">'.F_formatFloat($columns['unanswered']);
			if (in_array($row, $calcpercent)) {
				echo ' '.F_formatPercentage($columns['unanswered'] / $usrtestdata['all']);
			}
			echo '</td>'.K_NEWLINE;
			echo '<td class="numeric">'.F_formatFloat($columns['undisplayed']);
			if (in_array($row, $calcpercent)) {
				echo ' '.F_formatPercentage($columns['undisplayed'] / $usrtestdata['all']);
			}
			echo '</td>'.K_NEWLINE;
			echo '<td class="numeric">'.F_formatFloat($columns['unrated']);
			if (in_array($row, $calcpercent)) {
				echo ' '.F_formatPercentage($columns['unrated'] / $usrtestdata['all']);
			}
			echo '</td>'.K_NEWLINE;
			echo '</tr>';
		}
	}

	echo '</table>'.K_NEWLINE;

	echo '<br />'.K_NEWLINE;
}

echo '</div>'.K_NEWLINE;
echo '<div class="row">'.K_NEWLINE;
// show buttons by case
if (isset($user_id) AND ($user_id > 0) AND ($itemcount > 0)) {
	echo '<a href="'.pdfUserResultsLink($user_id, $startdate, $enddate, $full_order_field).'" class="xmlbutton" title="'.$l['h_pdf'].'">'.$l['w_pdf'].'</a> ';
	echo '<a href="tce_csv_allresults_user.php?user_id='.$user_id.'&amp;startdate='.urlencode($startdate).'&amp;enddate='.urlencode($enddate).'&amp;order_field='.urlencode($full_order_field).'" class="xmlbutton" title="'.$l['h_csv_export'].'">CSV</a> ';
	echo '<a href="tce_xml_user_results.php?user_id='.$user_id.'&amp;startdate='.urlencode($startdate).'&amp;enddate='.urlencode($enddate).'&amp;order_field='.urlencode($full_order_field).'&amp;menu_mode=startlongprocess" class="xmlbutton" title="'.$l['h_xml_export'].'">XML</a> ';
}

echo '<input type="hidden" name="order_field" id="order_field" value="'.$order_field.'" />'.K_NEWLINE;
echo '<input type="hidden" name="orderdir" id="orderdir" value="'.$orderdir.'" />'.K_NEWLINE;
// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />'.K_NEWLINE;
echo '<input type="hidden" name="itemcount" id="itemcount" value="'.$itemcount.'" />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

// display graph
if (($itemcount > 1) AND isset($points) AND !empty($points)) {
	$w = 800;
	$h = 300;
	echo '<div class="row">'.K_NEWLINE;
	echo '<hr />'.K_NEWLINE;
	// legend
	echo '<div style="font-size:90%;"><br /><span style="background-color:#ff0000;color:#ffffff;">&nbsp;'.$l['w_score'].'&nbsp;</span> <span style="background-color:#0000ff;color:#ffffff;">&nbsp;'.$l['w_answers_right'].'&nbsp;</span> / <span style="background-color:#dddddd;color:#000000;">&nbsp;'.$l['w_tests'].'&nbsp;</span></div>';
	echo '<img src="tce_svg_graph.php?w='.$w.'&amp;h='.$h.'&amp;p='.substr($points, 1).'" width="'.$w.'" height="'.$h.'" alt="'.$l['w_result_graph'].'" />'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
}

echo '</form>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_allresults_user'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

// ------------------------------------------------------------

/**
 * Returns an URL to open the PDF generator page for user's results.
 * @param $user_id (int) user ID
 * @param $startdate (string) start date in yyyy-mm-dd hh:mm:ss format
 * @param $enddate (string) end date in yyyy-mm-dd hh:mm:ss format
 * @param $order_field (string) ORDER BY portion of the SQL query
 * @return string
 */
function pdfUserResultsLink($user_id, $startdate, $enddate, $order_field='') {
	$pdflink = 'tce_pdf_user_results.php?';
	$pdflink .= 'user_id='.$user_id.'';
	$pdflink .= '&amp;startdate='.urlencode($startdate).'';
	$pdflink .= '&amp;enddate='.urlencode($enddate).'';
	if ($order_field) {
		$pdflink .= '&amp;orderfield='.urlencode($order_field).'';
	}
	return $pdflink;
}

//============================================================+
// END OF FILE
//============================================================+
