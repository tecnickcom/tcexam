<?php
//============================================================+
// File name   : tce_functions_errmsg.php
// Begin       : 2001-09-17
// Last Update : 2013-12-11
//
// Description : handle error messages
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
//    Copyright (C) 2004-2013  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Handle error/warning/system messages.<br>
 * messagetype:
 * <ul>
 * <li>message</li>
 * <li>warning</li>
 * <li>error</li>
 * </ul>
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2001-09-17
 */

/**
 * Handle error/warning/system messages.
 * Print a message
 * @param $messagetype (string) Type of message:  0=no message, message; warning; error.
 * @param $messagetoprint (string)  message to print.
 * @param $exit (bool) if true output a message and terminate the current script [default = false].
 */
function F_print_error($messagetype = 'MESSAGE', $messagetoprint = '', $exit = false)
{
    require_once(dirname(__FILE__).'/../config/tce_config.php');
    global $l;
    $messagetype = strtolower($messagetype);
    //message is appended to the log file
    if (K_USE_ERROR_LOG and (!strcmp($messagetype, 'error'))) {
        $logsttring = date(K_TIMESTAMP_FORMAT).K_TAB;
        $logsttring .= $_SESSION['session_user_id'].K_TAB;
        $logsttring .= $_SESSION['session_user_ip'].K_TAB;
        $logsttring .= $messagetype.K_TAB;
        $logsttring .= $_SERVER['SCRIPT_NAME'].K_TAB;
        $logsttring .= $messagetoprint.K_NEWLINE;
        error_log($logsttring, 3, '../log/tce_errors.log');
    }
    if (strlen($messagetoprint) > 0) {
        switch ($messagetype) {
            case 'message':{
                $msgtitle = $l['t_message'];
                break;
            }
            case 'warning':{
                $msgtitle = $l['t_warning'];
                break;
            }
            case 'error':{
                $msgtitle = $l['t_error'];
                break;
            }
            default: {//no message
                $msgtitle = $messagetype;
                break;
            }
        }
        echo '<div class="'.$messagetype.'">'.$msgtitle.': '.$messagetoprint.'</div>'.K_NEWLINE;
        if (K_ENABLE_JSERRORS) {
            //display message on JavaScript Alert Window.
            echo '<script type="text/javascript">'.K_NEWLINE;
            echo '//<![CDATA['.K_NEWLINE;
            $messagetoprint = unhtmlentities(strip_tags($messagetoprint));
            $messagetoprint = str_replace("'", "\'", $messagetoprint);
            echo 'alert(\'['.$msgtitle.']: '.$messagetoprint.'\');'.K_NEWLINE;
            echo '//]]>'.K_NEWLINE;
            echo '</script>'.K_NEWLINE;
        }
    }
    if ($exit) {
        exit(); // terminate the current script
    }
}

/**
 * Print the database error message.
 * @param $exit (bool) if true output a message and terminate the current script [default = true].
 */
function F_display_db_error($exit = true)
{
    global $db;
    $messagetype = 'ERROR';
    $messagetoprint = F_db_error($db);
    F_print_error($messagetype, $messagetoprint, $exit);
}

/**
 * Custom PHP error handler function.
 * @param $errno (int) The first parameter, errno, contains the level of the error raised, as an integer.
 * @param $errstr (string) The second parameter, errstr, contains the error message, as a string.
 * @param $errfile (string) The third parameter is optional, errfile, which contains the filename that the error was raised in, as a string.
 * @param $errline (int) The fourth parameter is optional, errline, which contains the line number the error was raised at, as an integer.
 */
function F_error_handler($errno, $errstr, $errfile, $errline)
{
    if (ini_get('error_reporting') == 0) {
        // this is required to ignore supressed error messages with '@'
        return;
    }
    $messagetoprint = '['.$errno.'] '.$errstr.' | LINE: '.$errline.' | FILE: '.$errfile.'';
    switch ($errno) {
        case E_ERROR:
        case E_USER_ERROR: {
            F_print_error('ERROR', $messagetoprint, true);
            break;
        }
        case E_WARNING:
        case E_USER_WARNING: {
            F_print_error('ERROR', $messagetoprint, false);
            break;
        }
        case E_NOTICE:
        case E_USER_NOTICE:
        default: {
            F_print_error('WARNING', $messagetoprint, false);
            break;
        }
    }
}

// Set the custom error handler function
$old_error_handler = set_error_handler('F_error_handler', K_ERROR_TYPES);

/**
 * Check if the URL exist.
 * @param url (string) URL to check.
 * @return Returns TRUE if the URL exists; FALSE otherwise.
 */
function F_url_exists($url) {
    $crs = curl_init();
    curl_setopt($crs, CURLOPT_URL, $url);
    curl_setopt($crs, CURLOPT_NOBODY, true);
    curl_setopt($crs, CURLOPT_FAILONERROR, true);
    if ((ini_get('open_basedir') == '') && (!ini_get('safe_mode'))) {
        curl_setopt($crs, CURLOPT_FOLLOWLOCATION, true);
    }
    curl_setopt($crs, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($crs, CURLOPT_TIMEOUT, 30);
    curl_setopt($crs, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($crs, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($crs, CURLOPT_USERAGENT, 'tc-lib-file');
    curl_exec($crs);
    $code = curl_getinfo($crs, CURLINFO_HTTP_CODE);
    curl_close($crs);
    return ($code == 200);
}

/**
 * Wrapper for file_exists.
 * Checks whether a file or directory exists.
 * Only allows some protocols and local files.
 * @param filename (string) Path to the file or directory. 
 * @return Returns TRUE if the file or directory specified by filename exists; FALSE otherwise.  
 */
function F_file_exists($filename) {
    if (preg_match('|^https?://|', $filename) == 1) {
        return F_url_exists($filename);
    }
    if (strpos($filename, '://')) {
        return false; // only support http and https wrappers for security reasons
    }
    return @file_exists($filename);
}

//============================================================+
// END OF FILE
//============================================================+
