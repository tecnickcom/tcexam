<?php

//============================================================+
// File name   : tce_login.php
// Begin       : 2002-03-21
// Last Update : 2023-11-30
//
// Description : Display Login interface and redirect to main
//               page.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Display Login interface and redirect to main page.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2002-03-21
 */

require_once '../config/tce_config.php';

$pagelevel = 1;
require_once '../../shared/code/tce_authorization.php';

echo '<!DOCTYPE html>' . K_NEWLINE;
echo '<html lang="' . $l['a_meta_language'] . '" dir="' . $l['a_meta_dir'] . '">' . K_NEWLINE;
echo '<head>' . K_NEWLINE;
echo '<meta charset="' . $l['a_meta_charset'] . '" />' . K_NEWLINE;
echo '<title>' . htmlspecialchars($l['w_login'], ENT_COMPAT, $l['a_meta_charset']) . '</title>' . K_NEWLINE;
echo '<meta http-equiv="refresh" content="0;url=' . K_MAIN_PAGE . '" />' . K_NEWLINE; //reload page
echo '</head>' . K_NEWLINE;
echo '<body>' . K_NEWLINE;
echo '<main id="maincontent">' . K_NEWLINE;
echo
    '<a href="'
        . htmlspecialchars(urldecode(K_MAIN_PAGE), ENT_COMPAT, $l['a_meta_charset'])
        . '">'
        . $l['w_login']
        . '...</a>'
        . K_NEWLINE
;
echo '</main>' . K_NEWLINE;
echo '</body>' . K_NEWLINE;
echo '</html>' . K_NEWLINE;
