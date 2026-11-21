<?php

//============================================================+
// File name   : tce_functions_errmsg.php
// Begin       : 2001-09-17
// Last Update : 2023-11-30
//
// Description : handle error messages
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
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
 * HTML tags that are allowed in an error message.
 */
define('K_ALLOWED_ERROR_TAGS', '<a><b><br><em><p><ol><ul><li><small><table><tr><th><td>');

/**
 * Handle error/warning/system messages.
 * Print a message
 * @param $messagetype (string) Type of message:  0=no message, message; warning; error.
 * @param $messagetoprint (string)  message to print.
 * @param $exit (bool) if true output a message and terminate the current script [default = false].
 */
function F_print_error($messagetype = 'MESSAGE', $messagetoprint = '', $exit = false)
{
    require_once __DIR__ . '/../config/tce_config.php';
    require_once __DIR__ . '/tce_functions_general.php';
    global $l;
    $messagetype = strtolower($messagetype);
    // Strip any markup here; the message is escaped per output context (HTML/JS) below.
    // NOTE: do not re-decode entities (unhtmlentities) — doing so reconstructs tags from
    // encoded input and reintroduces XSS via attribute-carrying allow-listed tags.
    $messagetoprint = trim(strip_tags($messagetoprint));
    //message is appended to the log file
    if (K_USE_ERROR_LOG && !strcmp($messagetype, 'error')) {
        $logsttring = date(K_TIMESTAMP_FORMAT) . K_TAB;
        $logsttring .= $_SESSION['session_user_id'] . K_TAB;
        $logsttring .= $_SESSION['session_user_ip'] . K_TAB;
        $logsttring .= $messagetype . K_TAB;
        $logsttring .= $_SERVER['SCRIPT_NAME'] . K_TAB;
        $logsttring .= $messagetoprint . K_NEWLINE;
        error_log($logsttring, 3, '../log/tce_errors.log');
    }

    if (strlen($messagetoprint) > 0) {
        // the translated title is used when available ($l is not set while the language file is being loaded)
        $msgtitle = $messagetype;
        $msgkey = match ($messagetype) {
            'message' => 't_message',
            'warning' => 't_warning',
            'error' => 't_error',
            default => '',
        };
        if ($msgkey !== '' && is_array($l) && array_key_exists($msgkey, $l) && is_string($l[$msgkey])) {
            $msgtitle = $l[$msgkey];
        }
        // announce the message to assistive technologies: warnings/errors are
        // assertive (role="alert"), informational messages are polite (role="status")
        $msgrole = match ($messagetype) {
            'warning', 'error' => 'alert',
            default => 'status',
        };
        echo
            '<div class="'
                . $messagetype
                . '" role="'
                . $msgrole
                . '">'
                . $msgtitle
                . ': '
                . htmlspecialchars($messagetoprint, ENT_QUOTES, 'UTF-8')
                . '</div>'
                . K_NEWLINE;
        if (K_ENABLE_JSERRORS) {
            //display message on JavaScript Alert Window.
            echo '<script type="text/javascript">' . K_NEWLINE;
            echo '//<![CDATA[' . K_NEWLINE;
            echo
                'alert('
                    . json_encode(
                        '[' . $msgtitle . ']: ' . $messagetoprint,
                        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE,
                    )
                    . ');'
                    . K_NEWLINE;
            echo '//]]>' . K_NEWLINE;
            echo '</script>' . K_NEWLINE;
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
    if ((error_reporting() & (int) $errno) === 0) {
        // the error is suppressed with '@' or excluded from error_reporting
        return false;
    }

    $messagetoprint = '[' . $errno . '] ' . $errstr . ' | LINE: ' . $errline . ' | FILE: ' . $errfile . '';
    $messagetoprint = strip_tags($messagetoprint, K_ALLOWED_ERROR_TAGS);
    match ($errno) {
        E_ERROR, E_USER_ERROR => F_print_error('ERROR', $messagetoprint, true),
        E_WARNING, E_USER_WARNING => F_print_error('ERROR', $messagetoprint, false),
        default => F_print_error('WARNING', $messagetoprint, false),
    };
}

// Set the custom error handler function
$old_error_handler = set_error_handler('F_error_handler', K_ERROR_TYPES);

/**
 * Wrapper for file_exists.
 * Checks whether a local file or directory exists; paths containing '://' (stream wrappers and URLs) return false.
 * @param filename (string) Path to the file or directory.
 * @return Returns TRUE if the file or directory specified by filename exists; FALSE otherwise.
 */
function F_file_exists($filename)
{
    $filename = (string) $filename;
    if (str_contains($filename, '://')) {
        return false;
    }

    return @file_exists($filename);
}
