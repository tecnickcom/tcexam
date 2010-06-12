<?php
//============================================================+
// File name   : tce_functions_tcecode_editor.php
// Begin       : 2002-02-20
// Last Update : 2010-06-12
//
// Description : TCExam Code Editor (editor for special mark-up
//               code used to add some text formatting)
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
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com S.r.l.
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
 * Functions for custom mark-up language editor.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2002-02-20
 */

/**
 */

require_once('../config/tce_config.php');

// upload files
$uploadedfile = array();
for ($id = 0; $id < 2; ++$id) {
	if(isset($_POST['sendfile'.$id]) AND ($_FILES['userfile'.$id]['name'])) {
		require_once('../code/tce_functions_upload.php');
		$uploadedfile['\''.$id.'\''] = F_upload_file('userfile'.$id, K_PATH_CACHE);
	}
}

/**
 * Display TCExam Code EDITOR Tag Buttons
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2006-03-07
 * @param string $callingform name of calling xhtml form
 * @param string $callingfield name of calling form field (textarea where output code will be sent)
 * @param int $id id appended to input fields names to differentiate from previous buttons.
 * @return XHTML string
 */
function tcecodeEditorTagButtons($callingform, $callingfield, $id=0) {
	global $l, $db;
	global $uploadedfile;
	require_once('../config/tce_config.php');

	$buttons = '';
	$buttons .= '<script src="'.K_PATH_SHARED_JSCRIPTS.'inserttag.js" type="text/javascript"></script>'.K_NEWLINE;

	// --- buttons

	$onclick = 'FJ_undo(document.getElementById(\''.$callingform.'\').'.$callingfield.')';
	$buttons .= getImageButton($callingform, $callingfield, $l['w_undo'], '', K_PATH_IMAGES.'buttons/undo.gif', $onclick, 'z');

	$onclick = 'FJ_redo(document.getElementById(\''.$callingform.'\').'.$callingfield.')';
	$buttons .= getImageButton($callingform, $callingfield, $l['w_redo'], '', K_PATH_IMAGES.'buttons/redo.gif', $onclick, 'y');

	$onclick = 'FJ_insert_tag(document.getElementById(\''.$callingform.'\').'.$callingfield.'';
	$buttons .= getImageButton($callingform, $callingfield, 'bold', '[b]', K_PATH_IMAGES.'buttons/bold.gif', $onclick, 'b');
	$buttons .= getImageButton($callingform, $callingfield, 'italic', '[i]', K_PATH_IMAGES.'buttons/italic.gif', $onclick, 'i');
	$buttons .= getImageButton($callingform, $callingfield, 'underline', '[u]', K_PATH_IMAGES.'buttons/under.gif', $onclick, 'u');
	$buttons .= getImageButton($callingform, $callingfield, 'strikethrough', '[s]', K_PATH_IMAGES.'buttons/strike.gif', $onclick, 'd');
	$buttons .= getImageButton($callingform, $callingfield, 'small', '[small]', K_PATH_IMAGES.'buttons/small.gif', $onclick, 's');
	$buttons .= getImageButton($callingform, $callingfield, 'subscript', '[sub]', K_PATH_IMAGES.'buttons/subscr.gif', $onclick, 'v');
	$buttons .= getImageButton($callingform, $callingfield, 'superscript', '[sup]', K_PATH_IMAGES.'buttons/superscr.gif', $onclick, 'a');
	$buttons .= getImageButton($callingform, $callingfield, 'link', '[url]', K_PATH_IMAGES.'buttons/link.gif', $onclick, 'k');
	$buttons .= getImageButton($callingform, $callingfield, 'unordered list', '[ulist]', K_PATH_IMAGES.'buttons/bullist.gif', $onclick, 'l');
	$buttons .= getImageButton($callingform, $callingfield, 'ordered list', '[olist]', K_PATH_IMAGES.'buttons/numlist.gif', $onclick, 'o');
	$buttons .= getImageButton($callingform, $callingfield, 'list item', '[li]', K_PATH_IMAGES.'buttons/li.gif', $onclick, 't');
	$buttons .= getImageButton($callingform, $callingfield, 'code', '[code]', K_PATH_IMAGES.'buttons/code.gif', $onclick, 'c');
	$buttons .= getImageButton($callingform, $callingfield, 'latex', '[tex]', K_PATH_IMAGES.'buttons/latex.gif', $onclick, 'm');

	$onclick = 'document.getElementById(\'bgcolor'.$id.'\').value=window.showModalDialog(\'tce_colorpicker.php\',\'\',\'dialogHeight:600px;dialogWidth:330px;resizable:yes;scroll:no;status:no;\');FJ_insert_tag(document.getElementById(\''.$callingform.'\').'.$callingfield.'';
	$buttons .= getImageButton($callingform, $callingfield, 'background-color', '[bgcolor=\'+document.getElementById(\'bgcolor'.$id.'\').value+\']', K_PATH_IMAGES.'buttons/bgcolor.gif', $onclick, '');

	$onclick = 'document.getElementById(\'color'.$id.'\').value=window.showModalDialog(\'tce_colorpicker.php\',\'\',\'dialogHeight:600px;dialogWidth:330px;resizable:yes;scroll:no;status:no;\');FJ_insert_tag(document.getElementById(\''.$callingform.'\').'.$callingfield.'';
	$buttons .= getImageButton($callingform, $callingfield, 'color', '[color=\'+document.getElementById(\'color'.$id.'\').value+\']', K_PATH_IMAGES.'buttons/color.gif', $onclick, '');

	$buttons .= '<input type="hidden" name="bgcolor'.$id.'" id="bgcolor'.$id.'" value="" />';
	$buttons .= '<input type="hidden" name="color'.$id.'" id="color'.$id.'" value="" />';

	// --- insert image/object
	$buttons .= '<br />'.K_NEWLINE;
	$buttons .= '<label for="selectobject'.$id.'">'.$l['w_image'].' / '.$l['w_object'].'</label>'.K_NEWLINE;
	$buttons .= '<select name="selectobject'.$id.'" id="selectobject'.$id.'" size="0">'.K_NEWLINE;
	$buttons .= '<option value="">&nbsp;</option>'.K_NEWLINE;
	$files_list = getDirFiles(K_PATH_CACHE, true, strlen(K_PATH_CACHE));
	foreach($files_list as $file) {
		$buttons .= '<option value="'.$file.'"';
		if (isset($uploadedfile['\''.$id.'\'']) AND (strcmp($uploadedfile['\''.$id.'\''], $file) == 0)) {
			$buttons .= ' selected="selected"';
		}
		$buttons .= '>'.$file.'</option>'.K_NEWLINE;
	}

	$buttons .= '</select>'.K_NEWLINE;

	$buttons .= '<br /><label for="object_alt'.$id.'">'.$l['w_description'].'</label>'.K_NEWLINE;
	$buttons .= '<input type="text" name="object_alt'.$id.'" id="object_alt'.$id.'" value="" size="30" maxlength="255" title="'.$l['w_description'].'"/>'.K_NEWLINE;

	$buttons .= '<br /><label for="object_width'.$id.'">'.$l['w_width'].'</label>'.K_NEWLINE;
	$buttons .= '<input type="text" name="object_width'.$id.'" id="object_width'.$id.'" value="" size="3" maxlength="5" title="'.$l['h_object_width'].'"/>'.K_NEWLINE;
	$buttons .= '<label for="object_height'.$id.'">'.$l['w_height'].'</label>'.K_NEWLINE;
	$buttons .= '<input type="text" name="object_height'.$id.'" id="object_height'.$id.'" value="" size="3" maxlength="5" title="'.$l['h_object_height'].'"/>'.K_NEWLINE;

	$onclick = 'FJ_insert_text(document.getElementById(\''.$callingform.'\').'.$callingfield.',\'[object]\'+document.getElementById(\'selectobject'.$id.'\').value+\'[/object:\'+document.getElementById(\'object_width'.$id.'\').value+\':\'+document.getElementById(\'object_height'.$id.'\').value+\':\'+document.getElementById(\'object_alt'.$id.'\').value+\']\');';

	$buttons .= '<input type="button" name="addobject'.$id.'" id="addobject'.$id.'" value="'.$l['w_add'].'" onclick="'.$onclick.'" title="'.$l['h_add_object'].'" />'.K_NEWLINE;

	// --- upload object file
	$buttons .= '<br />'.K_NEWLINE;
	$buttons .= '<label for="userfile'.$id.'">'.$l['w_upload_file'].'</label>'.K_NEWLINE;
	if ($id == 0) {
		$buttons .= '<input type="hidden" name="MAX_FILE_SIZE" value="'.K_MAX_UPLOAD_SIZE.'" />'.K_NEWLINE;
	}
	$buttons .= '<input type="file" name="userfile'.$id.'" id="userfile'.$id.'" size="20" title="'.$l['h_upload_file'].'" />'.K_NEWLINE;
	$buttons .= '<input type="submit" name="sendfile'.$id.'" id="sendfile'.$id.'" value="'.$l['w_upload'].'" title="'.$l['h_upload_file'].'" />'.K_NEWLINE;

	return $buttons;
}

/**
 * Display one tag button
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2006-03-07
 * @param string $callingform name of calling xhtml form
 * @param string $callingfield name of calling form field (textarea where output code will be sent)
 * @param string $name name of the button
 * @param string $tag tag value
 * @param string $image image file of button
 * @param string $onclick default onclick action
 * @param string $accesskey accesskey: character for keyboard shortcut
 * @return XHTML string
 */
function getImageButton($callingform, $callingfield, $name, $tag, $image, $onclick='', $accesskey='') {
	if (strlen($tag) > 0) {
		$onclick = $onclick.', \''.$tag.'\')';
	}
	$str = '<a href="#" onclick="'.$onclick.'" title="'.$name.' ['.$accesskey.']" accesskey="'.$accesskey.'">';
	$str .= '<img src="'.$image.'" alt="'.$name.' ['.$accesskey.']" class="button" />';
	$str .= '</a>';
	return $str;
}

/**
 * returns an array of files contained on the specified folder and subfolders
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2009-10-07
 * @param string $path initial directory path
 * @param boolean $sort if true sort in alphabetical ascending order, if false sort in alphabetical descending order
 * òparam int $baselen string lenght of the base dir path.
 * @return array
 */
function getDirFiles($path, $sort=true, $baselen=0) {
	$handle = opendir($path);
	$files_list = array();
	 while (false !== ($file = readdir($handle))) {
		if (is_file($path.$file) AND (substr($file, 0, 6) != 'latex_')) {
			$files_list[] = substr($path.$file, $baselen);
		} elseif (is_dir($path.$file) AND ($file != '.') AND ($file != '..') AND ($file != 'lang') AND ($file != 'backup')) {
			$files_list = array_merge($files_list, getDirFiles($path.$file.'/', $sort, $baselen));
		}
	}
	closedir($handle);
	// sort alphabetically
	if ($sort) {
		sort($files_list);
	} else {
		rsort($files_list);
	}
	return $files_list;
}

//============================================================+
// END OF FILE
//============================================================+
?>
