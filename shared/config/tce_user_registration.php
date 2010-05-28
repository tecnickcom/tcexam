<?php
//============================================================+
// File name   : tce_user_registration.php
// Begin       : 2008-03-30
// Last Update : 2009-11-10
//
// Description : Configuration file for user registration.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Configuration file for user registration.
 * NOTE: the email verification template is stored on the
 * TMX file at "m_email_registration" translation unit.
 * @package com.tecnick.tcexam.shared.cfg
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2008-03-30
 */

/**
 * If true enable USER REGISTRATION.
 */
define ('K_USRREG_ENABLED', true);

/**
 * If true requires email confirmation.
 */
define ('K_USRREG_EMAIL_CONFIRM', true);

/**
 * Default user group ID for registered user.
 */
define ('K_USRREG_GROUP', 1);

/**
 * URL of an HTML page containing the registration agreement (i.e.: "http://www.yoursite.com/agreement.html").
 */
define ('K_USRREG_AGREEMENT', '');

/**
 * The following email will receive copies of verification messages.
 */
define ('K_USRREG_ADMIN_EMAIL', '');

/**
 * Additional fields to display on registration form.
 * Legal values are:
 * 0 = disabled field;
 * 1 = enabled field;
 * 2 = required field;
 */
$regfields = array(
	'user_email' => 2,
	'user_regnumber' => 1,
	'user_firstname' => 2,
	'user_lastname' => 2,
	'user_birthdate' => 1,
	'user_birthplace' => 1,
	'user_ssn' => 1,
	'user_groups' => 1,
	'user_agreement' => 2
);

//============================================================+
// END OF FILE
//============================================================+
?>
