/*
============================================================
File name   : oracle_db_upgrade_9to10.sql
Begin       : 2010-02-12
Last Update : 2010-02-12

Description : TCExam database structure upgrade commands
              (from version 9 to 10).
Database    : Oracle

Author: Nicola Asuni

(c) Copyright:
              Nicola Asuni
              Tecnick.com LTD
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

ALTER TABLE tce_tests ADD test_mcma_partial_score NUMBER(1) DEFAULT '1' NOT NULL;
ALTER TABLE tce_tests ADD test_logout_on_timeout Boolean NUMBER(1) DEFAULT '0' NOT NULL;

