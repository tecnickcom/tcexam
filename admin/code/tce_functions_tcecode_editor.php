<?php
//============================================================+
// File name   : tce_functions_tcecode_editor.php
// Begin       : 2002-02-20
// Last Update : 2011-07-12
//
// Description : TCExam Code Editor (editor for special mark-up
//               code used to add some text formatting)
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
//    Copyright (C) 2004-2011 Nicola Asuni - Tecnick.com LTD
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
 * Functions for custom mark-up language editor.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2002-02-20
 */

/**
 * Display TCExam Code EDITOR Tag Buttons
 * @author Nicola Asuni
 * @since 2006-03-07
 * @param $callingform (string) name of calling xhtml form
 * @param $callingfield (string) name of calling form field (textarea where output code will be sent)
 * @param $id (int) DEPRECATED -- id appended to input fields names to differentiate from previous buttons.
 * @return XHTML string
 */
function tcecodeEditorTagButtons($callingform, $callingfield, $id=0) {
	global $l, $db;
	global $uploadedfile;
	require_once('../config/tce_config.php');

	// sanitize input parameters
	$callingform = preg_replace('/[^a-z0-9_]/', '', $callingform);
	$callingfield = preg_replace('/[^a-z0-9_]/', '', $callingfield);
	$id = intval($id);

	$buttons = '';
	$buttons .= '<script src="'.K_PATH_SHARED_JSCRIPTS.'inserttag.js" type="text/javascript"></script>'.K_NEWLINE;

	// --- buttons

	$onclick = 'FJ_undo(document.getElementById(\''.$callingform.'\').'.$callingfield.')';
	$buttons .= getImageButton($l['w_undo'], '', K_PATH_IMAGES.'buttons/undo.gif', $onclick, 'z');

	$onclick = 'FJ_redo(document.getElementById(\''.$callingform.'\').'.$callingfield.')';
	$buttons .= getImageButton($l['w_redo'], '', K_PATH_IMAGES.'buttons/redo.gif', $onclick, 'y');

	$onclick = 'FJ_insert_tag(document.getElementById(\''.$callingform.'\').'.$callingfield.'';
	$buttons .= getImageButton('bold', '[b]', K_PATH_IMAGES.'buttons/bold.gif', $onclick, 'b');
	$buttons .= getImageButton('italic', '[i]', K_PATH_IMAGES.'buttons/italic.gif', $onclick, 'i');
	$buttons .= getImageButton('underline', '[u]', K_PATH_IMAGES.'buttons/under.gif', $onclick, 'u');
	$buttons .= getImageButton('strikethrough', '[s]', K_PATH_IMAGES.'buttons/strike.gif', $onclick, 'd');
	$buttons .= getImageButton('small', '[small]', K_PATH_IMAGES.'buttons/small.gif', $onclick, 's');
	$buttons .= getImageButton('subscript', '[sub]', K_PATH_IMAGES.'buttons/subscr.gif', $onclick, 'v');
	$buttons .= getImageButton('superscript', '[sup]', K_PATH_IMAGES.'buttons/superscr.gif', $onclick, 'a');
	$buttons .= getImageButton('link', '[url]', K_PATH_IMAGES.'buttons/link.gif', $onclick, 'k');
	$buttons .= getImageButton('unordered list', '[ulist]', K_PATH_IMAGES.'buttons/bullist.gif', $onclick, 'l');
	$buttons .= getImageButton('ordered list', '[olist]', K_PATH_IMAGES.'buttons/numlist.gif', $onclick, 'o');
	$buttons .= getImageButton('list item', '[li]', K_PATH_IMAGES.'buttons/li.gif', $onclick, 't');
	$buttons .= getImageButton('LRT', '[dir=ltr]', K_PATH_IMAGES.'buttons/ltrdir.gif', $onclick, '');
	$buttons .= getImageButton('RTL', '[dir=rtl]', K_PATH_IMAGES.'buttons/rtldir.gif', $onclick, '');

	$onclick = 'window.open(\'tce_colorpicker.php?frm='.$callingform.'&amp;fld='.$callingfield.'&amp;tag=bgcolor\',\'colorpicker\',\'height=550,width=330,resizable=yes,menubar=no,scrollbars=no,toolbar=no,directories=no,status=no,modal=yes\');';
	$buttons .= getImageButton('background-color', '', K_PATH_IMAGES.'buttons/bgcolor.gif', $onclick, '');

	$onclick = 'window.open(\'tce_colorpicker.php?frm='.$callingform.'&amp;fld='.$callingfield.'&amp;tag=color\',\'colorpicker\',\'height=550,width=330,resizable=yes,menubar=no,scrollbars=no,toolbar=no,directories=no,status=no,modal=yes\');';
	$buttons .= getImageButton('color', '', K_PATH_IMAGES.'buttons/color.gif', $onclick, '');

	$onclick = 'FJ_insert_tag(document.getElementById(\''.$callingform.'\').'.$callingfield.'';
	$buttons .= getImageButton('code', '[code]', K_PATH_IMAGES.'buttons/code.gif', $onclick, 'c');
	$buttons .= getImageButton('latex', '[tex]', K_PATH_IMAGES.'buttons/latex.gif', $onclick, 'm');

	$onclick = 'window.open(\'tce_select_mediafile.php?frm='.$callingform.'&amp;fld='.$callingfield.'\',\'mediaselect\',\'height=600,width=680,resizable=yes,menubar=no,scrollbars=yes,toolbar=no,directories=no,status=no,modal=yes\');';
	$buttons .= getImageButton('object', '', K_PATH_IMAGES.'buttons/image.gif', $onclick, '');
	return $buttons;
}

/**
 * Display one tag button
 * @param $name (string) name of the button
 * @param $tag (string) tag value
 * @param $image (string) image file of button
 * @param $onclick (string) default onclick action
 * @param $accesskey (string) accesskey: character for keyboard shortcut
 * @return XHTML string
 * @author Nicola Asuni
 * @since 2006-03-07
 */
function getImageButton($name, $tag, $image, $onclick='', $accesskey='') {
	if (strlen($tag) > 0) {
		$onclick = $onclick.', \''.$tag.'\')';
	}
	$str = '<a href="#" onclick="'.$onclick.'" title="'.$name.' ['.$accesskey.']" accesskey="'.$accesskey.'">';
	$str .= '<img src="'.$image.'" alt="'.$name.' ['.$accesskey.']" class="button" />';
	$str .= '</a>';
	return $str;
}

//============================================================+
// END OF FILE
//============================================================+
