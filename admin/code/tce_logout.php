<?php

//============================================================+
// File name   : tce_logout.php
// Begin       : 2001-09-28
// Last Update : 2023-11-30
//
// Description : Destroy user's session (logout).
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Destroy user's session (logout).
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2001-09-28
 */

require_once '../config/tce_config.php';
require_once '../../shared/code/tce_functions_session.php';

// Destroys all user's session data
session_unset();
session_destroy();
// destroy session ID cookie
setcookie('PHPSESSID', '', [
    'expires' => 1,
    'path' => K_COOKIE_PATH,
    'domain' => K_COOKIE_DOMAIN,
    'secure' => K_COOKIE_SECURE,
    'httponly' => K_COOKIE_HTTPONLY,
    'samesite' => K_COOKIE_SAMESITE,
]);

if (!isset($current_page)) {
    $current_page = '../code/index.php?logout=1';
} elseif (!str_contains($current_page, '?')) {
    $current_page .= '?logout=1';
} else {
    $current_page .= '&amp;logout=1';
}

echo '<!DOCTYPE html>' . K_NEWLINE;
echo '<html lang="' . $l['a_meta_language'] . '" dir="' . $l['a_meta_dir'] . '">' . K_NEWLINE;
echo '<head>' . K_NEWLINE;
echo '<meta charset="' . $l['a_meta_charset'] . '" />' . K_NEWLINE;
echo '<title>' . htmlspecialchars($l['w_logout'], ENT_COMPAT, $l['a_meta_charset']) . '</title>' . K_NEWLINE;
echo '<meta http-equiv="refresh" content="0;url=' . $current_page . '" />' . K_NEWLINE; //reload page
echo '</head>' . K_NEWLINE;
echo '<body>' . K_NEWLINE;
echo '<main id="maincontent">' . K_NEWLINE;
echo '<a href="' . $current_page . '">' . $l['w_logout'] . '...</a>' . K_NEWLINE;
echo '</main>' . K_NEWLINE;
echo '</body>' . K_NEWLINE;
echo '</html>' . K_NEWLINE;
