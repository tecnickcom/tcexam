<?php
//============================================================+
// File name   : tce_import_omr_bulk.php
// Begin       : 2012-07-31
// Last Update : 2014-05-14
//
// Description : Import in bulk test answers using OMR 
//               (Optical Mark Recognition)
//               technique applied to images of scanned answer sheets.
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
//    Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Import in bulk test answers using OMR (Optical Mark Recognition) technique applied to images of scanned answer sheets.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2012-07-31
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_OMR_IMPORT;
$enable_calendar = true;
$max_omr_sheets = 10;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_omr_bulk_importer'];
require_once('tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_auth_sql.php');
require_once('tce_functions_omr.php');
require_once('tce_functions_user_select.php');

if (isset($_REQUEST['date'])) {
    $date = $_REQUEST['date'];
    $date_time = strtotime($date);
    $date = date(K_TIMESTAMP_FORMAT, $date_time);
} else {
    $date = date(K_TIMESTAMP_FORMAT);
}

if (isset($_REQUEST['omrdir']) and (strpos($_REQUEST['omrdir'], K_PATH_CACHE.'OMR') == 0)) {
    $omrdir = $_REQUEST['omrdir'];
    if (strpos($omrdir, '://') !== false) {
        F_print_error('ERROR', 'Invalid omrdir!', true);
    }
} else {
    $omrdir = K_PATH_CACHE.'OMR/';
}

if (!isset($_REQUEST['overwrite']) or (empty($_REQUEST['overwrite']))) {
    $overwrite = false;
} else {
    $overwrite = F_getBoolean($_REQUEST['overwrite']);
}

// process OMR files on the specified directory
if (isset($menu_mode) and ($menu_mode == 'upload') and F_file_exists($omrdir)) {
    $logfilename = 'log_import_omr_'.time().'.txt';
    $logfile = K_PATH_CACHE.'OMR/'.$logfilename;
    $dirhdl = @opendir($omrdir);
    if ($dirhdl !== false) {
        while ($file = readdir($dirhdl)) {
            if (($file != '.') and ($file != '..')) {
                $filename = $omrdir.$file;
                $matches = array();
                if (!is_dir($filename) and preg_match('/OMR_([^_]+)_QR.([a-zA-Z]+)/', $file, $matches)) {
                    // read OMR DATA page
                    $omr_testdata = F_decodeOMRTestDataQRCode($filename);
                    if ($omr_testdata === false) {
                        F_print_error('ERROR', $l['m_omr_wrong_test_data']);
                        file_put_contents($logfile, 'ERROR'."\t".$file."\t".'UNABLE TO DECODE'."\n", FILE_APPEND);
                    } else {
                        file_put_contents($logfile, 'OK'."\t".$file."\t".'SUCCESSFULLY DECODED'."\n", FILE_APPEND);
                        // read OMR ANSWER SHEET pages
                        $num_questions = (count($omr_testdata) - 1);
                        $num_pages = ceil($num_questions / 30);
                        $omr_answers = array();
                        for ($i = 1; $i <= $num_pages; ++$i) {
                            $answerfile = 'OMR_'.$matches[1].'_A'.$i.'.'.$matches[2];
                            if (F_file_exists($omrdir.$answerfile)) {
                                $answers_page = F_decodeOMRPage($omrdir.$answerfile);
                                if (($answers_page !== false) and !empty($answers_page)) {
                                    $omr_answers += $answers_page;
                                    file_put_contents($logfile, 'OK'."\t".$answerfile."\t".'SUCCESSFULLY DECODED'."\n", FILE_APPEND);
                                } else {
                                    F_print_error('ERROR', '[OMR ANSWER SHEET '.$answerfile.'] '.$l['m_omr_wrong_answer_sheet']);
                                    file_put_contents($logfile, 'ERROR'."\t".$answerfile."\t".'UNABLE TO DECODE'."\n", FILE_APPEND);
                                }
                            } else {
                                F_print_error('ERROR', '[OMR ANSWER SHEET '.$answerfile.'] '.$l['m_omr_wrong_answer_sheet']);
                                file_put_contents($logfile, 'ERROR'."\t".$answerfile."\t".'MISSING IMAGE FILE'."\n", FILE_APPEND);
                            }
                        }
                        // sort answers
                        ksort($omr_answers);
                        // get user ID from user registration code
                        $user_id = F_getUIDfromRegnum($matches[1]);
                        // import answers
                        if (($user_id > 0) and F_isAuthorizedEditorForUser($user_id) and F_importOMRTestData($user_id, $date, $omr_testdata, $omr_answers, $overwrite)) {
                            F_print_error('MESSAGE', '['.$matches[1].'] '.$l['m_import_ok'].': <a href="tce_show_result_user.php?testuser_id=32&test_id='.$omr_testdata[0].'&user_id='.$user_id.'" title="'.$l['t_result_user'].'" style="text-decoration:underline;color:#0000ff;">'.$l['w_results'].'</a>');
                            file_put_contents($logfile, 'OK'."\t".$matches[1]."\t".'SUCCESSFULLY IMPORTED - UID: '.$user_id."\n", FILE_APPEND);
                        } else {
                            F_print_error('ERROR', '['.$matches[1].'] '.$l['m_import_error']);
                            file_put_contents($logfile, 'ERROR'."\t".$matches[1]."\t".'UNABLE TO IMPORT - UID: '.$user_id."\n", FILE_APPEND);
                        }
                    }
                } // if QR file
            }
        }
        // print a link to log file
        F_print_error('MESSAGE', 'LOGFILE: <a href="tce_filemanager.php?d='.urlencode(K_PATH_CACHE.'OMR/').'&f='.urlencode($logfile).'&v=1" title="'.$l['w_select'].'">'.$logfilename.'</a>');
    }
}

// -----------------------------------------------------------------------------

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_omrimport">'.K_NEWLINE;

// -----------------------------------------------------------------------------
// date
echo getFormRowTextInput('date', $l['w_date'], $l['w_date'].' '.$l['w_datetime_format'], '', $date, '', 19, false, true, false);

if (F_file_exists(K_PATH_CACHE.'OMR')) {
    // directory containing files to import
    $dirs = array('OMR/');
    $dirhdl = @opendir(K_PATH_CACHE.'OMR/');
    if ($dirhdl !== false) {
        while ($file = readdir($dirhdl)) {
            if (($file != '.') and ($file != '..')) {
                $filename = K_PATH_CACHE.'OMR/'.$file.'/';
                if (is_dir($filename)) {
                    $dirs[$filename] = 'OMR/'.$file.'/';
                }
            }
        }
        // sort files alphabetically
        natcasesort($dirs);
        echo getFormRowSelectBox('omrdir', $l['w_omr_dir'], $l['h_omr_dir'], '', $omrdir, $dirs, '');
    }
}

echo getFormRowCheckBox('overwrite', $l['w_overwrite'], $l['h_omr_overwrite'], '', 1, $overwrite, false, '');

// -----------------------------------------------------------------------------

echo '<div class="row">'.K_NEWLINE;
echo '<br />'.K_NEWLINE;
// show upload button
F_submit_button('upload', $l['w_upload'], $l['h_submit_file']);
echo '</div>'.K_NEWLINE;
echo F_getCSRFTokenField().K_NEWLINE;
echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_omr_bulk_importer'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
