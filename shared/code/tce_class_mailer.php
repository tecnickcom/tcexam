<?php

//============================================================+
// File name   : cp_class_mailer.php
// Begin       : 2001-10-20
// Last Update : 2023-11-30
//
// Description : Extend PHPMailer class with inheritance
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * PHPMailer class extension.
 * @package PHPMailer
 * @brief PHP email transport class
 * @author Nicola Asuni
 * @since 2005-02-24
 */

require_once '../config/tce_config.php';

require_once '../../shared/config/tce_email_config.php'; // Include default public variables

// Set the custom error handler function
// This suppress the warnings
//$old_error_handler = set_error_handler('F_error_handler', E_ERROR | E_WARNING | E_PARSE);

// load Composer-managed dependencies (provides PHPMailer\PHPMailer\PHPMailer)
require_once '../../vendor/autoload.php';

/**
 * @class C_mailer
 * PHPMailer class extension.
 * @author Nicola Asuni
 * @package PHPMailer
 * @since 2005-02-24
 */
class C_mailer extends PHPMailer\PHPMailer\PHPMailer
{
    /**
     * Replace the default setError to show a localized TCExam error page and stop.
     * @param $msg (string) error message
     * @public
     */
    public function setError($msg)
    {
        parent::setError($msg);
        F_print_error('ERROR', $this->ErrorInfo);
        exit();
    }

    /**
     * Load the localized mailer-error strings.
     *
     * PHPMailer 7 resolves its error messages through the static self::lang()/self::$language
     * mechanism (lang() is no longer an overridable instance method, and is always called as
     * self::lang() internally). So instead of overriding lang(), the TCExam translations are
     * merged onto PHPMailer's own language keys: a TMX entry "m_mailerror_<key>" overrides
     * PHPMailer's "<key>" message. English defaults are loaded first so any key TCExam does not
     * translate still resolves.
     *
     * @param $lang (array) TCExam language array (the global $l).
     * @public
     */
    public function setLanguageData($lang)
    {
        parent::setLanguage();
        foreach ($lang as $key => $val) {
            if (is_string($val) && str_starts_with($key, 'm_mailerror_')) {
                self::$language[substr($key, 12)] = $val; // 12 == strlen('m_mailerror_')
            }
        }
    }
} //end of class
