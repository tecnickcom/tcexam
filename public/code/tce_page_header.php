<?php
//============================================================+
// File name   : tce_page_header.php
// Begin       : 2001-09-18
// Last Update : 2010-09-20
//
// Description : Outputs default XHTML page header.
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
 * Outputs default XHTML page header.
 * @package com.tecnick.tcexam.public
 * @author Nicola Asuni
 * @since 2001-09-18
 */

/**
 */

require_once('tce_xhtml_header.php');

// display header (image logo + timer)
echo '<div class="header">'.K_NEWLINE;
echo '<div class="left"></div>'.K_NEWLINE;
echo '<div class="right">'.K_NEWLINE;
echo '<a name="timersection" id="timersection"></a>'.K_NEWLINE;
include('../../shared/code/tce_page_timer.php');
echo '</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

// display menu
echo '<div id="scrollayer" class="scrollmenu">'.K_NEWLINE;
// CSS changes for old browsers
echo '<!--[if lte IE 7]>'.K_NEWLINE;
echo '<style type="text/css">'.K_NEWLINE;
echo 'ul.menu li {text-align:left;behavior:url("../../shared/jscripts/IEmen.htc");}'.K_NEWLINE;
echo 'ul.menu ul {background-color:#003399;margin:0;padding:0;display:none;position:absolute;top:20px;left:0px;}'.K_NEWLINE;
echo 'ul.menu ul li {width:200px;text-align:left;margin:0;}'.K_NEWLINE;
echo 'ul.menu ul ul {display:none;position:absolute;top:0px;left:190px;}'.K_NEWLINE;
echo '</style>'.K_NEWLINE;
echo '<![endif]-->'.K_NEWLINE;
require_once(dirname(__FILE__).'/tce_page_menu.php');
echo '</div>'.K_NEWLINE;

echo '<div class="body">'.K_NEWLINE;

echo '<a name="topofdoc" id="topofdoc"></a>'.K_NEWLINE;
echo '<h1>'.htmlspecialchars($thispage_title, ENT_NOQUOTES, $l['a_meta_charset']).'</h1>'.K_NEWLINE;

//============================================================+
// END OF FILE
//============================================================+
