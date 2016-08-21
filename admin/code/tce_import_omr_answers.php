<?php
//============================================================+
// File name   : tce_import_omr_answers.php
// Begin       : 2011-05-20
// Last Update : 2014-05-14
//
// Description : Import test answers using OMR (Optical Mark Recognition)
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
 * Import test answers using OMR (Optical Mark Recognition) technique applied to images of scanned answer sheets.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2011-05-20
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_OMR_IMPORT;
$enable_calendar = true;
$max_omr_sheets = 10;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_omr_answers_importer'];
require_once('tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../../shared/code/tce_functions_auth_sql.php');
require_once('tce_functions_omr.php');
require_once('tce_functions_user_select.php');

if (isset($_REQUEST['user_id'])) {
    $user_id = intval($_REQUEST['user_id']);
    if (!F_isAuthorizedEditorForUser($user_id)) {
        F_print_error('ERROR', $l['m_authorization_denied']);
        exit;
    }
} else {
    $user_id = 0;
}
if (isset($_REQUEST['date'])) {
    $date = $_REQUEST['date'];
    $date_time = strtotime($date);
    $date = date(K_TIMESTAMP_FORMAT, $date_time);
} else {
    $date = date(K_TIMESTAMP_FORMAT);
}

if (!isset($_REQUEST['overwrite']) or (empty($_REQUEST['overwrite']))) {
    $overwrite = false;
} else {
    $overwrite = F_getBoolean($_REQUEST['overwrite']);
}

// process uploaded files
if (isset($menu_mode) and ($menu_mode == 'upload') and ($user_id > 0) and !empty($_FILES)) {
    // read OMR DATA page
    $omr_testdata = F_decodeOMRTestDataQRCode($_FILES['omrfile']['tmp_name'][0]);
    if ($omr_testdata === false) {
        F_print_error('ERROR', $l['m_omr_wrong_test_data']);
    } else {
        // read OMR ANSWER SHEET pages
        $num_questions = (count($omr_testdata) - 1);
        $num_pages = ceil($num_questions / 30);
        $omr_answers = array();
        for ($i = 1; $i <= $num_pages; ++$i) {
            if ($_FILES['omrfile']['error'][$i] == 0) {
                $answers_page = F_decodeOMRPage($_FILES['omrfile']['tmp_name'][$i]);
                if (($answers_page !== false) and !empty($answers_page)) {
                    $omr_answers += $answers_page;
                } else {
                    F_print_error('ERROR', '[OMR ANSWER SHEET '.$i.'] '.$l['m_omr_wrong_answer_sheet']);
                }
            } else {
                F_print_error('ERROR', '[OMR ANSWER SHEET '.$i.'] '.$l['m_omr_wrong_answer_sheet']);
            }
        }
        // sort answers
        ksort($omr_answers);
        // import answers
        if (F_importOMRTestData($user_id, $date, $omr_testdata, $omr_answers, $overwrite)) {
            F_print_error('MESSAGE', $l['m_import_ok'].': <a href="tce_show_result_user.php?testuser_id=32&test_id='.$omr_testdata[0].'&user_id='.$user_id.'" title="'.$l['t_result_user'].'" style="text-decoration:underline;color:#0000ff;">'.$l['w_results'].'</a>');
        } else {
            F_print_error('ERROR', $l['m_import_error']);
        }
    }
    // remove uploaded files
    for ($i = 0; $i <= $max_omr_sheets; ++$i) {
        if ($_FILES['omrfile']['error'][$i] == 0) {
            @unlink($_FILES['omrfile']['tmp_name'][$i]);
        }
    }
}

// -----------------------------------------------------------------------------

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_omrimport">'.K_NEWLINE;

// select user
echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="user_id">'.$l['w_user'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="user_id" id="user_id" size="0" onchange="">'.K_NEWLINE;
$sql = 'SELECT user_id, user_lastname, user_firstname, user_name FROM '.K_TABLE_USERS.' WHERE (user_id>1)';
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
    echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['user_id'].'"';
        //if ($m['user_id'] == $user_id) {
        //	echo ' selected="selected"';
        //}
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

// -----------------------------------------------------------------------------
// date
echo getFormRowTextInput('date', $l['w_date'], $l['w_date'].' '.$l['w_datetime_format'], '', $date, '', 19, false, true, false);

// OMR DATA page
echo getFormUploadFile('omrfile[]', 'omrdata', $l['w_omr_data_page'], $l['h_omr_data_page'], '');

// OMR ANSWER SHEET pages
for ($i = 1; $i < $max_omr_sheets; ++$i) {
    echo getFormUploadFile('omrfile[]', 'omrsheet'.$i, $l['w_omr_answer_sheet'].' '.$i, '', 'document.getElementById(\'divomrsheet'.($i+1).'\').style.display=\'block\';');
}
echo getFormUploadFile('omrfile[]', 'omrsheet'.$max_omr_sheets, $l['w_omr_answer_sheet'].' '.$max_omr_sheets, '', '');

echo getFormRowCheckBox('overwrite', $l['w_overwrite'], $l['h_omr_overwrite'], '', 1, $overwrite, false, '');

// -----------------------------------------------------------------------------

echo '<div class="row">'.K_NEWLINE;
echo '<br />'.K_NEWLINE;
// show upload button
F_submit_button('upload', $l['w_upload'], $l['h_submit_file']);
echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

// hide unused file upload fields
echo '<script type="text/javascript">'.K_NEWLINE;
echo '//<![CDATA['.K_NEWLINE;
echo 'for (i=2; i<='.$max_omr_sheets.'; i++) {document.getElementById(\'divomrsheet\'+i).style.display=\'none\';}'.K_NEWLINE;
echo '//]]>'.K_NEWLINE;
echo '</script>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_omr_answers_importer'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
