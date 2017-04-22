/*
============================================================
File name   : mysql_db_upgrade_5to6.sql
Begin       : 2008-09-20
Last Update : 2007-10-03

Description : TCExam database structure upgrade commands
              (from version 5 to 6).
Database    : MySQL 4.1+

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

ALTER TABLE tce_questions ADD question_position Bigint UNSIGNED NULL;
ALTER TABLE tce_questions ADD question_timer Smallint(10) NULL;
ALTER TABLE tce_questions ADD question_fullscreen Bool NOT NULL DEFAULT '0';
ALTER TABLE tce_questions ADD question_inline_answers Bool NOT NULL DEFAULT '0';
ALTER TABLE tce_questions ADD question_auto_next Bool NOT NULL DEFAULT '0';
ALTER TABLE tce_answers ADD answer_keyboard_key Smallint(10) UNSIGNED NULL;
ALTER TABLE tce_tests DROP test_random_questions;
ALTER TABLE tce_tests ADD test_score_wrong Decimal(10,3) DEFAULT 0;
ALTER TABLE tce_tests ADD test_score_unanswered Decimal(10,3) Default 0;
ALTER TABLE tce_tests ADD test_score_threshold Decimal(10,3) Default 0;
ALTER TABLE tce_tests ADD test_random_questions_select Bool NOT NULL Default '1';
ALTER TABLE tce_tests ADD test_random_questions_order Bool NOT NULL Default '1';
ALTER TABLE tce_tests ADD test_random_answers_select Bool NOT NULL Default '1';
ALTER TABLE tce_tests ADD test_random_answers_order Bool NOT NULL Default '1';
ALTER TABLE tce_tests ADD test_comment_enabled Bool NOT NULL Default '1';
ALTER TABLE tce_tests ADD test_menu_enabled Bool NOT NULL Default '1';
ALTER TABLE tce_tests ADD test_noanswer_enabled Bool NOT NULL Default '1';
ALTER TABLE tce_tests ADD test_mcma_radio Bool NOT NULL Default '1';
ALTER TABLE tce_tests ADD test_report_to_users Bool NOT NULL DEFAULT '0';
ALTER TABLE tce_tests MODIFY test_score_right Decimal(10,3) NULL DEFAULT '1';
ALTER TABLE tce_tests_logs_answers MODIFY logansw_selected Smallint NOT NULL DEFAULT -1;
ALTER TABLE tce_tests_logs ADD testlog_reaction_time Bigint UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE tce_tests_logs ADD testlog_order Smallint NOT NULL DEFAULT 1;
ALTER TABLE tce_tests_logs ADD testlog_num_answers Smallint UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE tce_tests_logs ADD testlog_comment Text;


/* Indexes */

Alter table tce_tests_users add Index p_testuser_user_id (testuser_user_id);
Alter table tce_tests add Index p_test_user_id (test_user_id);
Alter table tce_subjects add Index p_subject_user_id (subject_user_id);
Alter table tce_usrgroups add Index p_usrgrp_user_id (usrgrp_user_id);
Alter table tce_questions add Index p_question_subject_id (question_subject_id);
Alter table tce_test_subjects add Index p_subjset_subject_id (subjset_subject_id);
Alter table tce_answers add Index p_answer_question_id (answer_question_id);
Alter table tce_tests_logs add Index p_testlog_question_id (testlog_question_id);
Alter table tce_tests_logs_answers add Index p_logansw_answer_id (logansw_answer_id);
Alter table tce_tests_users add Index p_testuser_test_id (testuser_test_id);
Alter table tce_testgroups add Index p_tstgrp_test_id (tstgrp_test_id);
Alter table tce_test_subject_set add Index p_tsubset_test_id (tsubset_test_id);
Alter table tce_tests_logs add Index p_testlog_testuser_id (testlog_testuser_id);
Alter table tce_tests_logs_answers add Index p_logansw_testlog_id (logansw_testlog_id);
Alter table tce_usrgroups add Index p_usrgrp_group_id (usrgrp_group_id);
Alter table tce_testgroups add Index p_tstgrp_group_id (tstgrp_group_id);
Alter table tce_test_subjects add Index p_subjset_tsubset_id (subjset_tsubset_id);

