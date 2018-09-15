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
              www.tecnick.com
              info@tecnick.com

License:
Copyright (C) 2004-2018 Nicola Asuni - Tecnick.com LTD
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
