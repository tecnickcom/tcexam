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
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//    Additionally, you can't remove, move or hide the original TCExam logo,
//    copyrights statements and links to Tecnick.com and TCExam websites.
//
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
