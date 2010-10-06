/*
//============================================================+
File name   : db_data.sql
Begin       : 2004-04-28
Last Update : 2010-10-05

Description : Installation (default) data for TCExam DB
Database    : PostgreSQL 8+ / MySQL 4.1+

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
   Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com S.r.l.

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

INSERT INTO tce_user_groups (group_name) VALUES ('default');
INSERT INTO tce_users (user_regdate,user_ip,user_name,user_password,user_level) VALUES ('2001-01-01 01:01:01', '0.0.0.0', 'anonymous', '05e573554d095a5a3201590037017eff', 0);
INSERT INTO tce_users (user_regdate,user_ip,user_name,user_password,user_level) VALUES ('2001-01-01 01:01:01', '127.0.0.0', 'admin', '81dc9bdb52d04dc20036dbd8313ed055', 10);
INSERT INTO tce_usrgroups (usrgrp_user_id,usrgrp_group_id) VALUES (2, 1);
INSERT INTO tce_modules (module_name,module_enabled) VALUES ('default', '1');
