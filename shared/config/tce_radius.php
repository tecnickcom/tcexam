<?php
//============================================================+
// File name   : tce_radius.php
// Begin       : 2008-01-15
// Last Update : 2009-11-10
//
// Description : Configuration file for RADIUS Render Class.
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
 * Configuration file for RADIUS Render Class.
 * @package com.tecnick.tcexam.shared.cfg
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2008-01-15
 */

/**
 * If true enable RADIUS.
 */
define ('K_RADIUS_ENABLED', false);

/**
 * IP address of the radius server (e.g.: "127.0.0.1").
 */
define ('K_RADIUS_SERVER_IP', 'localhost');

/**
 * Shared secret with the radius server.
 */
define ('K_RADIUS_SHARED_SECRET', 'WinRadius');

/**
 * Radius domain name suffix (e.g.: "@mydomain.com").
 */
define ('K_RADIUS_SUFFIX', '');

/**
 * Radius UDP timeout (e.g.: 5).
 */
define ('K_RADIUS_UDP_TIMEOUT', 5);

/**
 * Radius authentication port (e.g.: 1812).
 */
define ('K_RADIUS_AUTHENTICATION_PORT', 1812);

/**
 * Radius accounting port (e.g.: 1813).
 */
define ('K_RADIUS_ACCOUNTING_PORT', 1813);

/**
 * Set to true if RADIUS uses UTF-8 encoding.
 */
define ('K_RADIUS_UTF8', true);

/**
 * Default user level.
 */
define ('K_RADIUS_USER_LEVEL', 1);

/**
 * Default user group ID. This is the TCExam group id to which the radius accounts belongs.
 */
define ('K_RADIUS_USER_GROUP_ID', 1);

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
