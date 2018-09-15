<?php
//============================================================+
// File name   : tce_edit_sslcerts.php
// Begin       : 2013-07-04
// Last Update : 2013-07-09
//
// Description : Upload and edit SSL certificates.
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
 * Upload and edit SSL certificates.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2013-07-04
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_SSLCERT;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_sslcerts'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_authorization.php');

// set default values
if (!isset($ssl_enabled) or (empty($ssl_enabled))) {
    $ssl_enabled = false;
} else {
    $ssl_enabled = F_getBoolean($ssl_enabled);
}
if (isset($ssl_name)) {
    $ssl_name = utrim($ssl_name);
} else {
    $ssl_name = '';
}
if (isset($ssl_user_id)) {
    $ssl_user_id = intval($ssl_user_id);
} else {
    $ssl_user_id = intval($_SESSION['session_user_id']);
}
if (isset($_REQUEST['ssl_id']) and ($_REQUEST['ssl_id'] > 0)) {
    $ssl_id = intval($_REQUEST['ssl_id']);
    // check user's authorization for this certificate
    if (!F_isAuthorizedUser(K_TABLE_SSLCERTS, 'ssl_id', $ssl_id, 'ssl_user_id')) {
        F_print_error('ERROR', $l['m_authorization_denied']);
        exit;
    }
} else {
    $ssl_id = 0;
}

// extract hash and end date from uploaded file
$ssl_hash = '';
$ssl_end_date = '';
if (isset($_FILES['userfile']['name']) and (!empty($_FILES['userfile']['name']))) {
    require_once('../code/tce_functions_upload.php');
    // upload file
    $uploadedfile = F_upload_file('userfile', K_PATH_CACHE);
    if ($uploadedfile !== false) {
        $cert = file_get_contents(K_PATH_CACHE.$uploadedfile);
        $pkcs12 = (substr($uploadedfile, -4) == '.pfx');
        list($ssl_hash, $ssl_end_date) = F_getSSLCertificateHash($cert, $pkcs12);
        //remove certificate file
        unlink(K_PATH_CACHE.$uploadedfile);
    }
}

switch ($menu_mode) {
    case 'delete':{
        F_stripslashes_formfields();
        // check if this record is used
        if (!F_check_unique(K_TABLE_TEST_SSLCERTS, 'tstssl_ssl_id='.$ssl_id.'')) {
            //this record will be only disabled and not deleted because it's used
            $sql = 'UPDATE '.K_TABLE_QUESTIONS.' SET
				ssl_enabled=\'0\'
				WHERE ssl_id='.$ssl_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error();
            }
            F_print_error('WARNING', $l['m_disabled_vs_deleted']);
        } else {
            // ask confirmation
            F_print_error('WARNING', $l['m_delete_confirm']);
            echo '<div class="confirmbox">'.K_NEWLINE;
            echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
            echo '<div>'.K_NEWLINE;
            echo '<input type="hidden" name="ssl_id" id="ssl_id" value="'.$ssl_id.'" />'.K_NEWLINE;
            echo '<input type="hidden" name="ssl_name" id="ssl_name" value="'.$ssl_name.'" />'.K_NEWLINE;
            F_submit_button('forcedelete', $l['w_delete'], $l['h_delete']);
            F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
            echo '</div>'.K_NEWLINE;
            echo '</form>'.K_NEWLINE;
            echo '</div>'.K_NEWLINE;
        }
        break;
    }

    case 'forcedelete':{
        F_stripslashes_formfields();
        if ($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
            $sql = 'DELETE FROM '.K_TABLE_SSLCERTS.' WHERE ssl_id='.$ssl_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                $ssl_id=false;
                F_print_error('MESSAGE', $ssl_name.': '.$l['m_deleted']);
            }
        }
        break;
    }

    case 'update':{ // Update
        // check if the confirmation chekbox has been selected
        if (!isset($_REQUEST['confirmupdate']) or ($_REQUEST['confirmupdate'] != 1)) {
            F_print_error('WARNING', $l['m_form_missing_fields'].': '.$l['w_confirm'].' &rarr; '.$l['w_update']);
            F_stripslashes_formfields();
            break;
        }
        if ($formstatus = F_check_form_fields()) {
            // check if name is unique
            if (!F_check_unique(K_TABLE_SSLCERTS, 'ssl_name=\''.F_escape_sql($db, $ssl_name).'\'', 'ssl_id', $ssl_id)) {
                F_print_error('WARNING', $l['m_duplicate_name']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            if ($_SESSION['session_user_level'] >= K_AUTH_ADMINISTRATOR) {
                $ssl_user_id = intval($ssl_user_id);
            } else {
                $ssl_user_id = intval($_SESSION['session_user_id']);
            }
            $sql = 'UPDATE '.K_TABLE_SSLCERTS.' SET
				ssl_name=\''.F_escape_sql($db, $ssl_name).'\',
				ssl_enabled=\''.intval($ssl_enabled).'\',
				ssl_user_id=\''.$ssl_user_id.'\'
				WHERE ssl_id='.$ssl_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                F_print_error('MESSAGE', $l['m_updated']);
            }
        }
        break;
    }

    case 'add':{ // Add
        if (($formstatus = F_check_form_fields()) and (strlen($ssl_hash) == 32)) {
            // check if name is unique
            if (!F_check_unique(K_TABLE_SSLCERTS, 'ssl_name=\''.F_escape_sql($db, $ssl_name).'\'')) {
                F_print_error('WARNING', $l['m_duplicate_name']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            if ($_SESSION['session_user_level'] >= K_AUTH_ADMINISTRATOR) {
                $ssl_user_id = intval($ssl_user_id);
            } else {
                $ssl_user_id = intval($_SESSION['session_user_id']);
            }
            $sql = 'INSERT INTO '.K_TABLE_SSLCERTS.' (
				ssl_name,
				ssl_hash,
				ssl_end_date,
				ssl_enabled,
				ssl_user_id
				) VALUES (
				\''.F_escape_sql($db, $ssl_name).'\',
				\''.$ssl_hash.'\',
				\''.$ssl_end_date.'\',
				\''.intval($ssl_enabled).'\',
				\''.$ssl_user_id.'\'
				)';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                $ssl_id = F_db_insert_id($db, K_TABLE_SSLCERTS, 'ssl_id');
            }
        }
        break;
    }

    case 'clear':{ // Clear form fields
        $ssl_name = '';
        $ssl_hash = '';
        $ssl_end_date = '';
        $ssl_enabled = true;
        $ssl_user_id = intval($_SESSION['session_user_id']);
        break;
    }

    default :{
        break;
    }
} //end of switch

// --- Initialize variables
if ($formstatus) {
    if ($menu_mode != 'clear') {
        if (!isset($ssl_id) or empty($ssl_id)) {
            $ssl_id = 0;
            $ssl_name = '';
            $ssl_hash = '';
            $ssl_end_date = '';
            $ssl_enabled = true;
            $ssl_user_id = intval($_SESSION['session_user_id']);
        } else {
            $sql =  'SELECT * FROM '.K_TABLE_SSLCERTS.' WHERE ssl_id='.$ssl_id.' LIMIT 1';
            if ($r = F_db_query($sql, $db)) {
                if ($m = F_db_fetch_array($r)) {
                    $ssl_id = $m['ssl_id'];
                    $ssl_name = $m['ssl_name'];
                    $ssl_hash = $m['ssl_hash'];
                    $ssl_end_date = $m['ssl_end_date'];
                    $ssl_enabled = F_getBoolean($m['ssl_enabled']);
                    $ssl_user_id = intval($m['ssl_user_id']);
                } else {
                    $ssl_name = '';
                    $ssl_hash = '';
                    $ssl_end_date = '';
                    $ssl_enabled = true;
                    $ssl_user_id = intval($_SESSION['session_user_id']);
                }
            } else {
                F_display_db_error();
            }
        }
    }
}

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_importsslcert">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="ssl_id">'.$l['w_sslcert'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="ssl_id" id="ssl_id" size="0" onchange="document.getElementById(\'form_importsslcert\').submit()" title="'.$l['w_sslcert'].'">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($ssl_id == 0) {
    echo ' selected="selected"';
}
echo '>+</option>'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_SSLCERTS.' ORDER BY ssl_name';
if ($r = F_db_query($sql, $db)) {
    $countitem = 1;
    while ($m = F_db_fetch_array($r)) {
        echo '<option value="'.$m['ssl_id'].'"';
        if ($m['ssl_id'] == $ssl_id) {
            echo ' selected="selected"';
        }
        echo '>'.$countitem.'. ['.$m['ssl_id'].']';
        echo ' '.htmlspecialchars($m['ssl_name'], ENT_NOQUOTES, $l['a_meta_charset']);
        echo ' ('.htmlspecialchars($m['ssl_end_date'], ENT_NOQUOTES, $l['a_meta_charset']).')';
        echo '&nbsp;</option>'.K_NEWLINE;
        $countitem++;
    }
    if ($countitem == 1) {
        echo '<option value="0">&nbsp;</option>'.K_NEWLINE;
    }
} else {
    echo '</select></span></div>'.K_NEWLINE;
    F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo getFormRowTextInput('ssl_name', $l['w_name'], $l['w_name'], '', $ssl_name, '', 255, false, false, false, '');

if (!isset($ssl_id) or ($ssl_id <= 0)) {
    echo '<div class="row">'.K_NEWLINE;
    echo '<span class="label">'.K_NEWLINE;
    echo '<label for="userfile">'.$l['w_upload_file'].'</label>'.K_NEWLINE;
    echo '</span>'.K_NEWLINE;
    echo '<span class="formw">'.K_NEWLINE;
    echo '<input type="hidden" name="MAX_FILE_SIZE" value="'.K_MAX_UPLOAD_SIZE.'" />'.K_NEWLINE;
    echo '<input type="file" name="userfile" id="userfile" size="20" title="'.$l['h_upload_file'].'" />'.K_NEWLINE;
    echo '</span>'.K_NEWLINE;
    echo '&nbsp;'.K_NEWLINE;
    echo '</div>'.K_NEWLINE;
}

echo getFormRowCheckBox('ssl_enabled', $l['w_enabled'], $l['h_enabled'], '', 1, $ssl_enabled, false, '');


echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($ssl_id) and ($ssl_id > 0)) {
    echo '<span style="background-color:#999999;">';
    echo '<input type="checkbox" name="confirmupdate" id="confirmupdate" value="1" title="confirm &rarr; update" />';
    F_submit_button('update', $l['w_update'], $l['h_update']);
    echo '</span>';
    //F_submit_button('add', $l['w_add'], $l['h_add']);
    F_submit_button('delete', $l['w_delete'], $l['h_delete']);
} else {
    F_submit_button('add', $l['w_add'], $l['h_add']);
}
F_submit_button('clear', $l['w_clear'], $l['h_clear']);

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_import_ssl_certificates'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

// ---------------------------------------------------------------------


//============================================================+
// END OF FILE
//============================================================+
