<?php
//============================================================+
// File name   : tce_config.php
// Begin       : 2001-10-23
// Last Update : 2009-09-30
//
// Description : Configuration file for public section.
//
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com S.r.l.
//               Via della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//
// License: 
//    Copyright (C) 2004-2009  Nicola Asuni - Tecnick.com S.r.l.
//    
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//    
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//    
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//     
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Configuration file for public section.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-10-23
 */

/**
 */

// -- INCLUDE files -- 
require_once('../../shared/config/tce_config.php');

// -- DEFAULT META Tags --

/**
 * default site name
 */
define ('K_SITE_TITLE', 'TCExam');

/**
 * default site description
 */
define ('K_SITE_DESCRIPTION', 'TCExam by Tecnick.com');

/**
 * default site author
 */
define ('K_SITE_AUTHOR', 'Nicola Asuni - Tecnick.com s.r.l.');

/**
 * default html reply-to meta tag
 */
define ('K_SITE_REPLY', ''); //

/**
 * default keywords
 */
define ('K_SITE_KEYWORDS', 'TCExam, eExam, e-exam, web, exam');

/**
 * path to default html icon
 */
define ('K_SITE_ICON', '../../favicon.ico');

/**
 * path to public CSS stylesheet
 */
define ('K_SITE_STYLE', K_PATH_STYLE_SHEETS.'default.css');

/**
 * full path to CSS stylesheet for RTL languages
 */
define ('K_SITE_STYLE_RTL', K_PATH_STYLE_SHEETS.'default_rtl.css');


// -- Options / COSTANTS --

/**
 * max number of rows to display in tables
 */
define ('K_MAX_ROWS_PER_PAGE', 50);

/**
 * max file size to be uploaded [bytes]
 */
define ('K_MAX_UPLOAD_SIZE', 1000000);

/**
 * max memory limit for a PHP script
 */
define ('K_MAX_MEMORY_LIMIT', '32M');

/**
 * main page (homepage)
 */
define ('K_MAIN_PAGE', 'index.php');

// -- INCLUDE files -- 
require_once('../../shared/config/tce_db_config.php');
require_once('../../shared/code/tce_db_connect.php');
require_once('../../shared/code/tce_functions_general.php');

ini_set('memory_limit', K_MAX_MEMORY_LIMIT); // set PHP memory limit
ini_set('session.use_trans_sid', 0); // if =1 use PHPSESSID (for clients that do not support cookies)

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
