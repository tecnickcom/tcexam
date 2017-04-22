/*
============================================================
File name   : postgresql_db_upgrade_10to11.sql
Begin       : 2010-06-16
Last Update : 2011-01-28

Description : TCExam database structure upgrade commands
              (from version 10 to 11).
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

ALTER TABLE "tce_modules" ADD "module_user_id" Bigint NOT NULL Default 1;
ALTER TABLE "tce_modules" ADD Constraint "rel_module_author" foreign key ("module_user_id") references "tce_users" ("user_id") ON DELETE cascade;
ALTER TABLE "tce_questions" DROP INDEX "ak_question";
ALTER TABLE "tce_answers" DROP INDEX "ak_answer";
