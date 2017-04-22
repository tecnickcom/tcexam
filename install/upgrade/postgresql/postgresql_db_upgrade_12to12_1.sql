/*
============================================================
File name   : postgresql_db_upgrade_11to12.sql
Begin       : 2012-11-22
Last Update : 2013-07-05

Description : TCExam database structure upgrade commands
              (from version 12 to 12.1).
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

CREATE TABLE "tce_sslcerts" (
	"ssl_id" BigSerial NOT NULL,
	"ssl_name" Varchar(255) NOT NULL,
	"ssl_hash" Varchar(32) NOT NULL,
	"ssl_end_date" Timestamp NOT NULL,
	"ssl_enabled" Boolean NOT NULL Default '0',
	"ssl_user_id" Bigint NOT NULL Default 1,
constraint "pk_tce_sslcerts" primary key ("ssl_id")
) Without Oids;

CREATE TABLE "tce_testsslcerts" (
	"tstssl_test_id" Bigint NOT NULL,
	"tstssl_ssl_id" Bigint NOT NULL,
constraint "pk_tce_testsslcerts" primary key ("tstssl_test_id", "tstssl_ssl_id")
) Without Oids;

ALTER TABLE "tce_testsslcerts" ADD CONSTRAINT "rel_test_ssl" foreign key ("tstssl_test_id") references "tce_tests" ("test_id") ON DELETE cascade;
ALTER TABLE "tce_testsslcerts" ADD CONSTRAINT "rel_ssl_test" foreign key ("tstssl_ssl_id") references "tce_sslcerts" ("ssl_id") ON DELETE cascade;
