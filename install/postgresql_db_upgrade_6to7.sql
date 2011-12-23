/*
============================================================
File name   : postgresql_db_upgrade_6to7.sql
Begin       : 2008-11-28
Last Update : 2009-02-05

Description : TCExam database structure upgrade commands
              (from version 6 to 7).
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
   Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com LTD

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

CREATE TABLE "tce_modules" (
	"module_id" BigSerial NOT NULL,
	"module_name" Varchar(255) NOT NULL,
	"module_enabled" Boolean NOT NULL Default '0',
constraint "PK_tce_modules_module_id" primary key ("module_id")
) Without Oids;
INSERT INTO tce_modules (module_name,module_enabled) VALUES ('default','1');
ALTER TABLE "tce_modules" ADD Constraint "ak_module_name" UNIQUE ("module_name");
ALTER TABLE "tce_subjects" ADD "subject_module_id" Bigint NOT NULL Default 1,
ALTER TABLE "tce_subjects" DROP Constraint "ak_subject_name";
ALTER TABLE "tce_subjects" ADD Constraint "ak_subject_name" UNIQUE ("subject_module_id","subject_name");
ALTER TABLE "tce_subjects" ADD Constraint "rel_module_subjects" foreign key ("subject_module_id") references "tce_modules" ("module_id") ON DELETE cascade;
ALTER TABLE "tce_users" ALTER "user_ip" TYPE Varchar(39);
ALTER TABLE "tce_tests_logs" ALTER "testlog_user_ip" TYPE Varchar(39);

