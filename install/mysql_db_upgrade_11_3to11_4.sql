/*
============================================================
File name   : mysql_db_upgrade_11_3to11_4.sql
Begin       : 2012-11-06
Last Update : 2012-11-06

Description : TCExam database structure upgrade commands
              (from version 11.3 to 11.4).
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

ALTER TABLE tce_questions CHANGE question_description question_description TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;
ALTER TABLE tce_answers CHANGE answer_description answer_description TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;

