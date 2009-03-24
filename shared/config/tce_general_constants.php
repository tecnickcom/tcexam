<?php
//============================================================+
// File name   : tce_general_constants.php
// Begin       : 2002-03-01
// Last Update : 2009-02-12
// 
// Description : Configuration file for general constants.
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
 * Configuration file for general constants.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2002-03-01
 */

/**
 * New line character
 */
define ('K_NEWLINE', "\n");

/**
 * Tabulation character
 */
define ('K_TAB', "\t");

/**
 * number of seconds in one minute
 */
define ('K_SECONDS_IN_MINUTE', 60);

/**
 * number of seconds in one hour
 */
define ('K_SECONDS_IN_HOUR', 60 * K_SECONDS_IN_MINUTE);

/**
 * number of seconds in one day
 */
define ('K_SECONDS_IN_DAY', 24 * K_SECONDS_IN_HOUR);

/**
 * number of seconds in one week
 */
define ('K_SECONDS_IN_WEEK', 7 * K_SECONDS_IN_DAY);

/**
 * number of seconds in one month
 */
define ('K_SECONDS_IN_MONTH', 30 * K_SECONDS_IN_DAY);

/**
 * number of seconds in one year
 */
define ('K_SECONDS_IN_YEAR', 365 * K_SECONDS_IN_DAY);

/**
 * string used for security feature, do not alter
 */
define ('K_KEY_SECURITY', 'VENFeGFtIChjKSAyMDA0LTIwMDggTmljb2xhIEFzdW5pIC0gVGVjbmljay5jb20gcy5yLmwuIC0gd3d3LnRjZXhhbS5jb20=');

/**
 * string used as a seed for some security code generation please change this value and keep it secret
 */
define ('K_RANDOM_SECURITY', '23shw76x');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
