<?php

//============================================================+
// File name   : tce_page_timer.php
// Begin       : 2004-04-29
// Last Update : 2023-11-30
//
// Description : Display timer (date-time + countdown).
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
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
    $examtime = (float) $_REQUEST['examtime'];
    $enable_countdown = 'true';
    $timeout_logout = isset($_REQUEST['timeout_logout']) && $_REQUEST['timeout_logout'] ? 'true' : 'false';
}

echo '<form action="' . htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES) . '" id="timerform">' . K_NEWLINE;
// role="timer" identifies the region to assistive technologies; aria-live stays "off"
// (the default for the timer role) on purpose, so the per-second updates are not announced.
echo '<div role="timer" aria-live="off">' . K_NEWLINE;
echo '<label for="timer" class="timerlabel">' . $l['w_time'] . ':</label>' . K_NEWLINE;
echo
    '<input type="text" name="timer" id="timer" value="" size="29" maxlength="29" title="'
        . $l['w_clock_timer']
        . '" readonly="readonly"/>'
        . K_NEWLINE
;
echo '&nbsp;</div>' . K_NEWLINE;
echo '</form>' . K_NEWLINE;
echo '<script src="' . K_PATH_SHARED_JSCRIPTS . 'timer.js" type="text/javascript"></script>' . K_NEWLINE;
echo '<script type="text/javascript">' . K_NEWLINE;
echo '//<![CDATA[' . K_NEWLINE;
echo
    'FJ_start_timer('
        . $enable_countdown
        . ', '
        . (time() - $examtime)
        . ", '"
        . addslashes($l['m_exam_end_time'])
        . "', "
        . $timeout_logout
        . ', '
        . round(microtime(true) * 1000)
        . ');'
        . K_NEWLINE
;
echo '//]]>' . K_NEWLINE;
echo '</script>' . K_NEWLINE;
