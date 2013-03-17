<?php
//============================================================+
// File name   : tce_tsv_result_allusers.php
// Begin       : 2006-03-30
// Last Update : 2012-12-31
//
// Description : Functions to export users' results using
//               TSV file format (tab delimited text).
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
 * Display all test results in TSV format.
 * (Tab Delimited Text File)
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2006-03-30
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once('../../shared/code/tce_authorization.php');
require_once('../../shared/code/tce_functions_test_stats.php');

if (isset($_REQUEST['test_id']) AND ($_REQUEST['test_id'] > 0)) {
	$test_id = intval($_REQUEST['test_id']);
	// check user's authorization
	require_once('../../shared/code/tce_authorization.php');
	if (!F_isAuthorizedUser(K_TABLE_TESTS, 'test_id', $test_id, 'test_user_id')) {
		exit;
	}
} else {
	$test_id = 0;
}
if (isset($_REQUEST['group_id']) AND ($_REQUEST['group_id'] > 0)) {
	$group_id = intval($_REQUEST['group_id']);
} else {
	$group_id = 0;
}
if (isset($_REQUEST['user_id'])) {
	$user_id = intval($_REQUEST['user_id']);
} else {
	$user_id = 0;
}
if (isset($_REQUEST['startdate'])) {
	$startdate = $_REQUEST['startdate'];
	$startdate_time = strtotime($startdate);
	$startdate = date(K_TIMESTAMP_FORMAT, $startdate_time);
} else {
	$startdate = 0;
}
if (isset($_REQUEST['enddate'])) {
	$enddate = $_REQUEST['enddate'];
	$enddate_time = strtotime($enddate);
	$enddate = date(K_TIMESTAMP_FORMAT, $enddate_time);
} else {
	$enddate = 0;
}
if (isset($_REQUEST['order_field']) AND !empty($_REQUEST['order_field']) AND (in_array($_REQUEST['order_field'], array('testuser_creation_time', 'testuser_end_time', 'user_name', 'user_lastname', 'user_firstname', 'total_score')))) {
	$order_field = $_REQUEST['order_field'];
} else {
	$order_field = 'total_score, user_lastname, user_firstname';
}
if (!isset($_REQUEST['orderdir']) OR empty($_REQUEST['orderdir'])) {
	$full_order_field = $order_field;
} else {
	$full_order_field = $order_field.' DESC';
}
if (isset($_REQUEST['display_mode'])) {
	$display_mode = max(0, min(3, intval($_REQUEST['display_mode'])));
} else {
	$display_mode = 0;
}

// send headers
header('Content-Description: TXT File Transfer');
header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
// force download dialog
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream', false);
header('Content-Type: application/download', false);
header('Content-Type: text/tab-separated-values', false);
// use the Content-Disposition header to supply a recommended filename
header('Content-Disposition: attachment; filename=tcexam_test_results_'.$test_id.'_'.date('YmdHis').'.tsv;');
header('Content-Transfer-Encoding: binary');

$data = F_getAllUsersTestStat($test_id, $group_id, $user_id, $startdate, $enddate, $full_order_field);

$table = F_printTestResultStat($data, 1, $order_field, '');
$table .= F_printTestStat($test_id, $group_id, $user_id, $startdate, $enddate, 0, $data, $display_mode);

echo F_html_to_TSV($table);


//============================================================+
// END OF FILE
//============================================================+
