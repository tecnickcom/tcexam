<?php
//============================================================+
// File name   : tce_page_timer.php
// Begin       : 2004-04-29
// Last Update : 2010-10-05
//
// Description : Display timer (date-time + countdown).
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Display client timer (date-time + countdown).
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2004-04-29
 */

if (!isset($_REQUEST['examtime'])) {
    $examtime = 0; // remaining exam time in seconds
    $enable_countdown = 'false';
    $timeout_logout = 'false';
} else {
    $examtime = floatval($_REQUEST['examtime']);
    $enable_countdown = 'true';
    if (isset($_REQUEST['timeout_logout']) and ($_REQUEST['timeout_logout'])) {
        $timeout_logout = 'true';
    } else {
        $timeout_logout = 'false';
    }
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
echo 'FJ_start_timer('.$enable_countdown.', '.(time() - $examtime).', \''.addslashes($l['m_exam_end_time']).'\', '.$timeout_logout.', '.(round(microtime(true) * 1000)).');'.K_NEWLINE;
echo '//]]>'.K_NEWLINE;
echo '</script>'.K_NEWLINE;

//============================================================+
// END OF FILE
//============================================================+
