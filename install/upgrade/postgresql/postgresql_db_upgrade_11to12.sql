/*
============================================================
File name   : postgresql_db_upgrade_11to12.sql
Begin       : 2012-11-22
Last Update : 2012-12-26

Description : TCExam database structure upgrade commands
              (from version 11 to 11.5).
Database    : PostgreSQL 8+

Author: Nicola Asuni

(c) Copyright:
              Nicola Asuni
              Tecnick.com LTD
              Manor Coach House, Church Hill
              Aldershot, Hants, GU12 4RQ
              UK
              www.tecnick.com
              info@tecnick.com

License:
   Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com LTD

   This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as
   published by the Free Software Foundation, either version 3 of the
   License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>.

   Additionally, you can't remove, move or hide the original TCExam logo,
   copyrights statements and links to Tecnick.com and TCExam websites.

   See LICENSE.TXT file for more information.
//============================================================+
*/

ALTER TABLE "tce_users" ADD "user_otpkey" Varchar(255);
ALTER TABLE "tce_tests" ADD "test_questions_order_mode" Smallint NOT NULL Default 0;
ALTER TABLE "tce_tests" ADD "test_answers_order_mode" Smallint NOT NULL Default 0;
ALTER TABLE "tce_tests" ADD "test_password" Varchar(255);
ALTER TABLE "tce_tests_users" DROP CONSTRAINT "ak_testuser";
ALTER TABLE "tce_tests_users" ADD CONSTRAINT "ak_testuser" UNIQUE ("testuser_test_id","testuser_user_id","testuser_status");
ALTER TABLE "tce_tests_users" DROP CONSTRAINT IF EXISTS "tce_tests_users_testuser_status_check";
CREATE TABLE "tce_testuser_stat" ("tus_id" BigSerial NOT NULL, "tus_date" Timestamp NOT NULL, constraint "pk_tce_testuser_stat" primary key ("tus_id")) Without Oids;
