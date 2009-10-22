<?php
//============================================================+
// File name   : tce_functions_html2txt.php                     
// Begin       : 2001-10-21
// Last Update : 2009-09-30
// 
// Description : Function to convert HTML code to Text string.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com S.r.l.
//               Via della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//
// License: 
//    Copyright (C) 2004-2009  Nicola Asuni - Tecnick.com S.r.l.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Function to convert HTML code to Text string.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright &copy; 2004-2009, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2003-03-31
 */

/**
 * Convert HTML code to Text string.
 * @param int $user_id User ID
 * @param string $user_email User email
 * @param string $user_verifycode user verification code
 */
function F_html_to_text($str, $preserve_newlines=false, $display_links=false) {
	require_once('../../shared/code/tce_functions_general.php');
	
	$dollar_replacement = ":.dlr.:"; //string replacement for dollar symbol
	
	//tags conversion table
	$tags2textTable = array (
		"'<br[^>]*?>'i" => "\n",
		"'<p[^>]*?>'i" => "\n",
		"'</p>'i" => "\n",
		"'<div[^>]*?>'i" => "\n",
		"'</div>'i" => "\n",
		"'<table[^>]*?>'i" => "\n",
		"'</table>'i" => "\n",
		"'<tr[^>]*?>'i" => "\n",
		"'<th[^>]*?>'i" => "\t ",
		"'<td[^>]*?>'i" => "\t ",
		"'<li[^>]*?>\t'i" => "\n",
		"'<h[0-9][^>]*?>'i" => "\n\n",
		"'</h[0-9]>'i" => "\n",
		"'<head[^>]*?>.*?</head>'si" => "\n",  // Strip out head
		"'<style[^>]*?>.*?</style>'si" => "\n",  // Strip out style
		"'<script[^>]*?>.*?</script>'si" => "\n"  // Strip out javascript
	);
	
	$str = str_replace("\r\n", "\n",  $str);
	
	$str = str_replace("\$", $dollar_replacement,  $str); //replace special character
	
	//remove session variable PHPSESSID from links
	$str = preg_replace("/(\?|\&|%3F|%26|\&amp;|%26amp%3B)PHPSESSID(=|%3D)[a-z0-9]{32,32}/i", "", $str); 
	
	//remove applet and get alternative content
	$str = preg_replace("/<applet[^>]*?>(.*?)<\/applet>/esi", "preg_replace(\"/<param[^>]*>/i\", \"\", \"\\1\")", $str);
	
	//remove object and get alternative content
	$str = preg_replace("/<object[^>]*?>(.*?)<\/object>/esi", "preg_replace(\"/<param[^>]*>/i\", \"\", \"\\1\")", $str);
	
	//indent list elements
	$firstposition = 0;
	while (($pos=strpos($str, "<ul")) > $firstposition) {
		$str = preg_replace("/<ul[^>]*?>(.*?)<\/ul>/esi", "preg_replace(\"/<li[^>]*>/i\", \"<li>\t\", \"\\1\")", $str);
		$firstposition = $pos;
	}
	$firstposition = 0;
	while (($pos=strpos($str, "<ol")) > $firstposition) {
		$str = preg_replace("/<ol[^>]*?>(.*?)<\/ol>/esi", "preg_replace(\"/<li[^>]*>/i\", \"<li>\t\", \"\\1\")", $str);
		$firstposition = $pos;
	}
	
	$str = preg_replace("'<img[^>]*alt[\s]*=[\s]*[\"\']*([^\"\'<>]*)[\"\'][^>]*>'i", "[IMAGE: \\1]",  $str);
	
	//give textual representation of links and images
	if ($display_links) {
		$str = preg_replace("'<a[^>]*href[\s]*=[\s]*[\"\']*([^\"\'<>]*)[\"\'][^>]*>(.*?)</a>'si", "\\2 [LINK: \\1]",  $str);
	}
	
	if (!$preserve_newlines){ //remove newlines
		$str = str_replace("\n", "",  $str);
	}
	
	$str = preg_replace(array_keys($tags2textTable), array_values($tags2textTable), $str);
		
	$str = preg_replace("'<[^>]*?>'si", "", $str); //strip out remaining tags
	
	//remove some newlines in excess
	$str = preg_replace("'[ \t\f]+[\r\n]'si", "\n",  $str);
	$str = preg_replace("'[\r\n][\r\n]+'si", "\n\n",  $str);
	
	$str = unhtmlentities($str, FALSE);
	
	$str = str_replace($dollar_replacement, "\$",  $str); //restore special character
	
	return stripslashes(trim($str));
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
