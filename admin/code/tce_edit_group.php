<?php
//============================================================+
// File name   : tce_edit_group.php
// Begin       : 2006-03-11
// Last Update : 2012-12-03
//
// Description : Edit users' groups.
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
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display form to edit users' groups.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2006-03-11
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_GROUPS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_group_editor'];
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('../code/tce_functions_user_select.php');

$user_id = intval($_SESSION['session_user_id']);
$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);

if (isset($_REQUEST['group_id'])) {
    $group_id = intval($_REQUEST['group_id']);
    if (!F_isAuthorizedEditorForGroup($group_id)) {
        F_print_error('ERROR', $l['m_authorization_denied']);
        exit;
    }
} else {
    $group_id = 0;
}
if (isset($_REQUEST['group_name'])) {
    $group_name = $_REQUEST['group_name'];
} else {
    $group_name = '';
}

switch ($menu_mode) { // process submitted data

    case 'delete':{
        F_stripslashes_formfields(); // ask confirmation
        if ($_SESSION['session_user_level'] < K_AUTH_DELETE_GROUPS) {
            F_print_error('ERROR', $l['m_authorization_denied']);
            break;
        }
        F_print_error('WARNING', $l['m_delete_confirm']);
        echo '<div class="confirmbox">'.K_NEWLINE;
        echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
        echo '<div>'.K_NEWLINE;
        echo '<input type="hidden" name="group_id" id="group_id" value="'.$group_id.'" />'.K_NEWLINE;
        echo '<input type="hidden" name="group_name" id="group_name" value="'.stripslashes($group_name).'" />'.K_NEWLINE;
        F_submit_button('forcedelete', $l['w_delete'], $l['h_delete']);
        F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
        echo '</div>'.K_NEWLINE;
        echo '</form>'.K_NEWLINE;
        echo '</div>'.K_NEWLINE;
        break;
    }

    case 'forcedelete':{
        F_stripslashes_formfields(); // Delete specified user
        if ($_SESSION['session_user_level'] < K_AUTH_DELETE_GROUPS) {
            F_print_error('ERROR', $l['m_authorization_denied']);
            break;
        }
        if ($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
            $sql = 'DELETE FROM '.K_TABLE_GROUPS.' WHERE group_id='.$group_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                $group_id=false;
                F_print_error('MESSAGE', '['.stripslashes($group_name).'] '.$l['m_group_deleted']);
            }
        }
        break;
    }

    case 'update':{ // Update user
        // check if the confirmation chekbox has been selected
        if (!isset($_REQUEST['confirmupdate']) or ($_REQUEST['confirmupdate'] != 1)) {
            F_print_error('WARNING', $l['m_form_missing_fields'].': '.$l['w_confirm'].' &rarr; '.$l['w_update']);
            F_stripslashes_formfields();
            break;
        }
        if ($formstatus = F_check_form_fields()) {
            // check if name is unique
            if (!F_check_unique(K_TABLE_GROUPS, 'group_name=\''.F_escape_sql($db, $group_name).'\'', 'group_id', $group_id)) {
                F_print_error('WARNING', $l['m_duplicate_name']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            $sql = 'UPDATE '.K_TABLE_GROUPS.' SET
				group_name=\''.F_escape_sql($db, $group_name).'\'
				WHERE group_id='.$group_id.'';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                F_print_error('MESSAGE', $group_name.': '.$l['m_group_updated']);
            }
        }
        break;
    }

    case 'add':{ // Add user
        if ($formstatus = F_check_form_fields()) { // check submitted form fields
            // check if name is unique
            if (!F_check_unique(K_TABLE_GROUPS, 'group_name=\''.F_escape_sql($db, $group_name).'\'')) {
                F_print_error('WARNING', $l['m_duplicate_name']);
                $formstatus = false;
                F_stripslashes_formfields();
                break;
            }
            $sql = 'INSERT INTO '.K_TABLE_GROUPS.' (
				group_name
				) VALUES (
				\''.F_escape_sql($db, $group_name).'\')';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            } else {
                $group_id = F_db_insert_id($db, K_TABLE_GROUPS, 'group_id');
            }
            // add current user to the new group
            $sql = 'INSERT INTO '.K_TABLE_USERGROUP.' (
				usrgrp_user_id,
				usrgrp_group_id
				) VALUES (
				\''.$_SESSION['session_user_id'].'\',
				\''.$group_id.'\'
				)';
            if (!$r = F_db_query($sql, $db)) {
                F_display_db_error(false);
            }
        }
        break;
    }

    case 'clear':{ // Clear form fields
        $group_name = '';
        break;
    }

    default :{
        break;
    }
} //end of switch

// --- Initialize variables
if ($formstatus) {
    if ($menu_mode != 'clear') {
        if (!isset($group_id) or empty($group_id)) {
            $group_id = 0;
            $group_name = '';
        } else {
            $sql = F_user_group_select_sql('group_id='.$group_id).' LIMIT 1';
            if ($r = F_db_query($sql, $db)) {
                if ($m = F_db_fetch_array($r)) {
                    $group_id = $m['group_id'];
                    $group_name = $m['group_name'];
                } else {
                    $group_name = '';
                }
            } else {
                F_display_db_error();
            }
        }
    }
}

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_groupeditor">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="group_id">'.$l['w_group'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="group_id" id="group_id" size="0" onchange="document.getElementById(\'form_groupeditor\').submit()">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($group_id == 0) {
    echo ' selected="selected"';
}
echo '>+</option>'.K_NEWLINE;
$sql = F_user_group_select_sql();
if ($r = F_db_query($sql, $db)) {
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

echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo getFormRowTextInput('group_name', $l['w_name'], $l['h_group_name'], '', $group_name, '', 255, false, false, false, '');

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($group_id) and ($group_id > 0)) {
    echo '<span style="background-color:#999999;">';
    echo '<input type="checkbox" name="confirmupdate" id="confirmupdate" value="1" title="confirm &rarr; update" />';
    F_submit_button('update', $l['w_update'], $l['h_update']);
    echo '</span>';
    F_submit_button('add', $l['w_add'], $l['h_add']);
    if ($_SESSION['session_user_level'] >= K_AUTH_DELETE_GROUPS) {
        // your account and anonymous user can't be deleted
        F_submit_button('delete', $l['w_delete'], $l['h_delete']);
    }
} else {
    F_submit_button('add', $l['w_add'], $l['h_add']);
}
F_submit_button('clear', $l['w_clear'], $l['h_clear']);

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="group_name" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_edit_group'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
