<?php
//============================================================+
// File name   : tce_test_allresults.php
// Begin       : 2004-06-10
// Last Update : 2014-01-21
//
// Description : Display test results summary.
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
//    Copyright (C) 2004-2014  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display test results summary.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2012-12-19
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_PUBLIC_TEST_RESULTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_all_results_user'];
$enable_calendar = true;
require_once('tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('../../shared/code/tce_functions_auth_sql.php');
require_once('../../shared/code/tce_functions_statistics.php');

$user_id = intval($_SESSION['session_user_id']);
$filter = 'user_id='.$user_id;
if (isset($_REQUEST['test_id']) and ($_REQUEST['test_id'] > 0)) {
    $test_id = intval($_REQUEST['test_id']);
    // check user's authorization
    if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
        F_print_error('ERROR', $l['m_authorization_denied']);
        exit;
    }
    $filter .= '&amp;test_id='.$test_id.'';
    $test_group_ids = F_getTestGroups($test_id);
} else {
    $test_id = 0;
}
if (isset($_REQUEST['selectcategory'])) {
    $changecategory = 1;
}
if (isset($_REQUEST['group_id']) and !empty($_REQUEST['group_id'])) {
    $group_id = intval($_REQUEST['group_id']);
    $filter .= '&amp;group_id='.$group_id.'';
} else {
    $group_id = 0;
}
// filtering options
if (isset($_REQUEST['startdate'])) {
    $startdate = $_REQUEST['startdate'];
    $startdate_time = strtotime($startdate);
    $startdate = date(K_TIMESTAMP_FORMAT, $startdate_time);
} else {
    $startdate = date('Y').'-01-01 00:00:00';
}
$filter .= '&amp;startdate='.urlencode($startdate);
if (isset($_REQUEST['enddate'])) {
    $enddate = $_REQUEST['enddate'];
    $enddate_time = strtotime($enddate);
    $enddate = date(K_TIMESTAMP_FORMAT, $enddate_time);
} else {
    $enddate = date('Y').'-12-31 23:59:59';
}
$filter .= '&amp;enddate='.urlencode($enddate).'';

$detail_modes = array($l['w_disabled'], $l['w_minimum'], $l['w_module'], $l['w_subject'], $l['w_question'], $l['w_answer']);
if (isset($_REQUEST['display_mode'])) {
    $display_mode = max(0, min(5, intval($_REQUEST['display_mode'])));
    $filter .= '&amp;display_mode='.$display_mode;
} else {
    $display_mode = 0;
}
$filter .= '&amp;display_mode='.$display_mode;

if (isset($_REQUEST['show_graph'])) {
    $show_graph = intval($_REQUEST['show_graph']);
    $filter .= '&amp;show_graph='.$show_graph;
    if ($show_graph and ($display_mode == 0)) {
        $display_mode = 1;
    }
} else {
    $show_graph = 0;
}


if (isset($_REQUEST['order_field']) and !empty($_REQUEST['order_field']) and (in_array($_REQUEST['order_field'], array('testuser_creation_time', 'testuser_end_time', 'user_name', 'user_lastname', 'user_firstname', 'total_score', 'testuser_test_id')))) {
    $order_field = $_REQUEST['order_field'];
} else {
    $order_field = 'total_score, user_lastname, user_firstname';
}
$filter .= '&amp;order_field='.urlencode($order_field).'';
if (!isset($_REQUEST['orderdir']) or empty($_REQUEST['orderdir'])) {
    $orderdir = 0;
    $nextorderdir = 1;
    $full_order_field = $order_field;
} else {
    $orderdir = 1;
    $nextorderdir = 0;
    $full_order_field = $order_field.' DESC';
}
$filter .= '&amp;orderdir='.$orderdir.'';

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_resultallusers">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="test_id">'.$l['w_test'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="hidden" name="changecategory" id="changecategory" value="" />'.K_NEWLINE;
echo '<select name="test_id" id="test_id" size="0" onchange="document.getElementById(\'form_resultallusers\').changecategory.value=1; document.getElementById(\'form_resultallusers\').submit()" title="'.$l['h_test'].'">'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_TESTS.' WHERE test_id IN ('.F_getTestIDResults($test_id, $user_id).')  ORDER BY test_begin_time DESC, test_name';
if ($r = F_db_query($sql, $db)) {
    echo '<option value="0"';
    if ($test_id == 0) {
        echo ' selected="selected"';
    }
    echo '>&nbsp;-&nbsp;</option>'.K_NEWLINE;
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['test_id'].'"';
        if ($m['test_id'] == $test_id) {
            echo ' selected="selected"';
        }
        echo '>'.substr($m['test_begin_time'], 0, 10).' '.htmlspecialchars($m['test_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
    }
} else {
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;

echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectcategory');

echo getFormRowTextInput('startdate', $l['w_time_begin'], $l['w_time_begin'].' '.$l['w_datetime_format'], '', $startdate, '', 19, false, true, false);
echo getFormRowTextInput('enddate', $l['w_time_end'], $l['w_time_end'].' '.$l['w_datetime_format'], '', $enddate, '', 19, false, true, false);

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="group_id">'.$l['w_group'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="group_id" id="group_id" size="0" onchange="document.getElementById(\'form_resultallusers\').submit()">'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_GROUPS.', '.K_TABLE_USERGROUP.' WHERE usrgrp_group_id=group_id AND usrgrp_user_id='.$user_id.'';
if ($test_id > 0) {
    $sql .= ' AND group_id IN ('.$test_group_ids.')';
}
$sql .= ' ORDER BY group_name';
if ($r = F_db_query($sql, $db)) {
    echo '<option value="0"';
    if ($group_id == 0) {
        echo ' selected="selected"';
    }
    echo '>&nbsp;-&nbsp;</option>'.K_NEWLINE;
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['group_id'].'"';
        if ($m['group_id'] == $group_id) {
            echo ' selected="selected"';
        }
        echo '>'.htmlspecialchars($m['group_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
    }
} else {
    echo '</select></span></div>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectgroup');

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="display_mode">'.$l['w_stats'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="display_mode" id="display_mode" size="0" title="'.$l['w_mode'].'">'.K_NEWLINE;
foreach ($detail_modes as $key => $dmode) {
    echo '<option value="'.$key.'"';
    if ($key == $display_mode) {
        echo ' selected="selected"';
    }
    echo '>'.htmlspecialchars($dmode, ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('display_mode');

echo getFormRowCheckBox('show_graph', $l['w_graph'], $l['w_result_graph'], '', 1, $show_graph, false, '');

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">&nbsp;</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="submit" name="selectcategory" id="selectcategory" value="'.$l['w_select'].'" />'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="row"><hr /></div>'.K_NEWLINE;

// ---------------------------------------------------------------------
$itemcount = 0;

$data = F_getAllUsersTestStat($test_id, $group_id, $user_id, $startdate, $enddate, $full_order_field, true, $display_mode);
if (isset($data['num_records'])) {
    $itemcount = $data['num_records'];
}

echo '<div class="rowl">'.K_NEWLINE;
echo F_printTestResultStat($data, $nextorderdir, $order_field, $filter, true, $display_mode);
echo '<br />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

// display svg graph
if ($show_graph and isset($data['svgpoints']) and (preg_match_all('/[x]/', $data['svgpoints'], $match) > 1)) {
    $w = 800;
    $h = 300;
    echo '<div class="row">'.K_NEWLINE;
    echo '<hr />'.K_NEWLINE;
    // legend
    echo '<div style="font-size:90%;"><br /><span style="background-color:#ff0000;color:#ffffff;">&nbsp;'.$l['w_score'].'&nbsp;</span> <span style="background-color:#0000ff;color:#ffffff;">&nbsp;'.$l['w_answers_right'].'&nbsp;</span> / <span style="background-color:#dddddd;color:#000000;">&nbsp;'.$l['w_tests'].'&nbsp;</span></div>';
    echo '<img src="../../shared/code/tce_svg_graph.php?w='.$w.'&amp;h='.$h.'&amp;p='.substr($data['svgpoints'], 1).'" width="'.$w.'" height="'.$h.'" alt="'.$l['w_result_graph'].'" />'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;
}

if ($display_mode > 1) {
    // display statistics for modules and subjects
    echo '<div class="rowl">'.K_NEWLINE;
    echo F_printTestStat($test_id, $group_id, $user_id, $startdate, $enddate, 0, $data, $display_mode, true);
    echo '<br />'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;
}

if ($itemcount > 0) {
    echo '<div class="row">'.K_NEWLINE;
    // show buttons by case
    echo '<a href="tce_pdf_results.php?mode=1'.$filter.'" class="xmlbutton" title="'.$l['h_pdf'].'">'.$l['w_pdf'].'</a> ';
    echo '<a href="tce_pdf_results.php?mode=4'.$filter.'" class="xmlbutton" title="'.$l['h_pdf_all'].'">'.$l['w_pdf_all'].'</a> ';

    echo '<input type="hidden" name="order_field" id="order_field" value="'.$order_field.'" />'.K_NEWLINE;
    echo '<input type="hidden" name="orderdir" id="orderdir" value="'.$orderdir.'" />'.K_NEWLINE;
    // comma separated list of required fields
    echo '<input type="hidden" name="ff_required" id="ff_required" value="" />'.K_NEWLINE;
    echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />'.K_NEWLINE;
    echo '<input type="hidden" name="itemcount" id="itemcount" value="'.$itemcount.'>" />'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;
}
echo '</form>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_allresults_user'].'</div>'.K_NEWLINE;
echo '</div>';

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
