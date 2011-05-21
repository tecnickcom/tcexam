<?php
//============================================================+
// File name   : tce_import_xml_questions.php
// Begin       : 2006-03-12
// Last Update : 2011-05-20
//
// Description : Import questions from an XML file.
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
//    Copyright (C) 2004-2010  Nicola Asuni - Tecnick.com S.r.l.
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
 * Import questions from an XML file to a selected subject.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2006-03-12
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_IMPORT;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_question_importer'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('../../shared/code/tce_functions_tcecode.php');
require_once('../code/tce_functions_auth_sql.php');

if(!isset($type) OR (empty($type))) {
	$type = 1;
} else {
	$type = intval($type);
}

if (isset($menu_mode) AND ($menu_mode == 'upload')) {
	if($_FILES['userfile']['name']) {
		require_once('../code/tce_functions_upload.php');
		// upload file
		$uploadedfile = F_upload_file('userfile', K_PATH_CACHE);
		if ($uploadedfile !== false) {
			$xmlimporter = false;
			switch($type) {
				case 1: {
					// standard TCExam XML format
					require_once('../code/tce_class_import_xml.php');
					$xmlimporter = new XMLQuestionImporter(K_PATH_CACHE.$uploadedfile);
					break;
				}
				case 2: {
					// Custom TCExam XML format
					require_once('../code/tce_import_custom.php');
					$xmlimporter = new CustomQuestionImporter(K_PATH_CACHE.$uploadedfile);
					break;
				}
			}
			if ($xmlimporter) {
				F_print_error('MESSAGE', $l['m_importing_complete']);
			}
		}
	}
}
echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_importquestions">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="userfile">'.$l['w_upload_file'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<input type="hidden" name="MAX_FILE_SIZE" value="'.K_MAX_UPLOAD_SIZE.'" />'.K_NEWLINE;
echo '<input type="file" name="userfile" id="userfile" size="20" title="'.$l['h_upload_file'].'" />'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;
echo '</div>'.K_NEWLINE;


$custom_import = K_ENABLE_CUSTOM_IMPORT;
if (!empty($custom_import)) {
	echo '<div class="row">'.K_NEWLINE;
	echo '<span class="label">&nbsp;</span>'.K_NEWLINE;
	echo '<div class="formw">'.K_NEWLINE;
	echo '<fieldset class="noborder">'.K_NEWLINE;
	echo '<legend title="'.$l['w_type'].'">'.$l['w_type'].'</legend>'.K_NEWLINE;
	echo '<input type="radio" name="type" id="type_tcexam" value="1" title="TCExam XML Format"';
	if($type==1) {echo ' checked="checked"';}
	echo ' />';
	echo '<label for="type_tcexam">TCExam XML</label><br />'.K_NEWLINE;
	echo '<input type="radio" name="type" id="type_custom" value="2" title="'.$custom_import.'"'.K_NEWLINE;
	if($type==2) {echo ' checked="checked"';}
	echo ' />';
	echo '<label for="type_custom">'.$custom_import.'</label>'.K_NEWLINE;
	echo '</fieldset>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
}

echo '<div class="row">'.K_NEWLINE;
echo '<br />'.K_NEWLINE;

// show upload button
F_submit_button('upload', $l['w_upload'], $l['h_submit_file']);

echo '</div>'.K_NEWLINE;
echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_import_xml_questions'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
