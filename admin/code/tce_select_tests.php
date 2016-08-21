<?php
//============================================================+
// File name   : tce_select_tests.php
// Begin       : 2012-12-02
// Last Update : 2013-04-09
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
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display user selection table.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2012-12-02
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_TESTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_test_select'];

require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_test_select.php');

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

if (isset($_POST['lock'])) {
    $menu_mode = 'lock';
} elseif (isset($_POST['unlock'])) {
    $menu_mode = 'unlock';
}

echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_testselect">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
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
        $wherequery .= ' AND (';
        $wherequery .= ' (test_name LIKE \'%'.$word.'%\')';
        $wherequery .= ' OR (test_description LIKE \'%'.$word.'%\')';
        if ((preg_match('/^([0-9]{4})[\-]([0-9]{2})[\-]([0-9]{2})$/', $word, $wd) == 1) and (checkdate($wd[2], $wd[3], $wd[1]))) {
            $wherequery .= ' OR ((test_begin_time <= \''.$word.'\')';
            $wherequery .= ' AND (test_end_time >= \''.$word.'\'))';
        }
        $wherequery .= ')';
    }
    $wherequery = '('.substr($wherequery, 5).')';
}

echo getFormNoscriptSelect();

echo '<div class="row"><hr /></div>'.K_NEWLINE;

if (isset($menu_mode) and (!empty($menu_mode))) {
    $istart = 1 + $firstrow;
    $iend = $rowsperpage + $firstrow;
    for ($i = $istart; $i <= $iend; $i++) {
        // for each selected user
        $keyname = 'testid'.$i;
        if (isset($$keyname)) {
            $test_id = intval($$keyname);
            if (F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
                switch ($menu_mode) {
                    case 'lock':{ // lock test by changing end date (subtract 1000 years)
                        $sql = 'UPDATE '.K_TABLE_TESTS.' SET
							test_end_time=test_end_time-10000000000000
							WHERE test_id='.$test_id.'';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error(false);
                        }
                        break;
                    }
                    case 'unlock':{ // unlock test by restoring original end date (add 1000 years)
                        $sql = 'UPDATE '.K_TABLE_TESTS.' SET
							test_end_time=test_end_time+10000000000000
							WHERE test_id='.$test_id.'';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error(false);
                        }
                        break;
                    }
                    case 'delete': {
                        $sql = 'DELETE FROM '.K_TABLE_TESTS.'
							WHERE test_id='.$test_id.'';
                        if (!$r = F_db_query($sql, $db)) {
                            F_display_db_error();
                        }
                        break;
                    }
                } // end of switch
            }
        }
    }
    F_print_error('MESSAGE', $l['m_updated']);
}

F_select_test($order_field, $orderdir, $firstrow, $rowsperpage, $wherequery, $searchterms);

echo '</form>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
