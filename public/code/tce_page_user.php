<?php
//============================================================+
// File name   : tce_page_user.php
// Begin       : 2010-09-20
// Last Update : 2010-09-20
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
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Output XHTML unordered list menu for user.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2010-09-20
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_PAGE_USER;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['w_user'];
require_once('../code/tce_page_header.php');

echo '<div class="container">'.K_NEWLINE;

// print submenu
echo '<ul>'.K_NEWLINE;
foreach ($menu['tce_page_user.php']['sub'] as $link => $data) {
    echo F_menu_link($link, $data, 1);
}
echo '</ul>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
