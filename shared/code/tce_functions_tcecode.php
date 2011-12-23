<?php
//============================================================+
// File name   : tce_functions_tcecode.php
// Begin       : 2002-01-09
// Last Update : 2010-10-21
//
// Description : Functions to translate TCExam code
//               into XHTML.
//               The TCExam code is compatible to the common BBCode.
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com LTD
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
 * Functions to translate TCExam proprietary code into XHTML.
 * The TCExam code is compatible to the common BBCode.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2002-01-09
 */

/**
 * Returns XHTML code from text marked-up with TCExam Code Tags
 * @param $text_to_decode (string) text to convert
 * @return string XHTML code
 */
function F_decode_tcecode($text_to_decode) {
	require_once('../config/tce_config.php');
	global $l, $db;

	// Patterns and replacements
	$pattern = array();
	$replacement = array();
	$i=0;

	$newtext = htmlspecialchars($text_to_decode, ENT_NOQUOTES, $l['a_meta_charset']); // escape some special HTML characters

	// --- convert some BBCode to TCECode: ---
	// [*]list item - convert to new [li] tag
	$newtext = preg_replace("'\[\*\](.*?)\n'i", "[li]\\1[/li]",  $newtext);
	// [img]image[/img] - convert to new object tag
	$newtext = preg_replace("'\[img\](.*?)\[/img\]'si", "[object]\\1[/object]",  $newtext);
	// [img=WIDTHxHEIGHT]image[/img] - convert to new object tag
	$newtext = preg_replace("'\[img=(.*?)x(.*?)\](.*?)\[/img\]'si", "[object]\\3[/object:\\1:\\2]",  $newtext);
	// ---

	// [tex]LaTeX_code[/tex]
	$newtext = preg_replace_callback("#\[tex\](.*?)\[/tex\]#si", 'F_latex_callback', $newtext);

	// [object]object_url[/object:width:height:alt]
	$newtext = preg_replace_callback("#\[object\](.*?)\.(.*?)\[/object\:(.*?)\:(.*?)\:(.*?)\]#si", 'F_objects_callback', $newtext);
	// [object]object_url[/object:width:height]
	$newtext = preg_replace_callback("#\[object\](.*?)\.(.*?)\[/object\:(.*?)\:(.*?)\]#si", 'F_objects_callback', $newtext);
	// [object]object_url[/object]
	$newtext = preg_replace_callback("#\[object\](.*?)\.(.*?)\[/object\]#si", 'F_objects_callback', $newtext);

	// replace newline chars on [code] tag
	//$newtext = preg_replace("'\r\n'si", "\n",  $newtext);
	//$newtext = preg_replace("'\n\r'si", "\n",  $newtext);
	while (preg_match("'\[code\](.*?) (.*?)\[/code\]'si", $newtext)) {
		$newtext = preg_replace("'\[code\](.*?) (.*?)\[/code\]'si", "[code]\\1&nbsp;\\2[/code]",  $newtext);
	}
	/*
	while (preg_match("'\[code\](.*?)\n(.*?)\[/code\]'si", $newtext)) {
		$newtext = preg_replace("'\[code\](.*?)\n(.*?)\[/code\]'si", "[code]\\1@n@\\2[/code]",  $newtext);
	}*/

	// [url]http://www.domain.com[/url]
	$pattern[++$i] = "#\[url\](.*?)\[/url\]#si";
	$replacement[++$i] = '<a class="tcecode" href="\1">\1</a>';

	// [url=http://www.domain.com]linkname[/url]
	$pattern[++$i] = "#\[url=(.*?)\](.*?)\[/url\]#si";
	$replacement[++$i] = '<a class="tcecode" href="\1">\2</a>';

	// [dir=ltr]text direction: ltr, rtl[/dir]
	$pattern[++$i] = "#\[dir=(.*?)\](.*?)\[/dir\]#si";
	$replacement[++$i] = '<span dir="\1">\2</span>';

	// [align=left]text alignment: left, right, center, justify[/align]
	$pattern[++$i] = "#\[align=(.*?)\](.*?)\[/align\]#si";
	$replacement[++$i] = '<span style="text-align:\1;">\2</span>';

	// [code] and [/code] display text as source code
	$pattern[++$i] = "#\[code\](.*?)\[/code\]#si";
	$replacement[++$i] = '<div class="tcecodepre">\1</div>';

	// [small] and [/small] for small text
	$pattern[++$i] = "#\[small\](.*?)\[/small\]#si";
	$replacement[++$i] = '<small class="tcecode">\1</small>';

	// [b] and [/b] for bolding text.
	$pattern[++$i] = "#\[b\](.*?)\[/b\]#si";
	$replacement[++$i] = '<strong class="tcecode">\1</strong>';

	// [i] and [/i] for italicizing text.
	$pattern[++$i] = "#\[i\](.*?)\[/i\]#si";
	$replacement[++$i] = '<em class="tcecode">\1</em>';

	// [s] and [/s] for strikethrough text.
	$pattern[++$i] = "#\[s\](.*?)\[/s\]#si";
	$replacement[++$i] = '<span style="text-decoration:line-through;">\1</span>';

	// [u] and [/u] for underlined text.
	$pattern[++$i] = "#\[u\](.*?)\[/u\]#si";
	$replacement[++$i] = '<span style="text-decoration:underline;">\1</span>';

	// [o] and [/o] for overlined text.
	$pattern[++$i] = "#\[o\](.*?)\[/o\]#si";
	$replacement[++$i] = '<span style="text-decoration:overline;">\1</span>';

	// [sub] and [/sub] for subscript text.
	$pattern[++$i] = "#\[sub\](.*?)\[/sub\]#si";
	$replacement[++$i] = '<sub class="tcecode">\1</sub>';

	// [sup] and [/sup] for superscript text.
	$pattern[++$i] = "#\[sup\](.*?)\[/sup\]#si";
	$replacement[++$i] = '<sup class="tcecode">\1</sup>';

	// [ulist] and [/ulist] unordered list
	$pattern[++$i] = "#\[ulist\](.*?)\[/ulist\]#si";
	$replacement[++$i] = '<ul class="tcecode">\1</ul>';

	// [olist] and [/olist] ordered list.
	$pattern[++$i] = "#\[olist\](.*?)\[/olist\]#si";
	$replacement[++$i] = '<ol class="tcecode">\1</ol>';

	// [olist=1] and [/olist] ordered list.
	$pattern[++$i] = "#\[olist=1\](.*?)\[/olist\]#si";
	$replacement[++$i] = '<ol class="tcecode" style="list-style-type:arabic-numbers">\1</ol>';

	// [olist=a] and [/olist] ordered list.
	$pattern[++$i] = "#\[olist=a\](.*?)\[/olist\]#si";
	$replacement[++$i] = '<ol class="tcecode" style="list-style-type:lower-alpha">\1</ol>';

	// [li] list items [/li]
	$pattern[++$i] = "#\[li\](.*?)\[/li\]#si";
	$replacement[++$i] = '<li class="tcecode">\1</li>';

	// [color=#RRGGBB] and [/color]
	// [color=rgb(red,green,blue)] and [/color]
	// [color=html_color_name] and [/color]
	$pattern[++$i] = "#\[color=(.*?)\](.*?)\[/color\]#si";
	$replacement[++$i] = '<span style="color:\1">\2</span>';

	// [bgcolor=#RRGGBB] and [/bgcolor]
	// [bgcolor=rgb(red,green,blue)] and [/bgcolor]
	// [bgcolor=html_color_name] and [/bgcolor]
	$pattern[++$i] = "#\[bgcolor=(.*?)\](.*?)\[/bgcolor\]#si";
	$replacement[++$i] = '<span style="background-color:\1">\2</span>';

	// [size=percent] and [/size]
	$pattern[++$i] = "#\[size=(.*?)\](.*?)\[/size\]#si";
	$replacement[++$i] = '<span style="font-size:\1%">\2</span>';

	$newtext = preg_replace($pattern, $replacement, $newtext);

	// line breaks
	$newtext = preg_replace("'(\r\n|\n|\r)'", '<br />', $newtext);
	$newtext = str_replace('<br /><li', '<li', $newtext);
	$newtext = str_replace('</li><br />', '</li>', $newtext);
	$newtext = str_replace('<br /><param', '<param', $newtext);

	// restore newline chars on [code] tag
	//$newtext = preg_replace("'@n@'si", "\n",  $newtext);

	return ($newtext);
}

/**
 * Callback function for preg_replace_callback (LaTeX replacement).
 * Returns replacement image for LaTeX code.
 * @param $matches (string) array containing matches: $matches[0] is the complete match, $matches[1] the match for the first subpattern enclosed in '(...)' (the LaTeX code)
 * @return string replacement HTML code string to include the equivalent LaTeX image.
 */
function F_latex_callback($matches) {
	require_once('../../shared/code/tce_latexrender.php');
	$latex = new LatexRender();
	$latex_code = unhtmlentities($matches[1]);
	// get image URL
	$imgurl = $latex->getFormulaURL($latex_code);
	if ($imgurl) {
		// alternative text to image
		$alt_latex = '[LaTeX]'."\n".htmlentities($latex_code, ENT_QUOTES);
		$alt_latex = str_replace("\r", '&#13;', $alt_latex);
		$alt_latex = str_replace("\n", '&#10;', $alt_latex);
		// XHTML code for image
		$newtext = '<img src="'.$imgurl.'" alt="'.$alt_latex.'" class="tcecode" />';
	} else {
		$newtext = '[LaTeX: ERROR '.$latex->getErrorCode().']';
	}
	return $newtext;
}

/**
 * Callback function for preg_replace_callback.
 * Returns replacement code by MIME type.
 * @param $matches (string) array containing matches: $matches[0] is the complete match, $matches[1] the match for the first subpattern enclosed in '(...)' and so on
 * @return string replacement string by file extension
 */
function F_objects_callback($matches) {
	$width = 0;
	$height = 0;
	$alt = '';
	if(isset($matches[3]) AND ($matches[3] > 0)) {
		$width = $matches[3];
	}
	if(isset($matches[4]) AND ($matches[4] > 0)) {
		$height = $matches[4];
	}
	if(isset($matches[5]) AND (!empty($matches[5]))) {
		$alt = F_tcecodeToTitle($matches[5]);
	}
	return F_objects_replacement($matches[1], $matches[2], $width, $height, $alt);
}

/**
 * Returns the xhtml code needed to display the object by MIME type.
 * @param $name (string) object path excluded extension
 * @param $extension (string) object extension (e.g.: gif, jpg, swf, ...)
 * @param $width (int) object width
 * @param $height (int) object height
 * @param $maxwidth (int) object max or default width
 * @param $maxheight (int) object max or default height
 * @param $alt (string) alternative content
 * @return string replacement string
 */
function F_objects_replacement($name, $extension, $width=0, $height=0, $alt='', &$maxwidth=0, &$maxheight=0) {
	require_once('../config/tce_config.php');
	global $l, $db;
	$filename = $name.'.'.$extension;
	$extension = strtolower($extension);
	$htmlcode = '';
	switch($extension) {
		case 'gif':
		case 'jpg':
		case 'jpeg':
		case 'png':
		case 'svg': { // images
			$htmlcode = '<img src="'.K_PATH_URL_CACHE.$filename.'"';
			if (!empty($alt)) {
				$htmlcode .= ' alt="'.$alt.'"';
			} else {
				$htmlcode .= ' alt="image:'.$filename.'"';
			}
			$imsize = @getimagesize(K_PATH_CACHE.$filename);
			if ($imsize !== false) {
				list($pixw, $pixh) = $imsize;
				if (($width <= 0) AND ($height <= 0)) {
					// get default size
					$width = $pixw;
					$height = $pixh;
				} elseif ($width <= 0) {
					$width = $height * $pixw / $pixh;
				} elseif ($height <= 0) {
					$height = $width * $pixh / $pixw;
				}
			}
			$ratio = 1;
			if (($width > 0) AND ($height > 0)) {
				$ratio = $width / $height;
			}
			// fit image on max dimensions
			if (($maxwidth > 0) AND ($width > $maxwidth)) {
				$width = $maxwidth;
				$height = round($width / $ratio);
				$maxheight = min($maxheight, $height);
			}
			if (($maxheight > 0) AND ($height > $maxheight)) {
				$height = $maxheight;
				$width = round($height * $ratio);
			}
			// print size
			if ($width > 0) {
				$htmlcode .= ' width="'.$width.'"';
			}
			if ($height > 0) {
				$htmlcode .= ' height="'.$height.'"';
			}
			$htmlcode .= ' class="tcecode" />';
			if ($imsize !== false) {
				$maxwidth = $pixw;
				$maxheight = $pixh;
			}
			break;
		}
		default: {
			include('../../shared/config/tce_mime.php');
			if (isset($mime[$extension])) {
				$htmlcode = '<object type="'.$mime[$extension].'" data="'.K_PATH_URL_CACHE.$filename.'"';
				if ($width >0) {
					$htmlcode .= ' width="'.$width.'"';
				} elseif ($maxwidth > 0) {
					$htmlcode .= ' width="'.$maxwidth.'"';
				}
				if ($height >0) {
					$htmlcode .= ' height="'.$height.'"';
				} elseif ($maxheight > 0) {
					$htmlcode .= ' height="'.$maxheight.'"';
				}
				$htmlcode .= '>';
				$htmlcode .= '<param name="type" value="'.$mime[$extension].'" />';
				$htmlcode .= '<param name="src" value="'.K_PATH_URL_CACHE.$filename.'" />';
				$htmlcode .= '<param name="filename" value="'.K_PATH_URL_CACHE.$filename.'" />';
				if ($width > 0) {
					$htmlcode .= '<param name="width" value="'.$width.'" />';
				} elseif ($maxwidth > 0) {
					$htmlcode .= '<param name="width" value="'.$maxwidth.'" />';
				}
				if ($height > 0) {
					$htmlcode .= '<param name="height" value="'.$height.'" />';
				} elseif ($maxheight > 0) {
					$htmlcode .= '<param name="height" value="'.$maxheight.'" />';
				}
				if (!empty($alt)) {
					$htmlcode .= ''.$alt.'';
				} else {
					$htmlcode .= '['.$mime[$extension].']:'.$filename.'';
				}
				$htmlcode .= '</object>';
			} else {
				$htmlcode = '[ERROR - UNKNOW MIME TYPE FOR: '.$extension.']';
			}
			break;
		}
	}
	return $htmlcode;
}

/**
 * Returns specified string without tcecode mark-up tags
 * @param $str (string) text to process
 * @return string without tcecode markup tags
 */
function F_remove_tcecode($str) {
	$str = preg_replace("'\[object\](.*?)\[/object([^\]]*?)\]'si", '[OBJ]',  $str);
	$str = preg_replace("'\[img([^\]]*?)\](.*?)\[/img\]'si", '[IMG]',  $str);
	$str = preg_replace("'\[code\](.*?)\[/code\]'si", '\1',  $str);
	$str = preg_replace("'\[b\](.*?)\[/b\]'si", '\1',  $str);
	$str = preg_replace("'\[i\](.*?)\[/i\]'si", '\1',  $str);
	$str = preg_replace("'\[s\](.*?)\[/s\]'si", '\1',  $str);
	$str = preg_replace("'\[u\](.*?)\[/u\]'si", '\1',  $str);
	$str = preg_replace("'\[o\](.*?)\[/o\]'si", '\1',  $str);
	$str = preg_replace("'\[color([^\]]*?)\](.*?)\[/color\]'si", '\2',  $str);
	$str = preg_replace("'\[bgcolor([^\]]*?)\](.*?)\[/bgcolor\]'si", '\2',  $str);
	$str = preg_replace("'\[size([^\]]*?)\](.*?)\[/size\]'si", '\2',  $str);
	$str = preg_replace("'\[small\](.*?)\[/small\]'si", '\1',  $str);
	$str = preg_replace("'\[sub\](.*?)\[/sub\]'si", '\1',  $str);
	$str = preg_replace("'\[sup\](.*?)\[/sup\]'si", '\1',  $str);
	$str = preg_replace("'\[url([^\]]*?)\](.*?)\[/url\]'si", '\2',  $str);
	$str = preg_replace("'\[li\](.*?)\[/li\]'si", ' * \1',  $str);
	$str = preg_replace("'\[\*\](.*?)\n'i", ' * \1',  $str);
	$str = preg_replace("'\[ulist\](.*?)\[/ulist\]'si", '\1',  $str);
	$str = preg_replace("'\[olist([^\]]*?)\](.*?)\[/olist\]'si", '\2',  $str);
	$str = preg_replace("'\[tex\](.*?)\[/tex\]'si", '[TEX]',  $str);
	return $str;
}

/**
 * Converts tcecode text to a single XHTML string removing some objects.
 * @param $str (string) text to process
 * return string
 */
function F_tcecodeToLine($str) {
	$str = preg_replace("'\[object\](.*?)\[/object([^\]]*?)\]'si", '[OBJ]',  $str);
	$str = preg_replace("'\[img([^\]]*?)\](.*?)\[/img\]'si", '[IMG]',  $str);
	$str = preg_replace("'\[code\](.*?)\[/code\]'si", '\1',  $str);
	$str = preg_replace("'\[li\](.*?)\[/li\]'si", ' * \1',  $str);
	$str = preg_replace("'\[\*\](.*?)\n'i", ' * \1',  $str);
	$str = preg_replace("'\[ulist\](.*?)\[/ulist\]'si", '\1',  $str);
	$str = preg_replace("'\[olist([^\]]*?)\](.*?)\[/olist\]'si", '\2',  $str);
	$str = preg_replace("'\[url([^\]]*?)\](.*?)\[/url\]'si", '\2',  $str);
	$str = preg_replace("'\[tex\](.*?)\[/tex\]'si", '[TEX]',  $str);
	$str = F_compact_string($str);
	$str = F_decode_tcecode($str);
	$str = F_compact_string($str);
	if (strlen($str) > K_QUESTION_LINE_MAX_LENGTH) {
		$str = F_substrHTML($str, K_QUESTION_LINE_MAX_LENGTH, 20).' ...';
	}
	return $str;
}

/**
 * Converts tcecode text to simple string for XHTML title attribute.
 * @param $str (string) text to process
 * return string
 */
function F_tcecodeToTitle($str) {
	require_once('../config/tce_config.php');
	global $l;
	$str = F_remove_tcecode($str);
	$str = F_compact_string($str);
	$str = htmlspecialchars($str, ENT_COMPAT, $l['a_meta_charset']);
	return $str;
}

/**
 * Return a substring of XHTML code while making sure no html tags are chopped.
 * It also prevents chopping while a tag is still open.
 * this function is based on a public-domain script posted on www.php.net by fox@conskript.server and mr@bbp.biz
 * @param $htmltext (string)
 * @param $min_length (int) (default=100) the approximate length you want the concatenated text to be
 * @param $offset_length (int) (default=20) the max variation in how long the text can be
 */
function F_substrHTML($htmltext, $min_length=100, $offset_length=20) {
	// Reset tag counter and quote checker
	$tag_counter = 0;
	$quotes_on = FALSE;
	// Check if the text is too long
	if (strlen($htmltext) > $min_length) {
		// Reset the tag_counter and pass through (part of) the entire text
		$c = 0;
		for ($i = 0; $i < strlen($htmltext); $i++) {
			// Load the current character and the next one if the string has not arrived at the last character
			$current_char = substr($htmltext, $i, 1);
			if ($i < strlen($htmltext) - 1) {
				$next_char = substr($htmltext, $i + 1, 1);
			} else {
				$next_char = '';
			}
			// First check if quotes are on
			if (!$quotes_on) {
				// Check if it's a tag On a "<" add 3 if it's an opening tag (like <a href...) or add only 1 if it's an ending tag (like </a>)
				if ($current_char == '<') {
					if ($next_char == '/') {
						$tag_counter += 1;
					} else {
						$tag_counter += 3;
					}
				}
				// Slash signifies an ending (like </a> or ... />) substract 2
				if (($current_char == '/') AND ($tag_counter != 0)) {
					$tag_counter -= 2;
				}
				// On a ">" substract 1
				if ($current_char == '>') {
					$tag_counter -= 1;
				}
				// If quotes are encountered, start ignoring the tags (for directory slashes)
				if ($current_char == '"') {
					$quotes_on = true;
				}
			} else {
				// IF quotes are encountered again, turn it back off
				if ($current_char == '"') {
					$quotes_on = false;
				}
			}
			// Count only the chars outside html tags
			if(($tag_counter == 2) OR ($tag_counter == 0)){
				$c++;
			}
			// Check if the counter has reached the minimum length yet,
			// then wait for the tag_counter to become 0, and chop the string there
			if (($c > $min_length - $offset_length) AND ($tag_counter == 0) AND ($next_char == ' ')) {
				$htmltext = substr($htmltext,0,$i + 1);
				return $htmltext;
			}
		}
	}
	return $htmltext;
}

//============================================================+
// END OF FILE
//============================================================+
