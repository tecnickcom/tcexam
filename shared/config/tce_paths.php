<?php
//============================================================+
// File name   : tce_paths.php
// Begin       : 2002-01-15
// Last Update : 2009-02-12
//
// Description : Configuration file for files and directories
//               paths.
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
 * Configuration file for files and directories paths.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2002-01-15
 */

/**
 * host URL (e.g.: "http://www.yoursite.com")
 */
define ('K_PATH_HOST', '');

/**
 * relative URL where this program is installed (e.g.: "/")
 */
define ('K_PATH_TCEXAM', '');

/**
 * real full path where this program is installed (e.g: "/var/www/html/TCExam/")
 */
define ('K_PATH_MAIN', '');

/**
 * Constant used on TCPDF library
 */
define ('K_PATH_URL', K_PATH_HOST.K_PATH_TCEXAM);

/**
 * standard port for http (80) or https (443)
 */
define ('K_STANDARD_PORT', 80);

// --- DO NOT CHANGE THE FOLLOWING VALUES ---

/**
 * path to public code
 */
define ('K_PATH_PUBLIC_CODE', K_PATH_HOST.K_PATH_TCEXAM.'public/code/');

/**
 * server path to public code
 */
define ('K_PATH_PUBLIC_CODE_REAL', K_PATH_MAIN.'public/code/');

/**
 * full path to cache directory
 */
define ('K_PATH_CACHE', K_PATH_MAIN.'cache/');

/**
 * url path to to cache directory
 */
define ('K_PATH_URL_CACHE', K_PATH_TCEXAM.'cache/');

/**
 * full path to fonts directory
 */
define ('K_PATH_FONTS', K_PATH_MAIN.'fonts/');

/**
 * relative path to stylesheets directory
 */
define ('K_PATH_STYLE_SHEETS', '../styles/');

/**
 * relative path to javascript directory
 */
define ('K_PATH_JSCRIPTS', '../jscripts/');

/**
 * relative path to shared javascript directory
 */
define ('K_PATH_SHARED_JSCRIPTS', '../../shared/jscripts/');

/**
 * relative path to images directory
 */
define ('K_PATH_IMAGES', '../../images/');

/**
 * full path to TMX language file
 */
define ('K_PATH_TMX_FILE', K_PATH_MAIN.'shared/config/lang/language_tmx.xml');

/**
 * full path to a blank image
 */
define ('K_BLANK_IMAGE', K_PATH_IMAGES.'_blank.png');

// DOCUMENT_ROOT fix for IIS Webserver
if ((!isset($_SERVER['DOCUMENT_ROOT'])) OR (empty($_SERVER['DOCUMENT_ROOT']))) {
	if(isset($_SERVER['SCRIPT_FILENAME'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
	} elseif(isset($_SERVER['PATH_TRANSLATED'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
	} else {
		// define here your DOCUMENT_ROOT path if the previous fails
		$_SERVER['DOCUMENT_ROOT'] = '/var/www';
	}
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
