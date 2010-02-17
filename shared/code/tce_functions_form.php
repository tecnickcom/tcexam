<?php
//============================================================+
// File name   : tce_functions_form.php
// Begin       : 2001-11-07
// Last Update : 2010-02-17
// 
// Description : Functions to handle XHTML Form Fields.
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
//    Additionally, you can't remove the original TCExam logo, copyrights statements
//    and links to Tecnick.com and TCExam websites.
//    
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * Functions to handle XHTML Form Fields.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-11-07
 */

/**
 */

$formstatus = TRUE; //reset form status

// check buttons actions
if(isset($_POST['update'])) {
	$menu_mode = 'update';
} elseif(isset($_POST['delete'])) {
	$menu_mode = 'delete';
} elseif(isset($_POST['forcedelete'])) {
	$menu_mode = 'forcedelete';
} elseif(isset($_POST['cancel'])) {
	$menu_mode = 'cancel';
} elseif(isset($_POST['add'])) {
	$menu_mode = 'add';
} elseif(isset($_POST['clear'])) {
	$menu_mode = 'clear';
} elseif(isset($_POST['upload'])) {
	$menu_mode = 'upload';
} elseif(isset($_POST['addquestion'])) {
	$menu_mode = 'addquestion';
}
if(!isset($menu_mode)) {
	$menu_mode = '';
}

/**
 * Returns an array containing form fields.
 * @return array containing form fields
 */
function F_decode_form_fields() {
	$formvars = 'HTTP_'.$_SERVER['REQUEST_METHOD'].'_VARS';
	global $$formvars;
	return $$formvars;
}

/**
 * Check Required Form Fields.<br>
 * Returns a string containing a list of missing fields (comma separated).
 * @param string $formfields input array containing form fields
 * @return array containing a list of missing fields (if any)
 */
function F_check_required_fields($formfields) {
	if(empty($formfields) OR !array_key_exists('ff_required', $formfields) OR strlen($formfields['ff_required']) <= 0) {
		return FALSE;
	}
	$missing_fields = '';
	$required_fields = explode(',',$formfields['ff_required']);
	$required_fields_labels = explode(',',$formfields['ff_required_labels']); // form fields labels
	for($i=0; $i<count($required_fields); $i++) { //for each required field
		$fieldname = trim($required_fields[$i]);
		if(!array_key_exists($fieldname, $formfields) OR strlen(trim($formfields[$fieldname])) <= 0) { //if is empty
			if ($required_fields_labels[$i]) { // check if field has label
				$fieldname = $required_fields_labels[$i];
			}
			$missing_fields .= ', '.stripslashes($fieldname);
		}
	}
	if(strlen($missing_fields)>1) {
		$missing_fields = substr($missing_fields, 1); // cuts first comma
	} 
	return ($missing_fields);
}

/**
 * Check fields format using regular expression comparisons.<br>
 * Returns a string containing a list of wrong fields (comma separated).
 * 
 * NOTE:
 * to check a field create a new hidden field with the same name starting with 'x_'
 * 
 * An example powerful regular expression for email check is:
 *  ^([a-zA-Z0-9_\.\-]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$
 * @param string $formfields input array containing form fields
 * @return array containing a list of wrongfields (if any)
 */
function F_check_fields_format($formfields) {
	if(empty($formfields)) {
		return '';
	}
	reset($formfields);
	$wrongfields = '';
	while(list($key,$value) = each($formfields)) {
		if(substr($key,0,2) == 'x_') {
			$fieldname = substr($key,2);
			if(array_key_exists($fieldname, $formfields) AND strlen($formfields[$fieldname]) > 0) { //if is not empty
				if(!preg_match("'".stripslashes($value)."'i", $formfields[$fieldname])) { //check regular expression
					if ($formfields['xl_'.$fieldname]) { //check if field has label
						$fieldname = $formfields['xl_'.$fieldname];
					}
					$wrongfields .= ', '.stripslashes($fieldname);
				}
			}
		}
	}
	if(strlen($wrongfields) > 1) {
		$wrongfields = substr($wrongfields, 2); // cuts first 2 chars
	} 
	return ($wrongfields);
}

/**
 * Check Form Fields.
 * see: F_check_required_fields, F_check_fields_format
 * @return false in case of error, true otherwise
 */
function F_check_form_fields() {
	require_once('../config/tce_config.php');
	global $l;
	
	$formfields = F_decode_form_fields(); //decode form fields
	
	//check missing fields
	if($missing_fields = F_check_required_fields($formfields)) {
		F_print_error('WARNING', $l['m_form_missing_fields'].': '.$missing_fields);
		F_stripslashes_formfields();
		return FALSE;
	}
	
	//check fields format
	if($wrong_fields = F_check_fields_format($formfields)) {
		F_print_error('WARNING', $l['m_form_wrong_fields'].': '.$wrong_fields);
		F_stripslashes_formfields();
		return FALSE;
	}
	return TRUE;
}

/**
 * Strip slashes from posted form fields.
 */
function F_stripslashes_formfields() {
	foreach ($_POST as $key => $value) {
		if (($key{0} != '_') AND (is_string($value))) {
			global $$key;
			$$key = stripslashes($value);
		}
	}
}

/**
 * Returns XHTML code string to display a window close button
 * @param string $onclick additional javascript code to execute before closing the window.
 * @return XHTML code string
 */
function F_close_button($onclick='') {
	require_once('../config/tce_config.php');
	global $l;
	$str = '';
	$str .= '<div class="row">'.K_NEWLINE;
	$str .= '<form action="'.$_SERVER['SCRIPT_NAME'].'" id="closeform">'.K_NEWLINE;
	$str .= '<div>'.K_NEWLINE;
	$str .= '<input type="button" name="wclose" id="wclose" value="'.$l['w_close'].'" title="'.$l['h_close_window'].'" onclick="'.$onclick.'window.close();" />'.K_NEWLINE;
	$str .= '</div>'.K_NEWLINE;
	$str .= '</form>'.K_NEWLINE;
	$str .= '</div>'.K_NEWLINE;
	return $str;
}

/**
 * Returns XHTML code string to display Form Submit Button.
 * @param string $name button name
 * @param string $value label for button
 * @param string $title button title, default=""
 * @return XHTML code string
 */
function F_submit_button($name, $value, $title="") {
	echo '<input type="submit" name="'.$name.'" id="'.$name.'" value="'.$value.'" title="'.$title.'" />';
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
