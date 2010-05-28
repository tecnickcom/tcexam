<?php
//============================================================+
// File name   : tce_db_dal.php
// Begin       : 2003-10-12
// Last Update : 2010-02-08
//
// Description : Load the functions for the selected database
//               type (Database Abstraction Layer).
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com S.r.l.
//               Via della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com S.r.l.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Database Abstraction layer (DAL).
 * Loads the Database functions for the selected DATABASE type.
 * The database type is defined by K_DATABASE_TYPE constant on /shared/config/tce_db_config.php configuration file.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2003-10-12
 */

/**
 */

switch (K_DATABASE_TYPE) {
	case 'MYSQL':
	default: {
		require_once('../../shared/code/tce_db_dal_mysql.php');
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
}

//============================================================+
// END OF FILE
//============================================================+
?>
