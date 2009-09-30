<?php
//============================================================+
// File name   : tce_auth.php
// Begin       : 2002-09-02
// Last Update : 2009-09-30
// 
// Description : Define access levels for each admin page
//               Note:
//                0 = Anonymous user (uregistered user)
//                1 = registered user
//               10 = System Administrator
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
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//    
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//     
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Configuration file: define access levels for each admin page.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2002-09-02
 */

// ************************************************************
// SECURITY WARNING :
// SET THIS FILE AS READ ONLY AFTER MODIFICATIONS   
// ************************************************************

/**
 * required user's level to access index page
 */
define ('K_AUTH_INDEX', 5);

/**
 * required user's level to access "user editor"
 */
define ('K_AUTH_ADMIN_USERS', 5);

/**
 * required user's level to access "tcecode editor"
 */
define ('K_AUTH_ADMIN_TCECODE', 5);

/**
 * required user's level to access "module editor"
 */
define ('K_AUTH_ADMIN_MODULES', 5);

/**
 * required user's level to access "subject editor"
 */
define ('K_AUTH_ADMIN_SUBJECTS', 5);

/**
 * required user's level to access "question editor"
 */
define ('K_AUTH_ADMIN_QUESTIONS', 5);

/**
 * required user's level to access "answer editor"
 */
define ('K_AUTH_ADMIN_ANSWERS', 5);

/**
 * required user's level to access "test editor"
 */
define ('K_AUTH_ADMIN_TESTS', 5);

/**
 * required user's level to access "TCExam information"
 */
define ('K_AUTH_ADMIN_INFO', 0);

/**
 * required user's level to display online users
 */
define ('K_AUTH_ADMIN_ONLINE_USERS', 5);

/**
 * required user's level to upload images
 */
define ('K_AUTH_ADMIN_UPLOAD_IMAGES', 5);

/**
 * required user's level to manually rate free text answers
 */
define ('K_AUTH_ADMIN_RATING', 5);

/**
 * required user's level to display results
 */
define ('K_AUTH_ADMIN_RESULTS', 5);

/**
 * administrator level
 */
define ('K_AUTH_ADMINISTRATOR', 10);

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
