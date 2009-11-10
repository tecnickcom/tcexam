<?php
//============================================================+
// File name   : tce_page_timer.php
// Begin       : 2004-04-29
// Last Update : 2009-09-30
// 
// Description : Display timer (date-time + countdown).
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
 * Display client timer (date-time + countdown).
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2004-04-29
 */

if (!isset($_REQUEST['examtime'])) {
	$_REQUEST['examtime'] = 0; // remaining exam time in seconds
	$enable_countdown = 'false';	
} else {
	$enable_countdown = 'true';
}

echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" id="timerform">'.K_NEWLINE;
echo '<div>'.K_NEWLINE;
echo '<label for="timer" class="timerlabel">'.$l['w_time'].':</label>'.K_NEWLINE;
echo '<input type="text" name="timer" id="timer" value="" size="29" maxlength="29" title="'.$l['w_clock_timer'].'" readonly="readonly"/>'.K_NEWLINE;
echo '&nbsp;</div>'.K_NEWLINE;
echo '</form>'.K_NEWLINE;
echo '<script src="'.K_PATH_SHARED_JSCRIPTS.'timer.js" type="text/javascript"></script>'.K_NEWLINE;
echo '<script type="text/javascript">'.K_NEWLINE;
echo '//<![CDATA['.K_NEWLINE;
echo 'FJ_start_timer('.$enable_countdown.', '.(time() - $_REQUEST['examtime']).', \''.addslashes($l['m_exam_end_time']).'\');'.K_NEWLINE;
echo '//]]>'.K_NEWLINE;
echo '</script>'.K_NEWLINE;

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
