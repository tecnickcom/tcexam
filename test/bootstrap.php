<?php

//============================================================+
// File name   : bootstrap.php
// Begin       : 2026-06-22
//
// Description : PHPUnit bootstrap for TCExam.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * PHPUnit bootstrap. The application is procedural and not PSR-4 autoloaded, so the source
 * files exercised by the suite are required explicitly here. Test classes live under the
 * Test\ namespace (see composer "autoload-dev").
 * @package com.tecnick.tcexam.test
 */

require_once __DIR__ . '/../vendor/autoload.php';

// A configured per-install secret (any value other than the shipped placeholder/default) so that
// code reading K_RANDOM_SECURITY behaves as on a properly installed instance.
if (! defined('K_RANDOM_SECURITY')) {
    define('K_RANDOM_SECURITY', 'test-' . str_repeat('a1b2', 8));
}

// Cookie/session ini constants consumed at include-time by tce_functions_session.php.
if (! defined('K_COOKIE_HTTPONLY')) {
    define('K_COOKIE_HTTPONLY', true);
}

if (! defined('K_COOKIE_SECURE')) {
    define('K_COOKIE_SECURE', true);
}

if (! defined('K_COOKIE_SAMESITE')) {
    define('K_COOKIE_SAMESITE', 'Strict');
}

// Minimal shim for the file-existence helper used by TMXResourceBundle. The real implementation
// lives in shared/code/tce_functions_errmsg.php, which registers a global error handler on
// include; the TMX parser only needs a plain local-file check here.
if (! function_exists('F_file_exists')) {
    function F_file_exists($filename)
    {
        return @file_exists((string) $filename);
    }
}

// Application source under test. Each is a pure definition file, except tce_functions_session.php
// whose web bootstrap is guarded to be skipped under the CLI SAPI (see that file).
require_once __DIR__ . '/../shared/code/tce_functions_general.php';
require_once __DIR__ . '/../shared/code/tce_functions_session.php';
require_once __DIR__ . '/../shared/code/tce_tmx.php';
// Form helpers: the top-level CSRF guard self-skips under the CLI SAPI (no POST action), and the
// only include-time side effect is defining K_EMAIL_RE_PATTERN. F_check_fields_format() is pure.
require_once __DIR__ . '/../shared/code/tce_functions_form.php';
