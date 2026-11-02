<?php

//============================================================+
// File name   : tce_export_custom.php
// Begin       : 2008-11-29
// Last Update : 2023-11-30
//
// Description : Export all users' results using a custom format.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Export all users' results using a custom format.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2008-11-29
 * @param $_REQUEST['testid'] (int) test ID
 * @param $_REQUEST['groupid'] (int) group ID
 * @param $_REQUEST['orderfield'] (string) ORDER BY portion of SQL selection query
 */

require_once '../config/tce_config.php';
$pagelevel = K_AUTH_ADMIN_RESULTS;
require_once '../../shared/code/tce_authorization.php';

// customize this file to export questions to your custom format file.

// ...
