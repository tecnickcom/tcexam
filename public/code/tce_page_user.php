<?php

//============================================================+
// File name   : tce_page_user.php
// Begin       : 2010-09-20
// Last Update : 2023-11-30
//
// Description : Output XHTML unordered list menu for user.
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
 * Output XHTML unordered list menu for user.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2010-09-20
 */



require_once('../config/tce_config.php');

$pagelevel = K_AUTH_PAGE_USER;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['w_user'];
require_once('../code/tce_page_header.php');

echo '<div class="container">' . K_NEWLINE;

// Modern vertical menu with icons
$user_page_icons = [
    'tce_user_change_email.php' => '&#9993;',
    'tce_user_change_password.php' => '&#128273;',
];

echo '<div class="vmenu">' . K_NEWLINE;
foreach ($menu['tce_page_user.php']['sub'] as $link => $data) {
    if (! $data['enabled'] || $_SESSION['session_user_level'] < $data['level']) {
        continue;
    }
    $icon = isset($user_page_icons[$link]) ? $user_page_icons[$link] : '&#128196;';
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

echo '</div>' . K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
