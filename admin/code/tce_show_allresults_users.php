<?php
//============================================================+
// File name   : tce_show_allresults_users.php
// Begin       : 2008-12-26
// Last Update : 2009-10-10
// 
// Description : Display all test results for the selected users.
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
 * Display all test results for the selected users.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2008-12-26
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_all_results_user'];
$enable_calendar = true;
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('../code/tce_functions_auth_sql.php');
require_once('../code/tce_functions_statistics.php');

if (isset($_REQUEST['user_id'])) {
	$user_id = intval($_REQUEST['user_id']);
	// check user's authorization
	if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
		$sql = 'SELECT user_id 
			FROM '.K_TABLE_USERS.' 
			WHERE user_id='.$user_id.' 
				AND user_id IN ('.F_getAuthorizedUsers($_SESSION['session_user_id']).')';
		if($r = F_db_query($sql, $db)) {
			if(!F_db_fetch_array($r)) {
				F_print_error('ERROR', $l['m_authorization_denied']);
				exit;
			}
		} else {
			F_display_db_error();
		}
	}
}

if (isset($selectcategory)) {
	$changecategory = 1;
}
if(isset($_POST['lock'])) {
	$menu_mode = 'lock';
} elseif(isset($_POST['unlock'])) {
	$menu_mode = 'unlock';
} elseif(isset($_POST['extendtime'])) {
	$menu_mode = 'extendtime';
}
if(!isset($order_field) OR empty($order_field)) {
	$order_field = 'testuser_creation_time';
} else {
	$order_field = F_escape_sql($order_field);
}
if(!isset($orderdir) OR empty($orderdir)) {
	$orderdir=0; $nextorderdir=1; $full_order_field = $order_field;
} else {
	$orderdir=1; $nextorderdir=0; $full_order_field = $order_field.' DESC';
}
if(isset($user_id)) {
	$user_id = intval($user_id);
}
if(!isset($startdate) OR empty($startdate)) {
	$startdate = date('Y').'-01-01 00:00:00';
}
if(!isset($enddate) OR empty($enddate)) {
	$enddate = date('Y').'-12-31 23:59:59';
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
	if(!isset($user_id) OR empty($user_id)) {
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
		if($r = F_db_query($sql, $db)) {
			if($m = F_db_fetch_array($r)) {
				$user_id = $m['testuser_user_id'];
			} else {
				$user_id = 0;
			}
		} else {
			F_display_db_error();
		}
	}
}
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_allresultsuser">

<div class="row">
<span class="label">
<label for="user_id"><?php echo $l['w_user']; ?></label>
</span>
<span class="formw">
<select name="user_id" id="user_id" size="0" onchange="document.getElementById('form_allresultsuser').submit()">
<?php
$sql = "SELECT user_id, user_lastname, user_firstname, user_name
	FROM ".K_TABLE_USERS."
	WHERE user_level > 0";
	if ($_SESSION['session_user_level'] < K_AUTH_ADMINISTRATOR) {
		$sql .= " AND user_id IN (".F_getAuthorizedUsers($_SESSION['session_user_id']).")";
	}
	$sql .= " ORDER BY user_lastname, user_firstname, user_name";
if($r = F_db_query($sql, $db)) {
	$countitem = 1;
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['user_id'].'"';
		if($m['user_id'] == $user_id) {
			echo ' selected="selected"';
		}
		echo '>'.$countitem.'. '.htmlspecialchars($m['user_lastname'].' '.$m['user_firstname'].' - '.$m['user_name'].'', ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
		$countitem++;
	}
} else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
?>
</select>
</span>
</div>

<div class="row">
<span class="label">
<label for="startdate"><?php echo $l['w_time_begin'].' '.$l['w_datetime_format']; ?></label>
</span>
<span class="formw">
<input type="text" name="startdate" id="startdate" value="<?php echo $startdate; ?>" size="20" maxlength="20" title="<?php echo $l['h_time_begin']; ?>" />
<button name="startdate_trigger" id="startdate_trigger" title="<?php echo $l['w_calendar']; ?>">...</button>
<input type="hidden" name="x_startdate" id="x_startdate" value="^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2})\:([0-9]{1,2})\:([0-9]{1,2})$" />
<input type="hidden" name="xl_startdate" id="xl_startdate" value="<?php echo $l['w_time_begin']; ?>" />
</span>
</div>

<div class="row">
<span class="label">
<label for="enddate"><?php echo $l['w_time_end'].' '.$l['w_datetime_format']; ?></label>
</span>
<span class="formw">
<input type="text" name="enddate" id="enddate" value="<?php echo $enddate; ?>" size="20" maxlength="20" title="<?php echo $l['h_time_end']; ?>" />
<button name="enddate_trigger" id="enddate_trigger" title="<?php echo $l['w_calendar']; ?>">...</button>
<input type="hidden" name="x_enddate" id="x_enddate" value="^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2})\:([0-9]{1,2})\:([0-9]{1,2})$" />
<input type="hidden" name="xl_enddate" id="xl_enddate" value="<?php echo $l['w_time_end']; ?>" />
</span>
</div>

<div class="row">
<span class="label">&nbsp;</span>
<span class="formw">
<input type="submit" name="selectcategory" id="selectcategory" value="<?php echo $l['w_select']; ?>" />
</span>
</div>


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
echo F_allresults_user_table_header_element($user_id, 'testuser_creation_time', $nextorderdir, $l['w_time'], $l['w_time'], $order_field);
echo '<th title="'.$l['w_test'].'">'.$l['w_test'].'</th>'.K_NEWLINE;
echo F_allresults_user_table_header_element($user_id, 'total_score', $nextorderdir, $l['h_score_total'], $l['w_score'], $order_field);
echo '<th title="'.$l['h_answers_right'].'">'.$l['w_answers_right'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_answers_wrong'].'">'.$l['w_answers_wrong'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_unanswered'].'">'.$l['w_questions_unanswered'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_undisplayed'].'">'.$l['w_questions_undisplayed'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_questions_unrated'].'">'.$l['w_questions_unrated'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['w_status'].'">'.$l['w_status'].'</th>'.K_NEWLINE;
echo '<th title="'.$l['h_testcomment'].'">'.$l['w_comment'].'</th>'.K_NEWLINE;
?>
</tr>
<?php
// output users stats
$sqlr = 'SELECT testuser_id, test_id, test_name, testuser_creation_time, testuser_status, SUM(testlog_score) AS total_score 
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
$sqlr .= ' GROUP BY testuser_id, test_id, test_name, testuser_creation_time, testuser_status
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
		$usrtestdata = F_getUserTestStat($mr['test_id'], $user_id);
		echo '<tr>';
		echo '<td>';
		echo '<input type="checkbox" name="testuserid'.$itemcount.'" id="testuserid'.$itemcount.'" value="'.$mr['testuser_id'].'" title="'.$l['w_select'].'"';
		if (isset($_REQUEST['checkall']) AND ($_REQUEST['checkall'] == 1)) {
			echo ' checked="checked"';
		}
		echo ' />';
		echo '</td>'.K_NEWLINE;
		echo '<td><a href="tce_show_result_user.php?testuser_id='.$mr['testuser_id'].'&amp;test_id='.$mr['test_id'].'&amp;user_id='.$user_id.'" title="'.$l['h_view_details'].'">'.$itemcount.'</a></td>'.K_NEWLINE;
		
		echo '<td style="text-align:'.$tdalign.';">'.$mr['testuser_creation_time'].'</td>'.K_NEWLINE;
		echo '<td style="text-align:'.$tdalign.';"><a href="tce_show_result_allusers.php?test_id='.$mr['test_id'].'">'.$mr['test_name'].'</a></td>'.K_NEWLINE;
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
		echo '<td class="numeric">'.$usrtestdata['right'].' '.F_formatPercentage($usrtestdata['right'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['wrong'].' '.F_formatPercentage($usrtestdata['wrong'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['unanswered'].' '.F_formatPercentage($usrtestdata['unanswered'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['undisplayed'].' '.F_formatPercentage($usrtestdata['undisplayed'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
		echo '<td class="numeric">'.$usrtestdata['unrated'].' '.F_formatPercentage($usrtestdata['unrated'] / $usrtestdata['all']).'</td>'.K_NEWLINE;
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
			if (in_array($row, $calcpercent)) {
				echo '<td class="numeric">'.round(100 * $columns['score']).'%</td>'.K_NEWLINE;
				echo '<td class="numeric">'.round(100 * $columns['right']).'%</td>'.K_NEWLINE;
				echo '<td class="numeric">'.round(100 * $columns['wrong']).'%</td>'.K_NEWLINE;
				echo '<td class="numeric">'.round(100 * $columns['unanswered']).'%</td>'.K_NEWLINE;
				echo '<td class="numeric">'.round(100 * $columns['undisplayed']).'%</td>'.K_NEWLINE;
				echo '<td class="numeric">'.round(100 * $columns['unrated']).'%</td>'.K_NEWLINE;
			} else {
				echo '<td class="numeric">'.round($columns['score'], 3).'</td>'.K_NEWLINE;
				echo '<td class="numeric">'.round($columns['right'], 3).'</td>'.K_NEWLINE;
				echo '<td class="numeric">'.round($columns['wrong'], 3).'</td>'.K_NEWLINE;
				echo '<td class="numeric">'.round($columns['unanswered'], 3).'</td>'.K_NEWLINE;
				echo '<td class="numeric">'.round($columns['undisplayed'], 3).'</td>'.K_NEWLINE;
				echo '<td class="numeric">'.round($columns['unrated'], 3).'</td>'.K_NEWLINE;
			}
			echo '</tr>';
		}
	}
	echo '</table>'.K_NEWLINE;

	echo '<br />'.K_NEWLINE;
}

echo '</div>'.K_NEWLINE;
echo '<div class="row">'.K_NEWLINE;
// show buttons by case
if (isset($user_id) AND ($user_id > 0)) {
	echo '<a href="'.pdfUserResultsLink($user_id, $startdate, $enddate, $full_order_field).'" class="xmlbutton" title="'.$l['h_pdf'].'">'.$l['w_pdf'].'</a> ';
	echo '<a href="tce_csv_allresults_user.php?user_id='.$user_id.'&amp;startdate='.urlencode($startdate).'&amp;enddate='.urlencode($enddate).'&amp;order_field='.urlencode($full_order_field).'" class="xmlbutton" title="'.$l['h_csv_export'].'">CSV</a> ';
	echo '<a href="tce_xml_user_results.php?user_id='.$user_id.'&amp;startdate='.urlencode($startdate).'&amp;enddate='.urlencode($enddate).'&amp;order_field='.urlencode($full_order_field).'&amp;menu_mode=startlongprocess" class="xmlbutton" title="'.$l['h_xml_export'].'">XML</a> ';
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

echo '<div class="pagehelp">'.$l['hp_allresults_user'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

// calendar
echo '<script type="text/javascript">'.K_NEWLINE;
echo '//<![CDATA['.K_NEWLINE;
echo 'Calendar.setup({inputField: "startdate", ifFormat: "%Y-%m-%d %H:%M:%S", showsTime: "true", button: "startdate_trigger"});'.K_NEWLINE;
echo 'Calendar.setup({inputField: "enddate", ifFormat: "%Y-%m-%d %H:%M:%S", showsTime: "true", button: "enddate_trigger"});'.K_NEWLINE;
echo '//]]>'.K_NEWLINE;
echo '</script>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

// ------------------------------------------------------------

/**
 * Display table header element with order link.
 * @param string $user_id user ID
 * @param string $order_field name of table field
 * @param string $orderdir order direction
 * @param string $title title field of anchor link
 * @param string $name column name
 * @param string $current_order_field current order field name
 * @return table header element string
 */
function F_allresults_user_table_header_element($user_id, $order_field, $orderdir, $title, $name, $current_order_field="") {
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
	$str = '<th><a href="'.$_SERVER['SCRIPT_NAME'].'?user_id='.$user_id.'&amp;firstrow=0&amp;order_field='.$order_field.'&amp;orderdir='.$orderdir.'" title="'.$title.'">'.$name.'</a> '.$ord.'</th>'.K_NEWLINE;
	return $str;
}

/**
 * Returns an URL to open the PDF generator page for user's results.
 * @param string $mode PDF mode (1=all users results, 2=questions stats, 3=detailed report for single user 4=all users details)
 * @param int $user_id user ID
 * @param string $startdate start date in yyyy-mm-dd hh:mm:ss format
 * @param string $enddate end date in yyyy-mm-dd hh:mm:ss format
 * @param string $order_field ORDER BY portion of the SQL query
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
?>
