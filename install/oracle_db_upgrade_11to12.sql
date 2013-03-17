/*
============================================================
File name   : oracle_db_upgrade_11to12.sql
Begin       : 2012-11-22
Last Update : 2012-12-26

Description : TCExam database structure upgrade commands
              (from version 11 to 11.5).
Database    : Oracle

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
   Copyright (C) 2004-2012 Nicola Asuni - Tecnick.com LTD

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

ALTER TABLE tce_users ADD user_otpkey VARCHAR2(255);
ALTER TABLE tce_tests ADD test_questions_order_mode NUMBER(5,0) DEFAULT 0 NOT NULL;
ALTER TABLE tce_tests ADD test_answers_order_mode NUMBER(5,0) DEFAULT 0 NOT NULL;
ALTER TABLE tce_tests ADD test_password VARCHAR2(255);
ALTER TABLE tce_tests_users DROP Constraint ak_testuser;
ALTER TABLE tce_tests_users ADD Constraint ak_testuser UNIQUE (testuser_test_id,testuser_user_id,testuser_status);
CREATE TABLE tce_testuser_stat (tus_id NUMBER(19,0) NOT NULL, tus_date DATE NOT NULL, constraint pk_tce_testuser_stat primary key (tus_id));
CREATE SEQUENCE tce_testuser_stat_seq MINVALUE 1 START WITH 1 INCREMENT BY 1 CACHE 3;
CREATE OR REPLACE TRIGGER tce_testuser_stat_trigger BEFORE INSERT ON tce_testuser_stat FOR EACH ROW BEGIN SELECT tce_testuser_stat_seq.nextval INTO :new.tus_id FROM DUAL; END;;
