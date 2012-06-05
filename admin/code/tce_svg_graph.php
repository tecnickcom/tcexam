<?php
//============================================================+
// File name   : tce_svg_graph.php
// Begin       : 2012-04-15
// Last Update : 2012-04-15
//
// Description : Create an SVG graph for user results.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               Manor Coach House, Church Hill
//               Aldershot, Hants, GU12 4RQ
//               UK
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2012  Nicola Asuni - Tecnick.com LTD
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

// points to graph (values between 0 and 100)
if (isset($_REQUEST['p'])) {
	$points = explode('x', $_REQUEST['p']);
} else {
	exit;
}
// count the of points
$numpoints = count($points);
// vertical and horizontal space to leave for labels
$label_space = 35;
// graph width
$width = ($label_space + ($numpoints * 2));
if (isset($_REQUEST['w'])) {
	$width = max($width, intval($_REQUEST['w']));
}
// graph height
$height = 200 + $label_space;
if (isset($_REQUEST['h'])) {
	$height = max($height, intval($_REQUEST['h']));
}
// graph colors
$color = array('ff0000', '0000ff');

// font size for labels
$fontsize = sprintf('%.3F', ($label_space / 3));

// create SVG graph
$svg = '<'.'?'.'xml version="1.0" standalone="no"'.'?'.'>'."\n";
$svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'."\n";
$svg .= '<svg width="'.$width.'" height="'.$height.'" version="1.1" xmlns="http://www.w3.org/2000/svg">'."\n";

// draw horizontal grids
$vstep = floor(($height - $label_space) / 11);
$pstep = ($vstep / 10);
$hw = ($width - 4);
$textpos = ($label_space * 0.8);
$svg .= '<g stroke="#cccccc" fill="#666666" stroke-width="1" text-anchor="end" font-family="Arial,Verdana" font-size="'.$fontsize.'">'."\n";
for ($i = 0; $i <= 10; ++$i) {
	$y = (($i + 1) * $vstep);
	// text
	$svg .= "\t".'<text x="'.$textpos.'" y="'.$y.'" stroke-width="0">'.(100 - ($i * 10)).'%</text>'."\n";
	// line
	$svg .= "\t".'<line x1="'.$label_space.'" y1="'.$y.'" x2="'.$hw.'" y2="'.$y.'" />'."\n";
}
$svg .= '</g>'."\n";

// draw vertical grids and points
$hstep = floor(($hw - $label_space) / ($numpoints - 1));
$vh = ($height - $label_space);
$textpos = $vh + ($label_space * 0.5);
$svg .= '<g stroke="#cccccc" fill="#666666" stroke-width="1" text-anchor="end" font-family="Arial,Verdana" font-size="'.$fontsize.'">'."\n";
$graph = array('', '');
for ($i = 0; $i < $numpoints; ++$i) {
	$point = explode('v', $points[$i]);
	$x = (($i * $hstep) + $label_space);
	// line
	$svg .= "\t".'<line x1="'.$x.'" y1="'.$vstep.'" x2="'.$x.'" y2="'.$vh.'" />'."\n";
	// text
	$svg .= "\t".'<text x="'.$x.'" y="'.$textpos.'" stroke-width="0">'.($i + 1).'</text>'."\n";
	for ($k = 0; $k <= 1; ++$k) {
		// graph path
		$y = sprintf('%.3F', (11 * $vstep) - (intval($point[$k]) * $pstep));
		$graph[$k] .= ' '.$x.','.$y;
		// point
		$svg .= "\t".'<circle cx="'.$x.'" cy="'.$y.'" r="4" stroke-width="0" fill="#'.$color[$k].'" />'."\n";
	}
}
$svg .= '</g>'."\n";

// draw graph
for ($k = 0; $k <= 1; ++$k) {
	$svg .= '<path fill-opacity="0.2" fill="#'.$color[$k].'" stroke-width="0" d="M '.$label_space.' '.(11 * $vstep).' L '.$graph[$k].' '.((($numpoints - 1) * $hstep) + $label_space).' '.(11 * $vstep).' Z" />'."\n";
	$svg .= '<polyline fill="none" stroke="#'.$color[$k].'" stroke-width="2" points="'.$graph[$k].'" />'."\n";
}

// close SVG graph
$svg .= '</svg>'."\n";

// send headers
header('Content-Description: SVG Data');
header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Content-Type: image/svg+xml');
header('Content-Disposition: inline; filename="srvusg.svg";');
// Turn on output buffering with the gzhandler
ob_start('ob_gzhandler');
// output SVG code
echo $svg;

//============================================================+
// END OF FILE
//============================================================+
