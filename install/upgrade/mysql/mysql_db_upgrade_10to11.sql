/*
============================================================
File name   : mysql_db_upgrade_10to11.sql
Begin       : 2010-06-16
Last Update : 2011-01-28

Description : TCExam database structure upgrade commands
              (from version 10 to 11).
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

ALTER TABLE tce_modules ADD module_user_id Bigint UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE tce_modules ADD INDEX p_module_user_id (module_user_id);
ALTER TABLE tce_modules ADD Foreign Key (module_user_id) references tce_users (user_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_questions DROP INDEX ak_question;
ALTER TABLE tce_answers DROP INDEX ak_answer;

