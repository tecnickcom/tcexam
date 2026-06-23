<?php

//============================================================+
// File name   : tce_show_online_users.php
// Begin       : 2001-10-18
// Last Update : 2023-11-30
//
// Description : Display online user's data.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Display online user's data.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2001-10-18
 */

require_once '../config/tce_config.php';

$pagelevel = K_AUTH_ADMIN_ONLINE_USERS;
require_once '../../shared/code/tce_authorization.php';

$thispage_title = $l['t_online_users'];

require_once '../code/tce_page_header.php';
require_once 'tce_functions_users_online.php';

// set values from the request (formerly provided by the register-globals emulation), with defaults
$order_field = $_REQUEST['order_field'] ?? 'cpsession_expiry';
$orderdir = isset($_REQUEST['orderdir']) ? (int) $_REQUEST['orderdir'] : 0;
$firstrow = isset($_REQUEST['firstrow']) ? (int) $_REQUEST['firstrow'] : 0;
$rowsperpage = isset($_REQUEST['rowsperpage']) ? (int) $_REQUEST['rowsperpage'] : K_MAX_ROWS_PER_PAGE;

F_show_online_users('', $order_field, $orderdir, $firstrow, $rowsperpage);

require_once '../code/tce_page_footer.php';
