/*
============================================================
File name   : postgresql_db_upgrade_4to5.sql
Begin       : 2007-08-25
Last Update : 2007-08-25

Description : TCExam database structure upgrade commands
              (from version 4 to 5).
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

ALTER TABLE "tce_answers" ADD "answer_position" Bigint NULL;
ALTER TABLE "tce_tests_logs_answers" ADD "logansw_position" Bigint NULL;
ALTER TABLE "tce_answers" DROP CONSTRAINT "ak_answer", ADD CONSTRAINT "ak_answer" UNIQUE ("answer_question_id","answer_description","answer_position");
