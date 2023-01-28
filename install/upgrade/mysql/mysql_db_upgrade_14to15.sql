/*
============================================================
File name   : mysql_db_upgrade_14to15.sql
Begin       : 2022-12-17
Last Update : 2022-12-17

Description : TCExam database structure upgrade commands
              (from version 14 to 15).
Database    : MySQL 4.1+

Author: Nicola Asuni

(c) Copyright:
              Nicola Asuni
              Tecnick.com LTD
              www.tecnick.com
              info@tecnick.com

License:
Copyright (C) 2004-2022 Nicola Asuni - Tecnick.com LTD
   See LICENSE.TXT file for more information.
//============================================================+
*/

ALTER TABLE tce_tests MODIFY COLUMN test_repeatable TINYINT NOT NULL Default 0;
