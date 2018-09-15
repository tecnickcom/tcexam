/*
============================================================
File name   : oracle_db_upgrade_10to11.sql
Begin       : 2010-06-16
Last Update : 2010-06-16

Description : TCExam database structure upgrade commands
              (from version 10 to 11).
Database    : Oracle

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

ALTER TABLE tce_modules ADD module_user_id NUMBER(19,0) DEFAULT 1 NOT NULL;
ALTER TABLE tce_modules ADD Constraint rel_module_author foreign key (module_user_id) references tce_users (user_id) ON DELETE cascade;

