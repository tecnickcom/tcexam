<?php
//============================================================+
// File name   : cp_class_mailer.php
// Begin       : 2001-10-20
// Last Update : 2020-01-03
//
// Description : Extend PHPMailer class with inheritance
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
//    Copyright (C) 2004-2020  Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * PHPMailer class extension.
 * @package PHPMailer
 * @brief PHP email transport class
 * @author Nicola Asuni
 * @since 2005-02-24
 */

/**
 */

require_once('../config/tce_config.php');

require_once('../../shared/config/tce_email_config.php'); // Include default public variables

// Set the custom error handler function
// This suppress the warnings
//$old_error_handler = set_error_handler('F_error_handler', E_ERROR | E_WARNING | E_PARSE);

// include the phpmailer class
require_once('../../shared/phpmailer/src/autoload.php');
require_once('../../shared/phpmailer/src/PHPMailer.php');

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
     * Replace the default SetError
     * @param $msg (string) error message
     * @public
     * @return void
     */
    public function setError($msg)
    {
        parent::setError($msg);
        F_print_error('ERROR', $this->ErrorInfo);
        exit;
    }

    /**
     * Set the language array
     * @param $lang (array) Language array
     * @public
     * @return void
     */
    public function setLanguageData($lang)
    {
        $this->language = $lang;
    }

    /**
     * Returns a message in the appropriate language.
     * (override original lang() method).
     * @param $key (string) language key
     * @protected
     * @return string
     */
    protected function lang($key)
    {
        if (isset($this->language['m_mailerror_'.$key])) {
            return $this->language['m_mailerror_'.$key];
        } else {
            return 'UNKNOW ERROR: ['.$key.']';
        }
    }

} //end of class

//============================================================+
// END OF FILE
//============================================================+
