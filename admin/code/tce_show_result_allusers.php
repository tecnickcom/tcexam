<?php
//============================================================+
// File name   : tce_show_result_allusers.php
// Begin       : 2004-06-10
// Last Update : 2014-03-04
//
// Description : Display test results summary for all users.
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
//    Copyright (C) 2004-2014 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display test results summary for all users.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2004-06-10
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_result_all_users'];
$enable_calendar = true;
require_once('tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');
require_once('../../shared/code/tce_functions_auth_sql.php');
require_once('../../shared/code/tce_functions_statistics.php');
require_once('tce_functions_user_select.php');

$filter = 'sel=1';

if (isset($_REQUEST['selectcategory'])) {
    $changecategory = 1;
}
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
if (isset($_REQUEST['user_id'])) {
    $user_id = intval($_REQUEST['user_id']);
    $filter .= '&amp;user_id='.$user_id;
} else {
    $user_id = 0;
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

if (isset($_POST['lock'])) {
    $menu_mode = 'lock';
} elseif (isset($_POST['unlock'])) {
    $menu_mode = 'unlock';
} elseif (isset($_POST['extendtime'])) {
    $menu_mode = 'extendtime';
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

if (isset($menu_mode) and (!empty($menu_mode))) {
    for ($i = 1; $i <= $itemcount; $i++) {
        // for each selected item
        $keyname = 'testuserid'.$i;
        if (isset($$keyname)) {
            $testuser_id = $$keyname;
            switch ($menu_mode) {
                case 'delete':{
                    $sql = 'DELETE FROM '.K_TABLE_TEST_USER.'
						WHERE testuser_id='.$testuser_id.'';
                    if (!$r = F_db_query($sql, $db)) {
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
						WHERE testuser_id='.$testuser_id.'
						AND testuser_status<4';
                    if (!$ru = F_db_query($sqlu, $db)) {
                        F_display_db_error();
                    }
                    break;
                }
                case 'unlock':{
                    // update test mode to 1 = test unlocked
                    $sqlu = 'UPDATE '.K_TABLE_TEST_USER.'
						SET testuser_status=1
						WHERE testuser_id='.$testuser_id.'
						AND testuser_status<5';
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

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_resultallusers">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="test_id">'.$l['w_test'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="hidden" name="changecategory" id="changecategory" value="" />'.K_NEWLINE;
//echo '<select name="test_id" id="test_id" size="0" onchange="document.getElementById(\'form_resultallusers\').changecategory.value=1; document.getElementById(\'form_resultallusers\').submit()" title="'.$l['h_test'].'">'.K_NEWLINE;
echo '<select name="test_id" id="test_id" size="0" title="'.$l['h_test'].'">'.K_NEWLINE;
$sql = F_select_executed_tests_sql();
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

// link for user selection popup
$jsaction = 'selectWindow=window.open(\'tce_select_tests_popup.php?cid=test_id\', \'selectWindow\', \'dependent, height=600, width=800, menubar=no, resizable=yes, scrollbars=yes, status=no, toolbar=no\');return false;';
echo '<a href="#" onclick="'.$jsaction.'" class="xmlbutton" title="'.$l['w_select'].'">...</a>';

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
//echo '<select name="group_id" id="group_id" size="0" onchange="document.getElementById(\'form_resultallusers\').submit()">'.K_NEWLINE;
echo '<select name="group_id" id="group_id" size="0">'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_GROUPS.'';
if ($test_id > 0) {
    $sql .= ' WHERE group_id IN ('.$test_group_ids.')';
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
echo '<label for="user_id">'.$l['w_user'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
//echo '<select name="user_id" id="user_id" size="0" onchange="document.getElementById(\'form_resultallusers\').submit()">'.K_NEWLINE;
echo '<select name="user_id" id="user_id" size="0">'.K_NEWLINE;
$sql = 'SELECT user_id, user_lastname, user_firstname, user_name FROM '.K_TABLE_USERS.'';
if ($test_id > 0) {
    $sql .= ', '.K_TABLE_TEST_USER.' WHERE testuser_user_id=user_id AND testuser_test_id='.$test_id.'';
} elseif ($group_id > 0) {
    $sql .= ', '.K_TABLE_USERGROUP.' WHERE usrgrp_user_id=user_id AND usrgrp_group_id='.$group_id.' AND user_id>1';
} else {
    $sql .= ' WHERE user_id>1';
}
$sql .= ' GROUP BY user_id, user_lastname, user_firstname, user_name ORDER BY user_lastname, user_firstname, user_name';
if ($r = F_db_query($sql, $db)) {
    $countitem = 1;
    echo '<option value="0"';
    if ($user_id == 0) {
        echo ' selected="selected"';
    }
    echo '>&nbsp;-&nbsp;</option>'.K_NEWLINE;
    while ($m = F_db_fetch_array($r)) {
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
if (isset($_REQUEST['sel'])) {
    $data = F_getAllUsersTestStat($test_id, $group_id, $user_id, $startdate, $enddate, $full_order_field, false, $display_mode);
    if (isset($data['num_records'])) {
        $itemcount = $data['num_records'];
    }

    echo '<div class="rowl">'.K_NEWLINE;

    echo F_printTestResultStat($data, $nextorderdir, $order_field, $filter, false, $display_mode);

    if (!empty($data['testuser'])) {
        // check/uncheck all options
        echo '<span dir="'.$l['a_meta_dir'].'">';
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
    }

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
        // display statistics for modules, subjects, questions and answers
        echo '<div class="rowl">'.K_NEWLINE;
        echo F_printTestStat($test_id, $group_id, $user_id, $startdate, $enddate, 0, $data, $display_mode);
        echo '<br />'.K_NEWLINE;
        echo '</div>'.K_NEWLINE;
    }

    if ($itemcount > 0) {
        echo '<div class="row">'.K_NEWLINE;
        // show buttons by case
        echo '<a href="tce_xml_results.php?menu_mode=startlongprocess'.$filter.'" class="xmlbutton" title="'.$l['h_xml_export'].'">XML</a> ';
        echo '<a href="tce_xml_results.php?format=JSON&amp;menu_mode=startlongprocess'.$filter.'" class="xmlbutton" title="JSON">JSON</a> ';
        echo '<a href="tce_tsv_result_allusers.php?'.$filter.'&amp;order_field='.urlencode($order_field).'&amp;orderdir='.$orderdir.'" class="xmlbutton" title="'.$l['h_tsv_export'].'">TSV</a> ';
        echo '<a href="tce_pdf_results.php?mode=1'.$filter.'" class="xmlbutton" title="'.$l['h_pdf'].'">'.$l['w_pdf'].'</a> ';
        echo '<a href="tce_pdf_results.php?mode=4'.$filter.'" class="xmlbutton" title="'.$l['h_pdf_all'].'">'.$l['w_pdf_all'].'</a> ';
        if (K_DISPLAY_PDFTEXT_BUTTON) {
            echo '<a href="tce_pdf_results.php?mode=5'.$filter.'" class="xmlbutton" title="'.$l['h_pdf_all'].' - TEXT">'.$l['w_pdf'].' TEXT</a> ';
        }
        echo '<a href="tce_email_results.php?mode=1&amp;menu_mode=startlongprocess'.$filter.'" class="xmlbutton" title="'.$l['h_email_all_results'].'">'.$l['w_email_all_results'].'</a> ';
        echo '<a href="tce_email_results.php?mode=0&amp;menu_mode=startlongprocess'.$filter.'" class="xmlbutton" title="'.$l['h_email_all_results'].' + PDF">'.$l['w_email_all_results'].' + PDF</a> ';
        $custom_export = K_ENABLE_CUSTOM_EXPORT;
        if (!empty($custom_export)) {
            echo '<a href="tce_export_custom.php?menu_mode=startlongprocess'.$filter.'" class="xmlbutton" title="'.$custom_export.'">'.$custom_export.'</a> ';
        }
    }
}

echo '<input type="hidden" name="sel" id="sel" value="1" />'.K_NEWLINE;
echo '<input type="hidden" name="order_field" id="order_field" value="'.$order_field.'" />'.K_NEWLINE;
echo '<input type="hidden" name="orderdir" id="orderdir" value="'.$orderdir.'" />'.K_NEWLINE;
// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />'.K_NEWLINE;
echo '<input type="hidden" name="itemcount" id="itemcount" value="'.$itemcount.'>" />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_result_alluser'].'</div>'.K_NEWLINE;
echo '</div>';

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
