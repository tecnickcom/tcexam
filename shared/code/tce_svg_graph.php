<?php
//============================================================+
// File name   : tce_svg_graph.php
// Begin       : 2012-04-15
// Last Update : 2013-07-14
//
// Description : Create an SVG graph for user results.
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
//    Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Create an SVG graph for user results.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2012-04-15
 */

/**
 */

require_once('../../shared/code/tce_functions_svg_graph.php');

// points to graph (values between 0 and 100)
if (isset($_REQUEST['p'])) {
    $p = $_REQUEST['p'];
} else {
    exit;
}
// graph width
if (isset($_REQUEST['w'])) {
    $w = intval($_REQUEST['w']);
} else {
    $w = '';
}
// graph height
if (isset($_REQUEST['h'])) {
    $h = intval($_REQUEST['h']);
} else {
    $h = '';
}

F_getSVGGraph($p, $w, $h);

//============================================================+
// END OF FILE
//============================================================+
