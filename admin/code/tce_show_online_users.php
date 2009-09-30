<?php
//============================================================+
// File name   : tce_show_online_users.php
// Begin       : 2001-10-18
// Last Update :2009-09-30
// 
// Description : Display online user's data.
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
 * Display online user's data.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link www.tecnick.com
 * @since 2001-10-18
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_ONLINE_USERS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_online_users'];

require_once('../code/tce_page_header.php');
require_once('tce_functions_users_online.php');

// set default values
if(!isset($order_field)) {$order_field='cpsession_expiry';}
if(!isset($orderdir)) {$orderdir=0;}
if(!isset($firstrow)) {$firstrow=0;}
if(!isset($rowsperpage)) {$rowsperpage=K_MAX_ROWS_PER_PAGE;}

F_show_online_users('', $order_field, $orderdir, $firstrow, $rowsperpage); 

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
