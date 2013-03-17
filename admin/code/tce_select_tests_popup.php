<?php
//============================================================+
// File name   : tce_select_tests_popup.php
// Begin       : 2012-12-02
// Last Update : 2012-12-02
//
// Description : Display user selection table on popup window.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               Manor Coach House, Church Hill
//               Aldershot, Hants, GU12 4RQ
//               UK
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
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
//    Additionally, you can't remove, move or hide the original TCExam logo,
//    copyrights statements and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display user selection table on popup window.
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

require_once('../code/tce_page_header_popup.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_test_select.php');

if(!isset($order_field)) {$order_field='test_begin_time DESC,test_name';}
if(!isset($orderdir)) {$orderdir=0;}
if(!isset($firstrow)) {$firstrow=0;}
if(!isset($rowsperpage)) {$rowsperpage=K_MAX_ROWS_PER_PAGE;}
if(!isset($searchterms)) {$searchterms='';}
if(!isset($cid)) {$cid='';} else {$cid = preg_replace('/[^a-z0-9_]/', '', $cid);} // ID of the calling form field
if(!isset($tids)) {$tids='';} else {$tids = preg_replace('/[^x0-9]/', '', $tids);}  // selected test IDs

echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_testselect">'.K_NEWLINE;

echo '<input type="hidden" name="cid" id="cid" value="'.$cid.'" />'.K_NEWLINE;
echo '<input type="hidden" name="tids" id="tids" value="'.$tids.'" />'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;

echo '<span class="formw">'.K_NEWLINE;
echo '<input type="text" name="searchterms" id="searchterms" value="'.htmlspecialchars($searchterms, ENT_COMPAT, $l['a_meta_charset']).'" size="20" maxlength="255" title="'.$l['w_search'].'" />';
F_submit_button('search', $l['w_search'], $l['w_search']);
echo '</span></div>'.K_NEWLINE;
// build a search query
$wherequery = '';
if (strlen($searchterms) > 0) {
	$terms = preg_split("/[\s]+/i", $searchterms); // Get all the words into an array
	foreach ($terms as $word) {
		$word = F_escape_sql($word);
		$wherequery .= ' AND ((test_name LIKE \'%'.$word.'%\')';
		$wherequery .= ' OR (test_begin_time LIKE \'%'.$word.'%\')';
		$wherequery .= ' OR (test_end_time LIKE \'%'.$word.'%\')';
		$wherequery .= ' OR (test_description LIKE \'%'.$word.'%\'))';
	}
	$wherequery = '('.substr($wherequery, 5).')';
}

// select only specified test IDs
if (isset($tids) AND !empty($tids)) {
	$tid_list = '';
	$tids = explode('x', $tids);
	foreach ($tids as $id) {
		$tid_list .= ','.intval($id);
	}
	if (!empty($tid_list)) {
		if (!empty($wherequery)) {
			$wherequery .= ' AND ';
		}
		$wherequery .= '(test_id IN ('.substr($tid_list, 1).'))';
	}
}

echo getFormNoscriptSelect();

echo '<div class="row"><hr /></div>'.K_NEWLINE;

F_show_select_test_popup($order_field, $orderdir, $firstrow, $rowsperpage, $wherequery, $searchterms, $cid);

echo '</form>'.K_NEWLINE;

require_once('../code/tce_page_footer_popup.php');

//============================================================+
// END OF FILE
//============================================================+
