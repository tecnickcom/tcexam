<?php
//============================================================+
// File name   : tce_logout.php
// Begin       : 2001-09-28
// Last Update : 2010-10-04
//
// Description : Destroy user's session (logout).
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Destroy user's session (logout).
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2001-09-28
 */

/**
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_functions_session.php');

// Destroys all user's session data
session_unset();
session_destroy();
// destroy session ID cookie
setcookie('PHPSESSID', '', 1, K_COOKIE_PATH, K_COOKIE_DOMAIN, K_COOKIE_SECURE);

$login_page = '../code/index.php?logout=1';

echo '<'.'?xml version="1.0" encoding="'.$l['a_meta_charset'].'"?'.'>'.K_NEWLINE;
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.K_NEWLINE;
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$l['a_meta_language'].'" lang="'.$l['a_meta_language'].'" dir="'.$l['a_meta_dir'].'">'.K_NEWLINE;
echo '<head>'.K_NEWLINE;
echo '<title>LOGOUT</title>'.K_NEWLINE;
echo '<meta http-equiv="refresh" content="0;url='.$login_page.'" />'.K_NEWLINE; //reload page
echo '</head>'.K_NEWLINE;
echo '<body>'.K_NEWLINE;
echo '<a href="'.$login_page.'">LOGOUT...</a>'.K_NEWLINE;
echo '</body>'.K_NEWLINE;
echo '</html>'.K_NEWLINE;

//============================================================+
// END OF FILE
//============================================================+
