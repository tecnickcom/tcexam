<?php
//============================================================+
// File name   : tce_db_dal.php
// Begin       : 2003-10-12
// Last Update : 2013-10-23
//
// Description : Load the functions for the selected database
//               type (Database Abstraction Layer).
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
//    Copyright (C) 2004-2013  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Database Abstraction layer (DAL).
 * Loads the Database functions for the selected DATABASE type.
 * The database type is defined by K_DATABASE_TYPE constant on /shared/config/tce_db_config.php configuration file.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2003-10-12
 */

/**
 */

switch (K_DATABASE_TYPE) {
    case 'MYSQL':
    default: {
        require_once('../../shared/code/tce_db_dal_mysqli.php');
        break;
    }
    case 'POSTGRESQL': {
        require_once('../../shared/code/tce_db_dal_postgresql.php');
        break;
    }
    case 'ORACLE': {
        require_once('../../shared/code/tce_db_dal_oracle.php');
        break;
    }
    case 'MYSQLDEPRECATED':
    default: {
        require_once('../../shared/code/tce_db_dal_mysql.php');
        break;
    }
}

//============================================================+
// END OF FILE
//============================================================+
