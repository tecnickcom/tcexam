/*
============================================================
File name   : mysql_db_upgrade_10to11.sql
Begin       : 2010-06-16
Last Update : 2011-01-28

Description : TCExam database structure upgrade commands
              (from version 10 to 11).
Database    : MySQL 4.1+

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

ALTER TABLE tce_modules ADD module_user_id Bigint UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE tce_modules ADD INDEX p_module_user_id (module_user_id);
ALTER TABLE tce_modules ADD Foreign Key (module_user_id) references tce_users (user_id) ON DELETE cascade ON UPDATE no action;
ALTER TABLE tce_questions DROP INDEX ak_question;
ALTER TABLE tce_answers DROP INDEX ak_answer;

