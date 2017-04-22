/*
============================================================
File name   : oracle_db_upgrade_9to10.sql
Begin       : 2010-02-12
Last Update : 2010-02-12

Description : TCExam database structure upgrade commands
              (from version 9 to 10).
Database    : Oracle

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

ALTER TABLE tce_tests ADD test_mcma_partial_score NUMBER(1) DEFAULT '1' NOT NULL;
ALTER TABLE tce_tests ADD test_logout_on_timeout Boolean NUMBER(1) DEFAULT '0' NOT NULL;

