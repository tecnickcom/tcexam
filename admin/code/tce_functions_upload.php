<?php
//============================================================+
// File name   : tce_functions_upload.php
// Begin       : 2001-11-19
// Last Update : 2010-06-12
//
// Description : Upload functions.
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
 * Functions to upload files.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-11-19
 */

/**
 * Check if the uploaded file extension is allowed.
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-11-19
 * @param string $filename the filename
 * @return true in case of allowed file type, false otherwise
 */
function F_is_allowed_upload($filename) {
	if (!defined('K_ALLOWED_UPLOAD_EXTENSIONS')) {
		return false;
	}
	$allowed_extensions = unserialize(K_ALLOWED_UPLOAD_EXTENSIONS);
	$path_parts = pathinfo($filename);
	if (in_array($path_parts['extension'], $allowed_extensions)) {
		return true;
	}
	return false;
}

/**
 * Uploads image file to the server.
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2010-06-12
 * @param string $fieldname form field name containing the source file path
 * @param string $uploaddir upload directory
 * @return mixed file name or false in case of error
 */
function F_upload_file($fieldname, $uploaddir) {
	global $l;
	require_once('../config/tce_config.php');
	$filename = $uploaddir.$_FILES[$fieldname]['name'];
	if (F_is_allowed_upload($filename) AND move_uploaded_file($_FILES[$fieldname]['tmp_name'], $filename)) {
		F_print_error('MESSAGE', htmlspecialchars($_FILES[$fieldname]['name']).': '.$l['m_upload_yes']);
		return $_FILES[$fieldname]['name'];
	}
	F_print_error('ERROR', htmlspecialchars($_FILES[$fieldname]['name']).': '.$l['m_upload_not'].'');
	return FALSE;
}

/**
 * returns the file size in bytes
 * @author Nicola Asuni
 * @copyright Copyright © 2004-2010, Nicola Asuni - Tecnick.com S.r.l. - ITALY - www.tecnick.com - info@tecnick.com
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @link www.tecnick.com
 * @since 2001-11-19
 * @param string $filetocheck file to check (local path or URL)
 * @return mixed file size in bytes or false in case of error
 */
function F_read_file_size($filetocheck) {
	global $l;
	require_once('../config/tce_config.php');
	$filesize = 0;
	if($fp = fopen($filetocheck, 'rb')) {
		$s_array = fstat($fp);
		if($s_array['size']) {
			$filesize = $s_array['size'];
		} else {//read size from remote file (very slow function)
			while(!feof($fp)) {
				$content = fread($fp, 1);
				$filesize++;
			}
		}
		fclose($fp);
		return($filesize);
	}
	F_print_error('ERROR', basename($filetocheck).': '.$l['m_openfile_not']);
	return FALSE;
}

//============================================================+
// END OF FILE
//============================================================+
?>
