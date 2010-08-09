<?php
//============================================================+
// File name   : tce_show_result_allusers.php
// Begin       : 2004-06-10
// Last Update : 2010-06-16
//
// Description : Display test results summary for all users.
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
 * Display test results summary for all users.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2004-06-10
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_result_all_users'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('../code/tce_functions_auth_sql.php');
require_once('../code/tce_functions_statistics.php');

if (isset($_REQUEST['test_id']) AND ($_REQUEST['test_id'] > 0)) {
	$test_id = intval($_REQUEST['test_id']);
	// check user's authorization
	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		F_print_error('ERROR', $l['m_authorization_denied']);
		exit;
	}
}

if (isset($selectcategory)) {
	$changecategory = 1;
}
if(isset($test_id)) {
	$test_id = intval($test_id);
}
if(!isset($group_id) OR empty($group_id)) {
	$group_id = 0;
} else {
	$group_id = intval($group_id);
}
if(isset($_POST['lock'])) {
	$menu_mode = 'lock';
} elseif(isset($_POST['unlock'])) {
	$menu_mode = 'unlock';
} elseif(isset($_POST['extendtime'])) {
	$menu_mode = 'extendtime';
}
if(!isset($order_field) OR empty($order_field)) {
	$order_field = 'total_score, user_lastname, user_firstname';
} else {
	$order_field = F_escape_sql($order_field);
}
if(!isset($orderdir) OR empty($orderdir)) {
	$orderdir=0; $nextorderdir=1; $full_order_field = $order_field;
} else {
	$orderdir=1; $nextorderdir=0; $full_order_field = $order_field.' DESC';
}

if (isset($menu_mode) AND (!empty($menu_mode))) {
	for ($i = 1; $i <= $itemcount; $i++) {
		// for each selected item
		$keyname = 'testuserid'.$i;
		if (isset($$keyname)) {
			$testuser_id = $$keyname;
			switch($menu_mode) {
				case 'delete':{
					$sql = 'DELETE FROM '.K_TABLE_TEST_USER.'
						WHERE testuser_id='.$testuser_id.'';
					if(!$r = F_db_query($sql, $db)) {
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
					if($rus = F_db_query($sqlus, $db)) {
						if($mus = F_db_fetch_array($rus)) {
							$newstarttime = date(K_TIMESTAMP_FORMAT, strtotime($mus['testuser_creation_time']) + $extseconds);
							$sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
								SET testuser_creation_time=\''.$newstarttime.'\'
								WHERE testuser_id='.$testuser_id.'';
							if(!$ru = F_db_query($sqlu, $db)) {
							echo $sqlu; //DEBUG
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
					if(!$ru = F_db_query($sqlu, $db)) {
						F_display_db_error();
					}
					break;
				}
				case 'unlock':{
					// update test mode to 1 = test unlocked
					$sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
						SET testuser_status=1
						WHERE testuser_id='.$testuser_id.'';
					if(!$ru = F_db_query($sqlu, $db)) {
						F_display_db_error();
					}
					break;
				}
			} //end of switch
		}
	}
	F_print_error('MESSAGE', $l['m_updated']);
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
			$test_duration_time = ($m['test_duration_time'] * K_SECONDS_IN_MINUTE);
		} else {
			$test_id = 0;
			$test_duration_time = 0;
		}
	} else {
		F_display_db_error();
	}
}
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_resultallusers">

<div class="row">
<span class="label">
<label for="test_id"><?php echo $l['w_test']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="changecategory" id="changecategory" value="" />
<select name="test_id" id="test_id" size="0" onchange="document.getElementById('form_resultallusers').changecategory.value=1; document.getElementById('form_resultallusers').submit()" title="<?php echo $l['h_test']; ?>">
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
<input type="submit" name="selectcategory" id="selectcategory" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>

<div class="row">
<span class="label">
<label for="group_id"><?php echo $l['w_group']; ?></label>
</span>
<span class="formw">
<select name="group_id" id="group_id" size="0" onchange="document.getElementById('form_resultallusers').submit()">
<?php
$sql = 'SELECT *
	FROM '.K_TABLE_GROUPS.'
	WHERE group_id IN (
		SELECT tstgrp_group_id
		FROM '.K_TABLE_TEST_GROUPS.'
		WHERE tstgrp_test_id='.$test_id.'
	)
	ORDER BY group_name';
if($r = F_db_query($sql, $db)) {
	echo '<option value="0"';
		if($m['group_id'] == $group_id) {
			echo ' selected="selected"';
		}
		echo '>&nbsp;-&nbsp;</option>'.K_NEWLINE;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['group_id'].'"';
		if($m['group_id'] == $group_id) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($m['group_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
}
else {
	echo '</select></span></div>'.K_NEWLINE;
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
<input type="submit" name="selectgroup" id="selectgroup" value="<?php echo $l['w_select']; ?>" />
</span>
</div>
</noscript>


<div class="row"><hr /></div>

<div class="rowl">
<table class="userselect">
<tr>
<?php
if ($l['a_meta_dir'] == 'rtl') {
	$tdalignr = 'left';
	$tdalign = 'right';
} else {
	$tdalignr = 'right';
	$tdalign = 'left';
}

echo '<th>&nbsp;</th>'.K_NEWLINE;
echo '<th>#</th>'.K_NEWLINE;
echo F_result_allusers_table_header_element($test_id, 'total_score', $nextorderdir, $l['h_score_total'], $l['w_score'], $order_field);
echo F_result_allusers_table_header_element($test_id, 'user_name', $nextorderdir, $l['h_login_name'], $l['w_user'], $order_field);
echo F_result_allusers_table_header_element($test_id, 'user_lastname', $nextorderdir, $l['h_lastname'], $l['w_lastname'], $order_field);
echo F_result_allusers_table_header_element($test_id, 'user_firstname', $nextorderdir, $l['h_firstname'], $l['w_firstname'], $order_field);
echo '<th title="'.$l['h_answers_right'].'">'.$l['w_answers_right'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_answers_wrong'].'">'.$l['w_answers_wrong'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_unanswered'].'">'.$l['w_questions_unanswered'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_undisplayed'].'">'.$l['w_questions_undisplayed'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_unrated'].'">'.$l['w_questions_unrated'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['w_status'].' ('.$l['w_time'].' ['.$l['w_minutes'].'])">'.$l['w_status'].' ('.$l['w_time'].' ['.$l['w_minutes'].'])</th>'.K_NEWLINE;
echo '<th title="'.$l['h_testcomment'].'">'.$l['w_comment'].'</th>'.K_NEWLINE;
?>
</tr>
<?php
// output users stats
$sqlr = 'SELECT testuser_id, testuser_status, user_id, user_lastname, user_firstname, user_name, SUM(testlog_score) AS total_score
	FROM '.K_TABLE_TESTS_LOGS.', '.K_TABLE_TEST_USER.', '.K_TABLE_USERS.'';
if (isset($group_id) AND ($group_id > 0)) {
	$sqlr .= ','.K_TABLE_USERGROUP.'';
}
$sqlr .= ' WHERE testlog_testuser_id=testuser_id
		AND testuser_user_id=user_id
		AND testuser_test_id='.$test_id.'';
if (isset($group_id) AND ($group_id > 0)) {
	$sqlr .= ' AND usrgrp_user_id=user_id
		AND usrgrp_group_id='.$group_id.'';
}
$sqlr .= ' GROUP BY testuser_id, user_id, user_lastname, user_firstname, user_name, testuser_status
	ORDER BY '.$full_order_field.'';
if($rr = F_db_query($sqlr, $db)) {
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
		$usrtestdata = F_getUserTestStat($test_id, $mr['user_id']);
		echo '<tr>';
		echo '<td>';
		echo '<input type="checkbox" name="testuserid'.$itemcount.'" id="testuserid'.$itemcount.'" value="'.$mr['testuser_id'].'" title="'.$l['w_select'].'"';
		if (isset($_REQUEST['checkall']) AND ($_REQUEST['checkall'] == 1)) {
			echo ' checked="checked"';
		}
		echo ' />';
		echo '</td>'.K_NEWLINE;
		echo '<td><a href="tce_show_result_user.php?testuser_id='.$mr['testuser_id'].'&amp;test_id='.$test_id.'&amp;user_id='.$mr['user_id'].'" title="'.$l['h_view_details'].'">'.$itemcount.'</a></td>'.K_NEWLINE;

		$passmsg = '';
		if ($usrtestdata['score_threshold'] > 0) {
			if ($usrtestdata['score'] >= $usrtestdata['score_threshold']) {
				$passmsg = ' title="'.$l['w_passed'].'" style="background-color:#BBFFBB;"';
				$passed++;
			} else {
				$passmsg = ' title="'.$l['w_not_passed'].'" style="background-color:#FFBBBB;"';
			}
		}
		echo '<td'.$passmsg.' class="numeric">'.$mr['total_score'].' '.F_formatPercentage($usrtestdata['score'] / $usrtestdata['max_score']).'</td>'.K_NEWLINE;
		echo '<td style="text-align:'.$tdalign.';"><a href="tce_edit_user.php?user_id='.$mr['user_id'].'">'.$mr['user_name'].'</a></td>'.K_NEWLINE;
		echo '<td style="text-align:'.$tdalign.';">&nbsp;'.$mr['user_lastname'].'</td>'.K_NEWLINE;
		echo '<td style="text-align:'.$tdalign.';">&nbsp;'.$mr['user_firstname'].'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['right'].' '.F_formatPercentage($usrtestdata['right'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['wrong'].' '.F_formatPercentage($usrtestdata['wrong'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['unanswered'].' '.F_formatPercentage($usrtestdata['unanswered'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['undisplayed'].' '.F_formatPercentage($usrtestdata['undisplayed'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['unrated'].' '.F_formatPercentage($usrtestdata['unrated'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		if ($mr['testuser_status'] == 4) {
			echo '<td style="background-color:#FFBBBB;">'.$l['w_locked'];
		} else {
			echo '<td style="background-color:#BBFFBB;">'.$l['w_unlocked'];
		}
		// remaining user time in minutes
		$remaining_time = round(((time() - strtotime($usrtestdata['time'])) - $test_duration_time) / K_SECONDS_IN_MINUTE);
		if ($remaining_time < 0) {
			echo ' ('.$remaining_time.')';
		}
		echo '</td>'.K_NEWLINE;
		if (!empty($usrtestdata['comment'])) {
			echo '<td title="'.substr(F_compact_string(htmlspecialchars($usrtestdata['comment'], ENT_NOQUOTES, $l['a_meta_charset'])), 0, 255).'">'.$l['w_yes'].'</td>'.K_NEWLINE;
		} else {
			echo '<td>&nbsp;</td>'.K_NEWLINE;
		}
		echo '</tr>'.K_NEWLINE;

		// collects data for descriptive statistics
		$statsdata['score'][] = $mr['total_score'];
		$statsdata['right'][] = $usrtestdata['right'];
		$statsdata['wrong'][] = $usrtestdata['wrong'];
		$statsdata['unanswered'][] = $usrtestdata['unanswered'];
		$statsdata['undisplayed'][] = $usrtestdata['undisplayed'];
		$statsdata['unrated'][] = $usrtestdata['unrated'];
	}
} else {
	F_display_db_error();
}
echo '</table>'.K_NEWLINE;

if ($itemcount > 0) {

	// check/uncheck all options
	echo '<span dir="ltr">';
	echo '<input type="radio" name="checkall" id="checkall1" value="1" onclick="document.getElementById(\'form_resultallusers\').submit()" />';
	echo '<label for="checkall1">'.$l['w_check_all'].'</label> ';
	echo '<input type="radio" name="checkall" id="checkall0" value="0" onclick="document.getElementById(\'form_resultallusers\').submit()" />';
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
			echo '<td class="numeric">'.round($columns['score'], 3);
			if (in_array($row, $calcpercent)) {
				echo ' '.F_formatPercentage($columns['score'] / $usrtestdata['max_score']);
			}
			echo '</td>'.K_NEWLINE;
			echo '<td class="numeric">'.round($columns['right'], 3);
			if (in_array($row, $calcpercent)) {
				echo ' '.F_formatPercentage($columns['right'] / $usrtestdata['all']);
			}
			echo '</td>'.K_NEWLINE;
			echo '<td class="numeric">'.round($columns['wrong'], 3);
			if (in_array($row, $calcpercent)) {
				echo ' '.F_formatPercentage($columns['wrong'] / $usrtestdata['all']);
			}
			echo '</td>'.K_NEWLINE;
			echo '<td class="numeric">'.round($columns['unanswered'], 3);
			if (in_array($row, $calcpercent)) {
				echo ' '.F_formatPercentage($columns['unanswered'] / $usrtestdata['all']);
			}
			echo '</td>'.K_NEWLINE;
			echo '<td class="numeric">'.round($columns['undisplayed'], 3);
			if (in_array($row, $calcpercent)) {
				echo ' '.F_formatPercentage($columns['undisplayed'] / $usrtestdata['all']);
			}
			echo '</td>'.K_NEWLINE;
			echo '<td class="numeric">'.round($columns['unrated'], 3);
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
if (isset($test_id) AND ($test_id > 0)) {
	echo '<a href="tce_csv_result_allusers.php?testid='.$test_id.'&amp;groupid='.$group_id.'&amp;order_field='.urlencode($full_order_field).'" class="xmlbutton" title="'.$l['h_csv_export'].'">CSV</a> ';
	echo '<a href="'.pdfLink(1, $test_id, $group_id, '', $full_order_field).'" class="xmlbutton" title="'.$l['h_pdf'].'">'.$l['w_pdf'].'</a> ';
	echo '<a href="'.pdfLink(4, $test_id, $group_id, '', $full_order_field).'" class="xmlbutton" title="'.$l['h_pdf_all'].'">'.$l['w_pdf_all'].'</a> ';
	if (K_DISPLAY_PDFTEXT_BUTTON) {
		echo '<a href="'.pdfLink(5, $test_id, $group_id, '', $full_order_field).'" class="xmlbutton" title="'.$l['h_pdf_all'].' - TEXT">'.$l['w_pdf'].' TEXT</a> ';
	}
	echo '<a href="tce_xml_results.php?testid='.$test_id.'&amp;groupid='.$group_id.'&amp;menu_mode=startlongprocess" class="xmlbutton" title="'.$l['h_xml_export'].'">XML</a> ';
	echo '<a href="tce_email_results.php?testid='.$test_id.'&amp;groupid='.$group_id.'&amp;userid=0&amp;mode=1&amp;menu_mode=startlongprocess" class="xmlbutton" title="'.$l['h_email_all_results'].'">'.$l['w_email_all_results'].'</a> ';
	echo '<a href="tce_email_results.php?testid='.$test_id.'&amp;groupid='.$group_id.'&amp;userid=0&amp;mode=0&amp;menu_mode=startlongprocess" class="xmlbutton" title="'.$l['h_email_all_results'].' + PDF">'.$l['w_email_all_results'].' + PDF</a> ';
	$custom_export = K_ENABLE_CUSTOM_EXPORT;
	if (!empty($custom_export)) {
		echo '<a href="tce_export_custom.php?testid='.$test_id.'&amp;groupid='.$group_id.'&amp;menu_mode=startlongprocess" class="xmlbutton" title="'.$custom_export.'">'.$custom_export.'</a> ';
	}
}
?>
<input type="hidden" name="order_field" id="order_field" value="<?php echo $order_field; ?>" />
<input type="hidden" name="orderdir" id="orderdir" value="<?php echo $orderdir; ?>" />
<!-- comma separated list of required fields -->
<input type="hidden" name="ff_required" id="ff_required" value="" />
<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />
<input type="hidden" name="itemcount" id="itemcount" value="<?php echo $itemcount; ?>" />
</div>

</form>

</div>
<?php

echo '<div class="pagehelp">'.$l['hp_result_alluser'].'</div>'.K_NEWLINE;
echo '</div>';

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
function F_result_allusers_table_header_element($test_id, $order_field, $orderdir, $title, $name, $current_order_field="") {
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
