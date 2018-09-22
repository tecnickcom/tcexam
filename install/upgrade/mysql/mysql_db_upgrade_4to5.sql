/*
============================================================
File name   : mysql_db_upgrade_4to5.sql
Begin       : 2007-08-25
Last Update : 2007-08-25

Description : TCExam database structure upgrade commands
              (from version 4 to 5).
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

ALTER TABLE tce_answers ADD answer_position Bigint UNSIGNED NULL;
ALTER TABLE tce_tests_logs_answers ADD logansw_position Bigint UNSIGNED NULL;
ALTER TABLE tce_answers DROP INDEX ak_answer, ADD UNIQUE ak_answer (answer_question_id,answer_description(255),answer_position);
