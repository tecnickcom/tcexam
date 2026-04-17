<?php

//============================================================+
// File name   : tce_menu_tests.php
// Begin       : 2004-04-20
// Last Update : 2023-11-30
//
// Description : Output XHTML unordered list menu for tests.
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
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Output XHTML unordered list menu for tests.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2010-05-10
 */



require_once('../config/tce_config.php');

$pagelevel = 1;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['w_tests'];
require_once('../code/tce_page_header.php');

echo '<div class="container">' . K_NEWLINE;

// Modern vertical menu with icons
$test_icons = [
    'tce_edit_test.php' => '&#128221;',
    'tce_select_tests.php' => '&#128269;',
    'tce_import_omr_answers.php' => '&#128196;',
    'tce_import_omr_bulk.php' => '&#128230;',
    'tce_edit_rating.php' => '&#11088;',
    'tce_show_result_allusers.php' => '&#128202;',
    'tce_show_result_user.php' => '&#128100;',
];

echo '<div class="vmenu">' . K_NEWLINE;
foreach ($menu['tce_menu_tests.php']['sub'] as $link => $data) {
    if (! $data['enabled'] || $_SESSION['session_user_level'] < $data['level']) {
        continue;
    }
    $icon = isset($test_icons[$link]) ? $test_icons[$link] : '&#128196;';
    echo '<a href="' . $data['link'] . '" class="vmenu-item" title="' . htmlspecialchars($data['title'], ENT_COMPAT, $l['a_meta_charset']) . '">' . K_NEWLINE;
    echo '<span class="vmenu-icon">' . $icon . '</span>' . K_NEWLINE;
    echo '<span class="vmenu-text">' . K_NEWLINE;
    echo '<span class="vmenu-name">' . htmlspecialchars($data['name'], ENT_NOQUOTES, $l['a_meta_charset']) . '</span>' . K_NEWLINE;
    echo '<span class="vmenu-desc">' . htmlspecialchars($data['title'], ENT_NOQUOTES, $l['a_meta_charset']) . '</span>' . K_NEWLINE;
    echo '</span>' . K_NEWLINE;
    echo '<span class="vmenu-arrow">&rsaquo;</span>' . K_NEWLINE;
    echo '</a>' . K_NEWLINE;
}
echo '</div>' . K_NEWLINE;

//echo '<div class="pagehelp">'.$l['w_tests'].'</div>'.K_NEWLINE;
echo '</div>' . K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
