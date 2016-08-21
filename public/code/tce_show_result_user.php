<?php
//============================================================+
// File name   : tce_show_result_user.php
// Begin       : 2004-06-10
// Last Update : 2014-01-23
//
// Description : Display test results to the current user.
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
 * Display test results to the current user.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2004-06-10
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_PUBLIC_TEST_RESULTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_test_results'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_test.php');
require_once('../../shared/code/tce_functions_test_stats.php');

$user_id = intval($_SESSION['session_user_id']);

if (isset($_REQUEST['testuser_id']) and ($_REQUEST['testuser_id'] > 0)) {
    $testuser_id = intval($_REQUEST['testuser_id']);
} else {
    header('Location: index.php'); //redirect browser to public main page
    exit;
}
if (isset($_REQUEST['test_id']) and ($_REQUEST['test_id'] > 0)) {
    $test_id = intval($_REQUEST['test_id']);
} else {
    header('Location: index.php'); //redirect browser to public main page
    exit;
}

// security check
$checkid = 0;
$sqlt = 'SELECT testuser_user_id FROM '.K_TABLE_TEST_USER.' WHERE testuser_test_id='.$test_id.' AND testuser_id='.$testuser_id.'';
if ($rt = F_db_query($sqlt, $db)) {
    if ($mt = F_db_fetch_assoc($rt)) {
        $checkid = $mt['testuser_user_id'];
    }
} else {
    F_display_db_error();
}
if ($user_id != $checkid) {
    header('Location: index.php'); //redirect browser to public main page
    exit;
}

// get user's test stats
$userdata = F_getUserData($user_id);
$teststat = F_getTestStat($test_id, 0, $user_id, 0, 0, $testuser_id);
$teststat['testinfo'] = F_getUserTestStat($test_id, $user_id, $testuser_id);
$test_id = $teststat['testinfo']['test_id'];

if (!F_getBoolean($teststat['testinfo']['test_results_to_users'])) {
    header('Location: index.php'); //redirect browser to public main page
    exit;
}
//lock user's test
F_lockUserTest($test_id, $_SESSION['session_user_id']);

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;

$usr_all = htmlspecialchars($userdata['user_lastname'].' '.$userdata['user_firstname'].' - '.$userdata['user_name'].'', ENT_NOQUOTES, $l['a_meta_charset']);
echo getFormDescriptionLine($l['w_user'].':', $l['w_user'], $usr_all);

$test_all = '<strong>'.htmlspecialchars($teststat['testinfo']['test_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</strong><br />'.K_NEWLINE;
$test_all .= htmlspecialchars($teststat['testinfo']['test_description'], ENT_NOQUOTES, $l['a_meta_charset']);
echo getFormDescriptionLine($l['w_test'].':', $l['w_test'], $test_all);

echo getFormDescriptionLine($l['w_time_begin'].':', $l['h_time_begin'], $teststat['testinfo']['user_test_start_time']);
echo getFormDescriptionLine($l['w_time_end'].':', $l['h_time_end'], $teststat['testinfo']['user_test_end_time']);

if (!isset($teststat['testinfo']['user_test_end_time']) or ($teststat['testinfo']['user_test_end_time'] <= 0) or (strtotime($teststat['testinfo']['user_test_end_time']) < strtotime($teststat['testinfo']['user_test_start_time']))) {
    $time_diff = $teststat['testinfo']['test_duration_time'] * 60;
} else {
    $time_diff = strtotime($teststat['testinfo']['user_test_end_time']) - strtotime($teststat['testinfo']['user_test_start_time']); //sec
}
$time_diff = gmdate('H:i:s', $time_diff);
echo getFormDescriptionLine($l['w_test_time'].':', $l['w_test_time'], $time_diff);

$passmsg = '';
if ($teststat['testinfo']['test_score_threshold'] > 0) {
    if (isset($teststat['testinfo']['user_score']) and ($teststat['testinfo']['user_score'] >= $teststat['testinfo']['test_score_threshold'])) {
        $passmsg = ' - '.$l['w_passed'];
    } else {
        $passmsg = ' - '.$l['w_not_passed'];
    }
}
if ($teststat['testinfo']['test_max_score'] > 0) {
    $score_all = $teststat['testinfo']['user_score'].' / '.$teststat['testinfo']['test_max_score'].' ('.round(100 * $teststat['testinfo']['user_score'] / $teststat['testinfo']['test_max_score']).'%)'.$passmsg;
} else {
    $score_all = $teststat['testinfo']['user_score'].$passmsg;
}
echo getFormDescriptionLine($l['w_score'].':', $l['h_score_total'], $score_all);

$score_right_all = $teststat['qstats']['right'].' / '.$teststat['qstats']['recurrence'].' ('.$teststat['qstats']['right_perc'].'%)';
echo getFormDescriptionLine($l['w_answers_right'].':', $l['h_answers_right'], $score_right_all);
echo getFormDescriptionLine($l['w_comment'].':', $l['h_testcomment'], F_decode_tcecode($teststat['testinfo']['user_comment']));

if (F_getBoolean($teststat['testinfo']['test_report_to_users'])) {
    echo '<div class="rowl">'.K_NEWLINE;
    echo F_printUserTestStat($testuser_id);
    echo '</div>'.K_NEWLINE;

    // print statistics for modules and subjects
    echo '<div class="rowl">'.K_NEWLINE;
    echo '<hr />'.K_NEWLINE;
    echo '<h2>'.$l['w_stats'].'</h2>';
    echo F_printTestStat($test_id, 0, $user_id, 0, 0, $testuser_id, $teststat, 1, true);
    echo '<hr />'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;

    if (K_ENABLE_PUBLIC_PDF) {
        echo '<div class="row">'.K_NEWLINE;
        // PDF button
        echo '<a href="tce_pdf_results.php?mode=3&amp;test_id='.$test_id.'&amp;user_id='.$user_id.'&amp;testuser_id='.$testuser_id.'" class="xmlbutton" title="'.$l['h_pdf'].'">'.$l['w_pdf'].'</a> ';
        echo '</div>'.K_NEWLINE;
    }
}

echo '</div>'.K_NEWLINE;

echo '<a href="index.php" title="'.$l['h_index'].'">&lt; '.$l['w_index'].'</a>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_result_user'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
