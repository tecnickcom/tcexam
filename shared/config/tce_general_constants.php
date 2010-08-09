<?php
//============================================================+
// File name   : tce_general_constants.php
// Begin       : 2002-03-01
// Last Update : 2009-11-10
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com S.r.l.
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
//    Additionally, you can't remove, move or hide the original TCExam logo,
//    copyrights statements and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Configuration file for general constants.
 * @package com.tecnick.tcexam.shared.cfg
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2002-03-01
 */

/**
 * New line character.
 */
define ('K_NEWLINE', "\n");

/**
 * Tabulation character.
 */
define ('K_TAB', "\t");

/**
 * Number of seconds in one minute.
 */
define ('K_SECONDS_IN_MINUTE', 60);

/**
 * Number of seconds in one hour.
 */
define ('K_SECONDS_IN_HOUR', 60 * K_SECONDS_IN_MINUTE);

/**
 * Number of seconds in one day.
 */
define ('K_SECONDS_IN_DAY', 24 * K_SECONDS_IN_HOUR);

/**
 * Number of seconds in one week.
 */
define ('K_SECONDS_IN_WEEK', 7 * K_SECONDS_IN_DAY);

/**
 * Number of seconds in one month.
 */
define ('K_SECONDS_IN_MONTH', 30 * K_SECONDS_IN_DAY);

/**
 * Number of seconds in one year.
 */
define ('K_SECONDS_IN_YEAR', 365 * K_SECONDS_IN_DAY);

/**
 * String used for security feature, do not alter.
 */
define ('K_KEY_SECURITY', 'VENFeGFtIChjKSAyMDA0LTIwMDggTmljb2xhIEFzdW5pIC0gVGVjbmljay5jb20gcy5yLmwuIC0gd3d3LnRjZXhhbS5jb20=');

/**
 * String used as a seed for some security code generation please change this value and keep it secret.
 */
define ('K_RANDOM_SECURITY', 'j8sl1a9x');

//============================================================+
// END OF FILE
//============================================================+
