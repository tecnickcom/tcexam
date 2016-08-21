<?php
//============================================================+
// File name   : tce_select_users.php
// Begin       : 2001-09-13
// Last Update : 2011-07-13
//
// Description : Display user selection table.
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
//    Copyright (C) 2004-2011  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display user selection table.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2001-09-13
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_USERS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_user_select'];

require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_user_select.php');

// set default values
if (isset($new_group_id)) {
    $new_group_id = intval($new_group_id);
} else {
    $new_group_id = 0;
}
if (!isset($order_field)) {
    $order_field='user_lastname,user_firstname';
}
if (!isset($orderdir)) {
    $orderdir=0;
}
if (!isset($firstrow)) {
    $firstrow=0;
}
if (!isset($rowsperpage)) {
    $rowsperpage=K_MAX_ROWS_PER_PAGE;
}
if (!isset($searchterms)) {
    $searchterms='';
}
if (isset($_REQUEST['group_id'])) {
    $group_id = intval($_REQUEST['group_id']);
} else {
    $group_id = 0;
}
if (!F_isAuthorizedEditorForGroup($group_id)) {
    F_print_error('ERROR', $l['m_authorization_denied']);
    exit;
}

echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_userselect">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="group_id">'.$l['w_group'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="group_id" id="group_id" size="0" onchange="document.getElementById(\'form_userselect\').submit()">'.K_NEWLINE;

echo '<option value="0"';
if ($group_id == 0) {
    echo ' selected="selected"';
}
echo '>&nbsp;</option>'.K_NEWLINE;
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

echo '<input type="text" name="searchterms" id="searchterms" value="'.htmlspecialchars($searchterms, ENT_COMPAT, $l['a_meta_charset']).'" size="20" maxlength="255" title="'.$l['w_search'].'" />';
F_submit_button('search', $l['w_search'], $l['w_search']);
echo '</span></div>'.K_NEWLINE;
// build a search query
$wherequery = '';
if (strlen($searchterms) > 0) {
    $wherequery = '';
    $terms = preg_split("/[\s]+/i", $searchterms); // Get all the words into an array
    foreach ($terms as $word) {
        $word = F_escape_sql($db, $word);
        $wherequery .= ' AND ((user_name LIKE \'%'.$word.'%\')';
        $wherequery .= ' OR (user_email LIKE \'%'.$word.'%\')';
        $wherequery .= ' OR (user_firstname LIKE \'%'.$word.'%\')';
        $wherequery .= ' OR (user_lastname LIKE \'%'.$word.'%\')';
        $wherequery .= ' OR (user_regnumber LIKE \'%'.$word.'%\')';
        $wherequery .= ' OR (user_ssn LIKE \'%'.$word.'%\'))';
    }
    $wherequery = '('.substr($wherequery, 5).')';
}

echo getFormNoscriptSelect();

echo '<div class="row"><hr /></div>'.K_NEWLINE;

if (isset($_POST['addgroup'])) {
    $menu_mode = 'addgroup';
} elseif (isset($_POST['delgroup'])) {
    $menu_mode = 'delgroup';
} elseif (isset($_POST['move'])) {
    $menu_mode = 'move';
}
if (isset($menu_mode) and (!empty($menu_mode))) {
    $istart = 1 + $firstrow;
    $iend = $rowsperpage + $firstrow;
    for ($i = $istart; $i <= $iend; $i++) {
        // for each selected user
        $keyname = 'userid'.$i;
        if (isset($$keyname)) {
            $user_id = intval($$keyname);
            switch ($menu_mode) {
                case 'delete': {
                    if (($_SESSION['session_user_level'] >= K_AUTH_DELETE_USERS)
                        and ($user_id > 1) and ($user_id != $_SESSION['session_user_id'])
                        and F_isAuthorizedEditorForUser($user_id)) {
                        $sql = 'DELETE FROM '.K_TABLE_USERS.'
							WHERE user_id='.$user_id.'';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error();
                        }
                    }
                    break;
                }
                case 'addgroup': {
                    if (($_SESSION['session_user_level'] >= K_AUTH_ADMIN_GROUPS)
                        and ($new_group_id > 0)
                        and F_isAuthorizedEditorForGroup($new_group_id)) {
                        $groups = F_get_user_groups($user_id);
                        if (!in_array($new_group_id, $groups)) {
                            $sql = 'INSERT INTO '.K_TABLE_USERGROUP.' (
								usrgrp_user_id,
								usrgrp_group_id
								) VALUES (
								\''.$user_id.'\',
								\''.$new_group_id.'\'
								)';
                            if (!$r = F_db_query($sql, $db)) {
                                F_display_db_error();
                            }
                        }
                    }
                    break;
                }
                case 'delgroup': {
                    if (($_SESSION['session_user_level'] >= K_AUTH_DELETE_GROUPS)
                        and ($new_group_id > 0) and F_isAuthorizedEditorForGroup($new_group_id)) {
                        $sql = 'DELETE FROM '.K_TABLE_USERGROUP.'
							WHERE usrgrp_user_id='.$user_id.'
								AND usrgrp_group_id='.$new_group_id.'';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error();
                        }
                    }
                    break;
                }
                case 'move': {
                    if (($_SESSION['session_user_level'] >= K_AUTH_MOVE_GROUPS)
                        and isset($from_group_id) and ($from_group_id > 0)
                        and F_isAuthorizedEditorForGroup($from_group_id)
                        and isset($to_group_id) and ($to_group_id > 0)
                        and F_isAuthorizedEditorForGroup($to_group_id)) {
                        $groups = F_get_user_groups($user_id);
                        if (!in_array($to_group_id, $groups)) {
                            $sql = 'UPDATE '.K_TABLE_USERGROUP.' SET
								usrgrp_group_id='.$to_group_id.'
								WHERE usrgrp_user_id='.$user_id.'
									AND usrgrp_group_id='.$from_group_id.'
								LIMIT 1';
                            if (!$r = F_db_query($sql, $db)) {
                                F_display_db_error();
                            }
                        } else {
                            $sql = 'DELETE FROM '.K_TABLE_USERGROUP.'
							WHERE usrgrp_user_id='.$user_id.'
								AND usrgrp_group_id='.$from_group_id.'';
                            if (!$r = F_db_query($sql, $db)) {
                                F_display_db_error();
                            }
                        }
                    }
                    break;
                }
            } // end of switch
        }
    }
    F_print_error('MESSAGE', $l['m_updated']);
}

F_select_user($order_field, $orderdir, $firstrow, $rowsperpage, $group_id, $wherequery, $searchterms);

echo '</form>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
