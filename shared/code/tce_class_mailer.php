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
     * Copy the TCExam email configuration ($emailcfg) onto this mailer instance.
     *
     * The configuration comes from a plain PHP config file, so every value is untyped as far as
     * static analysis is concerned: cast each one to the type PHPMailer declares for the matching
     * property. The charset falls back to the configured default when the language has none.
     *
     * @param array<array-key, mixed> $emailcfg TCExam email configuration array.
     * @param string $charset Language charset, overrides the configured one when not empty.
     * @public
     */
    public function setConfigData(array $emailcfg, string $charset = ''): void
    {
        $this->Priority = (int) ($emailcfg['Priority'] ?? 3);
        $this->ContentType = (string) ($emailcfg['ContentType'] ?? 'text/plain');
        $this->Encoding = (string) ($emailcfg['Encoding'] ?? '8bit');
        $this->WordWrap = (int) ($emailcfg['WordWrap'] ?? 0);
        $this->Mailer = (string) ($emailcfg['Mailer'] ?? 'mail');
        $this->Sendmail = (string) ($emailcfg['Sendmail'] ?? '/usr/sbin/sendmail');
        $this->Host = (string) ($emailcfg['Host'] ?? 'localhost');
        $this->Port = (int) ($emailcfg['Port'] ?? 25);
        $this->Helo = (string) ($emailcfg['Helo'] ?? '');
        $this->SMTPAuth = filter_var($emailcfg['SMTPAuth'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->SMTPSecure = (string) ($emailcfg['SMTPSecure'] ?? '');
        $this->Username = (string) ($emailcfg['Username'] ?? '');
        $this->Password = (string) ($emailcfg['Password'] ?? '');
        $this->Timeout = (int) ($emailcfg['Timeout'] ?? 300);
        $this->SMTPDebug = (int) ($emailcfg['SMTPDebug'] ?? 0);
        $this->Sender = (string) ($emailcfg['Sender'] ?? '');
        $this->From = (string) ($emailcfg['From'] ?? '');
        $this->FromName = (string) ($emailcfg['FromName'] ?? '');
        $reply = (string) ($emailcfg['Reply'] ?? '');
        if ($reply !== '') {
            try {
                $this->addReplyTo($reply, (string) ($emailcfg['ReplyName'] ?? ''));
            } catch (PHPMailer\PHPMailer\Exception $e) {
                $this->setError($e->getMessage());
            }
        }

        $this->CharSet = $charset === '' ? (string) ($emailcfg['CharSet'] ?? 'UTF-8') : $charset;
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
