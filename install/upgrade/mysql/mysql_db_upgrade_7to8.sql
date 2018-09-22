/*
============================================================
File name   : mysql_db_upgrade_7to8.sql
Begin       : 2009-02-20
Last Update : 2009-02-20

Description : TCExam database structure upgrade commands
              (from version 7 to 8).
Database    : MySQL 4.1+

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

ALTER TABLE tce_questions ADD question_explanation Text NULL;
ALTER TABLE tce_answers ADD answer_explanation Text NULL;
ALTER TABLE tce_users CHANGE user_ip user_ip VARCHAR(39) NOT NULL;
ALTER TABLE tce_tests_logs CHANGE testlog_user_ip testlog_user_ip VARCHAR(39) NULL DEFAULT NULL;

