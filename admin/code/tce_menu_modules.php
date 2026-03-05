<?php

//============================================================+
// File name   : tce_menu_modules.php
// Begin       : 2004-04-20
// Last Update : 2023-11-30
//
// Description : Output XHTML unordered list menu for modules.
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
 * Output XHTML unordered list menu for modules.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2010-05-10
 */



require_once('../config/tce_config.php');

$pagelevel = 1;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['w_modules'];
require_once('../code/tce_page_header.php');

echo '<div class="container">' . K_NEWLINE;

// Modern vertical menu with icons
$module_icons = [
    'tce_edit_module.php' => '&#128218;',
    'tce_edit_subject.php' => '&#128209;',
    'tce_edit_question.php' => '&#10067;',
    'tce_edit_answer.php' => '&#9989;',
    'tce_show_all_questions.php' => '&#128203;',
    'tce_import_questions.php' => '&#128228;',
    'tce_filemanager.php' => '&#128193;',
    'tce_edit_sslcerts.php' => '&#128274;',
];

echo '<div class="vmenu">' . K_NEWLINE;
foreach ($menu['tce_menu_modules.php']['sub'] as $link => $data) {
    if (! $data['enabled'] || $_SESSION['session_user_level'] < $data['level']) {
        continue;
    }
    $icon = isset($module_icons[$link]) ? $module_icons[$link] : '&#128196;';
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

//echo '<div class="pagehelp">'.$l['w_modules'].'</div>'.K_NEWLINE;
echo '</div>' . K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
