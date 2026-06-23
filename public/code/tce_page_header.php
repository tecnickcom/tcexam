<?php

//============================================================+
// File name   : tce_page_header.php
// Begin       : 2001-09-18
// Last Update : 2023-11-30
//
// Description : Outputs default XHTML page header.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Outputs default XHTML page header.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2001-09-18
 */

require_once 'tce_xhtml_header.php';

// display header banner (logo + timer)
echo '<header class="header" role="banner">' . K_NEWLINE;
echo '<div class="left"></div>' . K_NEWLINE;
echo '<div class="right" id="timersection">' . K_NEWLINE;
include '../../shared/code/tce_page_timer.php';
echo '</div>' . K_NEWLINE;
echo '</header>' . K_NEWLINE;

// display navigation menu
echo
    '<nav id="scrollayer" class="scrollmenu" aria-label="'
        . htmlspecialchars($l['w_jump_menu'], ENT_QUOTES, $l['a_meta_charset'])
        . '">'
        . K_NEWLINE
;
require_once __DIR__ . '/tce_page_menu.php';
echo '</nav>' . K_NEWLINE;

echo '<main id="maincontent" class="body">' . K_NEWLINE;

echo '<h1>' . htmlspecialchars($thispage_title, ENT_NOQUOTES, $l['a_meta_charset']) . '</h1>' . K_NEWLINE;
