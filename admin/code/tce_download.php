<?php
//============================================================+
// File name   : tce_download.php
// Begin       : 2010-05-27
// Last Update : 2010-06-16
//
// Description : Send selected file to client for downloading
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
//    Additionally, you can't remove, move or hide the original TCExam logo,
//    copyrights statements and links to Tecnick.com and TCExam websites.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Download TCExam Database Backup.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2010-06-16
 */

/**
 */

require_once('../../shared/config/tce_paths.php');

if (!isset($_REQUEST['t']) OR !isset($_REQUEST['f'])) {
	exit();
}
$file = urldecode($_REQUEST['f']);
// security check
if (preg_match('/[^a-zA-Z0-9\_\-\.]+/i', $file) > 0) {
	exit();
}
switch ($_REQUEST['t']) {
	case 'b': { // backup file
		// security check
		if ((strlen($file) != 35) OR (substr($file, -3) != '.gz')) {
			exit();
		}
		$filepath = K_PATH_BACKUP;
		$mime = 'application/x-gzip';
		break;
	}
	default: {
		exit();
	}
}
$file_to_download = $filepath.$file;
// send XML headers
header('Content-Description: File Transfer');
header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
// force download dialog
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream', false);
header('Content-Type: application/download', false);
header('Content-Type: '.$mime, false);
// use the Content-Disposition header to supply a recommended filename
header('Content-Disposition: attachment; filename='.$file.';');
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.filesize($file_to_download));
readfile($file_to_download);

//============================================================+
// END OF FILE
//============================================================+
