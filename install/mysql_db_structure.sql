/*
============================================================
File name   : mysql_db_structure.sql
Begin       : 2004-04-28
Last Update : 2013-07-02

Description : TCExam database structure.
Database    : MySQL 4.1+

Author: Nicola Asuni

(c) Copyright:
              Nicola Asuni
              Tecnick.com LTD
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

/* Tables */

CREATE TABLE tce_sessions (
	cpsession_id Varchar(32) NOT NULL,
	cpsession_expiry Datetime NOT NULL,
	cpsession_data Text NOT NULL,
 Primary Key (cpsession_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_users (
	user_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	user_name Varchar(255) NOT NULL,
	user_password Varchar(255) NOT NULL,
	user_email Varchar(255),
	user_regdate Datetime NOT NULL,
	user_ip Varchar(39) NOT NULL,
	user_firstname Varchar(255),
	user_lastname Varchar(255),
	user_birthdate Date,
	user_birthplace Varchar(255),
	user_regnumber Varchar(255),
	user_ssn Varchar(255),
	user_level Smallint(3) UNSIGNED NOT NULL DEFAULT 1,
	user_verifycode Varchar(32),
	user_otpkey Varchar(255),
	UNIQUE (user_verifycode),
 Primary Key (user_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_modules (
	module_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	module_name Varchar(255) NOT NULL,
	module_enabled Bool NOT NULL DEFAULT '0',
	module_user_id Bigint UNSIGNED NOT NULL DEFAULT 1,
 Primary Key (module_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_subjects (
	subject_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	subject_module_id Bigint UNSIGNED NOT NULL DEFAULT 1,
	subject_name Varchar(255) NOT NULL,
	subject_description Text,
	subject_enabled Bool NOT NULL DEFAULT '0',
	subject_user_id Bigint UNSIGNED NOT NULL DEFAULT 1,
 Primary Key (subject_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_questions (
	question_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	question_subject_id Bigint UNSIGNED NOT NULL,
	question_description Text NOT NULL,
	question_explanation Text NULL,
	question_type Smallint(3) UNSIGNED NOT NULL DEFAULT 1,
	question_difficulty Smallint NOT NULL DEFAULT 1,
	question_enabled Bool NOT NULL DEFAULT '0',
	question_position Bigint UNSIGNED NULL,
	question_timer Smallint(10) NULL,
	question_fullscreen Bool NOT NULL DEFAULT '0',
	question_inline_answers Bool NOT NULL DEFAULT '0',
	question_auto_next Bool NOT NULL DEFAULT '0',
 Primary Key (question_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_answers (
	answer_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	answer_question_id Bigint UNSIGNED NOT NULL,
	answer_description Text NOT NULL,
	answer_explanation Text NULL,
	answer_isright Bool NOT NULL DEFAULT '0',
	answer_enabled Bool NOT NULL DEFAULT '0',
	answer_position Bigint UNSIGNED NULL,
	answer_keyboard_key Smallint(10) UNSIGNED NULL,
 Primary Key (answer_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_tests (
	test_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	test_name Varchar(255) NOT NULL,
	test_description Text NOT NULL,
	test_begin_time Datetime,
	test_end_time Datetime,
	test_duration_time Smallint(10) UNSIGNED NOT NULL DEFAULT 0,
	test_ip_range Varchar(255) NOT NULL DEFAULT '*.*.*.*',
	test_results_to_users Bool NOT NULL DEFAULT '0',
	test_report_to_users Bool NOT NULL DEFAULT '0',
	test_score_right Decimal(10,3) DEFAULT 1,
	test_score_wrong Decimal(10,3) DEFAULT 0,
	test_score_unanswered Decimal(10,3) Default 0,
	test_max_score Decimal(10,3) NOT NULL DEFAULT 0,
	test_user_id Bigint UNSIGNED NOT NULL DEFAULT 1,
	test_score_threshold Decimal(10,3) Default 0,
	test_random_questions_select Bool NOT NULL Default '1',
	test_random_questions_order Bool NOT NULL Default '1',
	test_questions_order_mode Smallint(3) UNSIGNED NOT NULL DEFAULT 0,
	test_random_answers_select Bool NOT NULL Default '1',
	test_random_answers_order Bool NOT NULL Default '1',
	test_answers_order_mode Smallint(3) UNSIGNED NOT NULL DEFAULT 0,
	test_comment_enabled Bool NOT NULL Default '1',
	test_menu_enabled Bool NOT NULL Default '1',
	test_noanswer_enabled Bool NOT NULL Default '1',
	test_mcma_radio Bool NOT NULL Default '1',
	test_repeatable Bool NOT NULL Default '0',
	test_mcma_partial_score Bool NOT NULL Default '1',
	test_logout_on_timeout Bool NOT NULL Default '0',
	test_password Varchar(255),
 Primary Key (test_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_test_subjects (
	subjset_tsubset_id Bigint UNSIGNED NOT NULL,
	subjset_subject_id Bigint UNSIGNED NOT NULL,
 Primary Key (subjset_tsubset_id,subjset_subject_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_tests_users (
	testuser_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	testuser_test_id Bigint UNSIGNED NOT NULL,
	testuser_user_id Bigint UNSIGNED NOT NULL,
	testuser_status Smallint UNSIGNED NOT NULL DEFAULT 0,
	testuser_creation_time Datetime NOT NULL,
	testuser_comment Text,
 Primary Key (testuser_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_tests_logs (
	testlog_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	testlog_testuser_id Bigint UNSIGNED NOT NULL,
	testlog_user_ip Varchar(39),
	testlog_question_id Bigint UNSIGNED NOT NULL,
	testlog_answer_text Text,
	testlog_score Decimal(10,3),
	testlog_creation_time Datetime,
	testlog_display_time Datetime,
	testlog_change_time Datetime,
	testlog_reaction_time Bigint UNSIGNED NOT NULL DEFAULT 0,
	testlog_order Smallint NOT NULL DEFAULT 1,
	testlog_num_answers Smallint UNSIGNED NOT NULL DEFAULT 0,
	testlog_comment Text,
 Primary Key (testlog_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_tests_logs_answers (
	logansw_testlog_id Bigint UNSIGNED NOT NULL,
	logansw_answer_id Bigint UNSIGNED NOT NULL,
	logansw_selected Smallint NOT NULL DEFAULT -1,
	logansw_order Smallint NOT NULL DEFAULT 1,
	logansw_position Bigint UNSIGNED NULL,
 Primary Key (logansw_testlog_id,logansw_answer_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_user_groups (
	group_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	group_name Varchar(255) NOT NULL,
	UNIQUE (group_name),
 Primary Key (group_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_usrgroups (
	usrgrp_user_id Bigint UNSIGNED NOT NULL,
	usrgrp_group_id Bigint UNSIGNED NOT NULL,
 Primary Key (usrgrp_user_id,usrgrp_group_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_testgroups (
	tstgrp_test_id Bigint UNSIGNED NOT NULL,
	tstgrp_group_id Bigint UNSIGNED NOT NULL,
 Primary Key (tstgrp_test_id,tstgrp_group_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_test_subject_set (
	tsubset_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	tsubset_test_id Bigint UNSIGNED NOT NULL,
	tsubset_type Smallint NOT NULL DEFAULT 1,
	tsubset_difficulty Smallint NOT NULL DEFAULT 1,
	tsubset_quantity Smallint NOT NULL DEFAULT 1,
	tsubset_answers Smallint NOT NULL DEFAULT 0,
 Primary Key (tsubset_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_sslcerts (
	ssl_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	ssl_name VARCHAR(255) NOT NULL,
	ssl_hash VARCHAR(32) NOT NULL,
	ssl_end_date DATETIME NOT NULL,
	ssl_enabled Bool NOT NULL DEFAULT '0',
	ssl_user_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
 Primary Key (ssl_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_testsslcerts (
	tstssl_test_id BIGINT UNSIGNED NOT NULL,
	tstssl_ssl_id BIGINT UNSIGNED NOT NULL,
 Primary Key (tstssl_test_id, tstssl_ssl_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE tce_testuser_stat (
	tus_id Bigint UNSIGNED NOT NULL AUTO_INCREMENT,
	tus_date Datetime NOT NULL,
 PRIMARY KEY (tus_id)
) ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

/* Alternate Keys */

ALTER TABLE tce_users ADD UNIQUE ak_user_name (user_name);
ALTER TABLE tce_users ADD UNIQUE ak_user_regnumber (user_regnumber);
ALTER TABLE tce_users ADD UNIQUE ak_user_ssn (user_ssn);
ALTER TABLE tce_modules ADD UNIQUE ak_module_name (module_name);
ALTER TABLE tce_subjects ADD UNIQUE ak_subject_name (subject_module_id,subject_name);
ALTER TABLE tce_tests ADD UNIQUE ak_test_name (test_name);
ALTER TABLE tce_tests_users ADD UNIQUE ak_testuser (testuser_test_id,testuser_user_id,testuser_status);
ALTER TABLE tce_tests_logs ADD UNIQUE ak_testuser_question (testlog_testuser_id,testlog_question_id);

/* Indexes */

ALTER TABLE tce_tests_users ADD INDEX p_testuser_user_id (testuser_user_id);
ALTER TABLE tce_tests ADD INDEX p_test_user_id (test_user_id);
ALTER TABLE tce_modules ADD INDEX p_module_user_id (module_user_id);
ALTER TABLE tce_subjects ADD INDEX p_subject_user_id (subject_user_id);
ALTER TABLE tce_usrgroups ADD INDEX p_usrgrp_user_id (usrgrp_user_id);
ALTER TABLE tce_questions ADD INDEX p_question_subject_id (question_subject_id);
ALTER TABLE tce_test_subjects ADD INDEX p_subjset_subject_id (subjset_subject_id);
ALTER TABLE tce_answers ADD INDEX p_answer_question_id (answer_question_id);
ALTER TABLE tce_tests_logs ADD INDEX p_testlog_question_id (testlog_question_id);
ALTER TABLE tce_tests_logs_answers ADD INDEX p_logansw_answer_id (logansw_answer_id);
ALTER TABLE tce_tests_users ADD INDEX p_testuser_test_id (testuser_test_id);
ALTER TABLE tce_testgroups ADD INDEX p_tstgrp_test_id (tstgrp_test_id);
ALTER TABLE tce_test_subject_set ADD INDEX p_tsubset_test_id (tsubset_test_id);
ALTER TABLE tce_tests_logs ADD INDEX p_testlog_testuser_id (testlog_testuser_id);
ALTER TABLE tce_tests_logs_answers ADD INDEX p_logansw_testlog_id (logansw_testlog_id);
ALTER TABLE tce_usrgroups ADD INDEX p_usrgrp_group_id (usrgrp_group_id);
ALTER TABLE tce_testgroups ADD INDEX p_tstgrp_group_id (tstgrp_group_id);
ALTER TABLE tce_test_subjects ADD INDEX p_subjset_tsubset_id (subjset_tsubset_id);
ALTER TABLE tce_testsslcerts ADD INDEX p_tstssl_test_id (tstssl_test_id);
ALTER TABLE tce_testsslcerts ADD INDEX p_tstssl_ssl_id (tstssl_ssl_id);

/*  Foreign Keys */

ALTER TABLE tce_tests_users ADD Foreign Key (testuser_user_id) references tce_users (user_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_tests ADD Foreign Key (test_user_id) references tce_users (user_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_modules ADD Foreign Key (module_user_id) references tce_users (user_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_subjects ADD Foreign Key (subject_user_id) references tce_users (user_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_subjects ADD Foreign Key (subject_module_id) references tce_modules (module_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_usrgroups ADD Foreign Key (usrgrp_user_id) references tce_users (user_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_questions ADD Foreign Key (question_subject_id) references tce_subjects (subject_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_test_subjects ADD Foreign Key (subjset_subject_id) references tce_subjects (subject_id) ON DELETE restrict ON UPDATE no action;
ALTER TABLE tce_answers ADD Foreign Key (answer_question_id) references tce_questions (question_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_tests_logs ADD Foreign Key (testlog_question_id) references tce_questions (question_id) ON DELETE restrict ON UPDATE no action;
ALTER TABLE tce_tests_logs_answers ADD Foreign Key (logansw_answer_id) references tce_answers (answer_id) ON DELETE restrict ON UPDATE no action;
ALTER TABLE tce_tests_users ADD Foreign Key (testuser_test_id) references tce_tests (test_id) ON DELETE cascade ON UPDATE restrict;
ALTER TABLE tce_testgroups ADD Foreign Key (tstgrp_test_id) references tce_tests (test_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_test_subject_set ADD Foreign Key (tsubset_test_id) references tce_tests (test_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_tests_logs ADD Foreign Key (testlog_testuser_id) references tce_tests_users (testuser_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_tests_logs_answers ADD Foreign Key (logansw_testlog_id) references tce_tests_logs (testlog_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_usrgroups ADD Foreign Key (usrgrp_group_id) references tce_user_groups (group_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_testgroups ADD Foreign Key (tstgrp_group_id) references tce_user_groups (group_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_test_subjects ADD Foreign Key (subjset_tsubset_id) references tce_test_subject_set (tsubset_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_testsslcerts ADD Foreign Key (tstssl_test_id) references tce_tests (test_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_testsslcerts ADD Foreign Key (tstssl_ssl_id) references tce_sslcerts (ssl_id) ON DELETE cascade ON UPDATE no action;

