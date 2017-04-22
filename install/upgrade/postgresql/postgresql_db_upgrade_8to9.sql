/*
============================================================
File name   : postgresql_db_upgrade_8to9.sql
Begin       : 2009-03-08
Last Update : 2009-03-08

Description : TCExam database structure upgrade commands
              (from version 8 to 9).
Database    : PostgreSQL 8+

Author: Nicola Asuni

(c) Copyright:
              Nicola Asuni
              Tecnick.com LTD
              www.tecnick.com
              info@tecnick.com

License:
Copyright (C) 2004-2017 Nicola Asuni - Tecnick.com LTD
   See LICENSE.TXT file for more information.
//============================================================+
*/

ALTER TABLE "tce_tests" ADD "test_repeatable" Boolean NOT NULL Default '0';

