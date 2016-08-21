<?php
//============================================================+
// File name   : tce_db_connect.php
// Begin       : 2001-09-02
// Last Update : 2014-01-26
//
// Description : open connection with active database
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
//    Copyright (C) 2004-2014  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Open a connection to a MySQL Server and select a database.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2001-09-02
 */

/**
 */

require_once('../../shared/code/tce_db_dal.php'); // Database Abstraction Layer for selected DATABASE type

if (!$db = @F_db_connect(K_DATABASE_HOST, K_DATABASE_PORT, K_DATABASE_USER_NAME, K_DATABASE_USER_PASSWORD, K_DATABASE_NAME)) {
    die('<h2>Unable to connect to the database!</h2>');
}

//============================================================+
// END OF FILE
//============================================================+
