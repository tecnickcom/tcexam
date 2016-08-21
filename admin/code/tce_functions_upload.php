<?php
//============================================================+
// File name   : tce_functions_upload.php
// Begin       : 2001-11-19
// Last Update : 2010-09-21
//
// Description : Upload functions.
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
//    Copyright (C) 2004-2010 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Functions to upload files.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2001-11-19
 */

/**
 * Check if the uploaded file extension is allowed.
 * @author Nicola Asuni
 * @since 2001-11-19
 * @param $filename (string) the filename
 * @return true in case of allowed file type, false otherwise
 */
function F_is_allowed_upload($filename)
{
    if (!defined('K_ALLOWED_UPLOAD_EXTENSIONS')) {
        return false;
    }
    $allowed_extensions = unserialize(K_ALLOWED_UPLOAD_EXTENSIONS);
    $path_parts = pathinfo($filename);
    if (in_array(strtolower($path_parts['extension']), $allowed_extensions)) {
        return true;
    }
    return false;
}

/**
 * Uploads image file to the server.
 * @author Nicola Asuni
 * @since 2010-06-12
 * @param $fieldname (string) form field name containing the source file path
 * @param $uploaddir (string) upload directory
 * @return mixed file name or false in case of error
 */
function F_upload_file($fieldname, $uploaddir)
{
    global $l;
    require_once('../config/tce_config.php');
    // sanitize file name
    $filename = preg_replace('/[\s]/', '_', $_FILES[$fieldname]['name']);
    $filename = preg_replace('/[^a-zA-Z0-9_\.\-]/', '', $filename);
    $filepath = $uploaddir.$filename;
    if (F_is_allowed_upload($filename) and move_uploaded_file($_FILES[$fieldname]['tmp_name'], $filepath)) {
        F_print_error('MESSAGE', htmlspecialchars($filename).': '.$l['m_upload_yes']);
        return $filename;
    }
    F_print_error('ERROR', htmlspecialchars($filename).': '.$l['m_upload_not'].'');
    return false;
}

/**
 * returns the file size in bytes
 * @author Nicola Asuni
 * @since 2001-11-19
 * @param $filetocheck (string) file to check (local path or URL)
 * @return mixed file size in bytes or false in case of error
 */
function F_read_file_size($filetocheck)
{
    global $l;
    require_once('../config/tce_config.php');
    $filesize = 0;
    if ($fp = fopen($filetocheck, 'rb')) {
        $s_array = fstat($fp);
        if ($s_array['size']) {
            $filesize = $s_array['size'];
        } else {//read size from remote file (very slow function)
            while (!feof($fp)) {
                $content = fread($fp, 1);
                $filesize++;
            }
        }
        fclose($fp);
        return($filesize);
    }
    F_print_error('ERROR', basename($filetocheck).': '.$l['m_openfile_not']);
    return false;
}

//============================================================+
// END OF FILE
//============================================================+
