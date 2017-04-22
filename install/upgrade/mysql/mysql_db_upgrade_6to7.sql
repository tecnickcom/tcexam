/*
============================================================
File name   : mysql_db_upgrade_6to7.sql
Begin       : 2008-11-28
Last Update : 2009-02-05

Description : TCExam database structure upgrade commands
              (from version 6 to 7).
Database    : MySQL 4.1+

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

CREATE TABLE tce_modules (
	module_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	module_name Varchar(255) NOT NULL,
	module_enabled Bool NOT NULL DEFAULT '0',
 Primary Key (module_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;
INSERT INTO tce_modules (module_name,module_enabled) VALUES ('default','1');
ALTER TABLE tce_modules ADD UNIQUE ak_module_name (module_name);
ALTER TABLE tce_subjects ADD subject_module_id Bigint UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE tce_subjects DROP KEY ak_subject_name;
ALTER TABLE tce_subjects ADD UNIQUE ak_subject_name (subject_module_id,subject_name);
ALTER TABLE tce_subjects ADD Foreign Key (subject_module_id) references tce_modules (module_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_users CHANGE user_ip user_ip VARCHAR(39) NOT NULL;
ALTER TABLE tce_tests_logs CHANGE testlog_user_ip testlog_user_ip VARCHAR(39) NULL DEFAULT NULL;

