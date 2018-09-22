/*
============================================================
File name   : postgresql_db_upgrade_9to10.sql
Begin       : 2010-02-12
Last Update : 2010-02-12

Description : TCExam database structure upgrade commands
              (from version 9 to 10).
Database    : PostgreSQL 8+

Author: Nicola Asuni

(c) Copyright:
              Nicola Asuni
              Tecnick.com LTD
              www.tecnick.com
              info@tecnick.com

License:
Copyright (C) 2004-2018 Nicola Asuni - Tecnick.com LTD
   See LICENSE.TXT file for more information.
//============================================================+
*/

ALTER TABLE "tce_tests" ADD "test_mcma_partial_score" Boolean NOT NULL Default '1';
ALTER TABLE "tce_tests" ADD "test_logout_on_timeout" Boolean NOT NULL Default '0';

