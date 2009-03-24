/*
//============================================================+
// File name   : db_data.sql
// Begin       : 2004-04-28
// Last Update : 2008-11-28
//
// Description : Installation (default) data for TCExam DB
// Database    : PostgreSQL 8+ / MySQL 4.1+

Author: Nicola Asuni
(c) Copyright:
              Tecnick.com S.r.l.
              Via della Pace n. 11
              09044 Quartucciu (CA)
              ITALY
              www.tecnick.com
              info@tecnick.com

License: GNU GENERAL PUBLIC LICENSE v.2
         http://www.gnu.org/copyleft/gpl.html
============================================================
*/

INSERT INTO tce_user_groups (group_name) VALUES ('default');
INSERT INTO tce_users (user_regdate,user_ip,user_name,user_password,user_level) VALUES ('2001-01-01 01:01:01', '0.0.0.0', 'anonymous', '05e573554d095a5a3201590037017eff', 0);
INSERT INTO tce_users (user_regdate,user_ip,user_name,user_password,user_level) VALUES ('2001-01-01 01:01:01', '127.0.0.0', 'admin', '81dc9bdb52d04dc20036dbd8313ed055', 10);
INSERT INTO tce_modules (module_name,module_enabled) VALUES ('default', '1');
