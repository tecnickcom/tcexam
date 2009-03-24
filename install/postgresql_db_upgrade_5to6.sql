/*
============================================================
File name   : postgresql_db_upgrade_5to6.sql
Begin       : 2008-09-20
Last Update : 2007-10-03

Description : TCExam database structure upgrade commands
              (from version 5 to 6).
Database    : PostgreSQL 8+

Author: Nicola Asuni
(c) Copyright:
              Nicola Asuni
              Tecnick.com S.r.l.
              Via della Pace, 11
              09044 Quartucciu (CA)
              ITALY
              www.tecnick.com
              info@tecnick.com

License: 
   Copyright (C) 2004-2009  Nicola Asuni - Tecnick.com S.r.l.
   
   This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.
   
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   
   You should have received a copy of the GNU General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
   Additionally, you can't remove the original TCExam logo, copyrights statements
   and links to Tecnick.com and TCExam websites.
   
   See LICENSE.TXT file for more information.
============================================================+
*/

ALTER TABLE "tce_questions" ADD "question_position" Bigint NULL;
ALTER TABLE "tce_questions" ADD "question_timer" Smallint NULL;
ALTER TABLE "tce_questions" ADD "question_fullscreen" Boolean NOT NULL Default '0';
ALTER TABLE "tce_questions" ADD "question_inline_answers" Boolean NOT NULL DEFAULT '0';
ALTER TABLE "tce_questions" ADD "question_auto_next" Boolean NOT NULL DEFAULT '0';
ALTER TABLE "tce_answers" ADD "answer_keyboard_key" Smallint NULL;
ALTER TABLE "tce_tests" DROP "test_random_questions";
ALTER TABLE "tce_tests" ADD "test_score_wrong" Numeric(10,3) Default 0;
ALTER TABLE "tce_tests" ADD "test_score_unanswered" Numeric(10,3) Default 0;
ALTER TABLE "tce_tests" ADD "test_score_threshold" Numeric(10,3) Default 0;
ALTER TABLE "tce_tests" ADD "test_random_questions_select" Boolean NOT NULL Default '1';
ALTER TABLE "tce_tests" ADD "test_random_questions_order" Boolean NOT NULL Default '1';
ALTER TABLE "tce_tests" ADD "test_random_answers_select" Boolean NOT NULL Default '1';
ALTER TABLE "tce_tests" ADD "test_random_answers_order" Boolean NOT NULL Default '1';
ALTER TABLE "tce_tests" ADD "test_comment_enabled" Boolean NOT NULL Default '1';
ALTER TABLE "tce_tests" ADD "test_menu_enabled" Boolean NOT NULL Default '1';
ALTER TABLE "tce_tests" ADD "test_noanswer_enabled" Boolean NOT NULL Default '1';
ALTER TABLE "tce_tests" ADD "test_mcma_radio" Boolean NOT NULL Default '1';
ALTER TABLE "tce_tests" ADD "test_report_to_users" Boolean NOT NULL Default '0';
ALTER TABLE "tce_tests" ALTER "test_score_right" Numeric(10,3) Default 1;
ALTER TABLE "tce_tests_logs_answers" ALTER "logansw_selected" Smallint NOT NULL Default -1;
ALTER TABLE "tce_tests_logs" ADD "testlog_reaction_time" Bigint NOT NULL Default 0;
ALTER TABLE "tce_tests_logs" ADD "testlog_order" Smallint NOT NULL Default 1;
ALTER TABLE "tce_tests_logs" ADD "testlog_num_answers" Smallint NOT NULL Default 0;
ALTER TABLE "tce_tests_logs" ADD "testlog_comment" Text;

