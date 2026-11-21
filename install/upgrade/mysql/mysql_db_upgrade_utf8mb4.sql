/*
============================================================
File name   : mysql_db_upgrade_utf8mb4.sql
Begin       : 2026-11-18
Last Update : 2026-11-18

Description : Convert the TCExam tables to the utf8mb4 character set
              (required to store 4-byte UTF-8 characters such as emoji).
Database    : MySQL 5.7.7+ or MariaDB 10.2.2+

Usage:
- back up the database first;
- replace the "tce_" prefix if the installation uses a different table prefix;
- also set the database default, replacing <database_name>:
  ALTER DATABASE <database_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
- the conversion rewrites every table; large tce_tests_logs tables can take a long time;
- CONVERT TO changes Text columns to MediumText, so they are set back to Text
  (the byte length of the stored values does not change).

License:
Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
See LICENSE file for more information.
//============================================================+
*/

ALTER TABLE tce_sessions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_modules CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_subjects CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_questions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_answers CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_tests CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_test_subjects CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_tests_users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_tests_logs CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_tests_logs_answers CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_user_groups CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_usrgroups CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_testgroups CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_test_subject_set CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_sslcerts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_testsslcerts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_testuser_stat CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, ROW_FORMAT = DYNAMIC;
ALTER TABLE tce_sessions MODIFY cpsession_data Text NOT NULL;
ALTER TABLE tce_subjects MODIFY subject_description Text;
ALTER TABLE tce_questions MODIFY question_description Text NOT NULL, MODIFY question_explanation Text;
ALTER TABLE tce_answers MODIFY answer_description Text NOT NULL, MODIFY answer_explanation Text;
ALTER TABLE tce_tests MODIFY test_description Text NOT NULL;
ALTER TABLE tce_tests_users MODIFY testuser_comment Text;
ALTER TABLE tce_tests_logs MODIFY testlog_answer_text Text, MODIFY testlog_comment Text;
