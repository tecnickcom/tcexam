<?php
//============================================================+
// File name   : tce_show_online_users.php
// Begin       : 2001-10-18
// Last Update :2009-09-30
//
// Description : Display online user's data.
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display online user's data.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2001-10-18
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_ONLINE_USERS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_online_users'];

require_once('../code/tce_page_header.php');
require_once('tce_functions_users_online.php');

// set default values
if (!isset($order_field)) {
    $order_field='cpsession_expiry';
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

F_show_online_users('', $order_field, $orderdir, $firstrow, $rowsperpage);

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
