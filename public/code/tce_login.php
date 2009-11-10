<?php
//============================================================+
// File name   : tce_login.php
// Begin       : 2002-03-21
// Last Update : 2009-09-30
// 
// Description : Display Login interface and redirect to main 
//               page.
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
 * Display Login interface and redirect to main page.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2002-03-21
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = 1;
require_once('../../shared/code/tce_authorization.php');

echo '<'.'?xml version="1.0" encoding="'.$l['a_meta_charset'].'"?'.'>'.K_NEWLINE;
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.K_NEWLINE;
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$l['a_meta_language'].'" lang="'.$l['a_meta_language'].'" dir="'.$l['a_meta_dir'].'">'.K_NEWLINE;
echo '<head>'.K_NEWLINE;
echo '<title>LOGIN</title>'.K_NEWLINE;
echo '<meta http-equiv="refresh" content="0;url='.K_MAIN_PAGE.'" />'.K_NEWLINE; //reload page
echo '</head>'.K_NEWLINE;
echo '<body>'.K_NEWLINE;
echo '<a href="'.htmlspecialchars(urldecode(K_MAIN_PAGE), ENT_COMPAT, $l['a_meta_charset']).'">LOGIN...</a>'.K_NEWLINE;
echo '</body>'.K_NEWLINE;
echo '</html>'.K_NEWLINE;

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
