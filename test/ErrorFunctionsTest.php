<?php

//============================================================+
// File name   : ErrorFunctionsTest.php
// Begin       : 2026-11-18
//
// Description : Unit tests for the error handler and the file-existence
//               helper (shared/code/tce_functions_errmsg.php).
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test;

use PHPUnit\Framework\TestCase;

/**
 * @file
 * Tests for F_error_handler() and F_file_exists().
 * @package com.tecnick.tcexam.test
 */
final class ErrorFunctionsTest extends TestCase
{
    public function testHandlerSkipsErrorsExcludedFromErrorReporting(): void
    {
        $old = error_reporting(E_ALL & ~E_WARNING);
        try {
            $result = \F_error_handler(E_WARNING, 'excluded', __FILE__, __LINE__);
        } finally {
            error_reporting($old);
        }

        $this->assertFalse($result);
    }

    public function testHandlerSkipsErrorsSuppressedWithAt(): void
    {
        $seen = null;
        set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline) use (
            &$seen,
        ): bool {
            $seen = \F_error_handler($errno, $errstr, $errfile, $errline);

            return true;
        });
        try {
            // @mago-expect lint:no-error-control-operator -- the test checks the handling of an @-suppressed warning
            @file_get_contents(sys_get_temp_dir() . '/' . uniqid('tcexam-missing-file-', true));
        } finally {
            restore_error_handler();
        }

        $this->assertFalse($seen, 'an @-suppressed warning must not be printed');
    }

    public function testFileExistsForLocalPaths(): void
    {
        $this->assertTrue(\F_file_exists(__FILE__));
        $this->assertTrue(\F_file_exists(__DIR__));
        $this->assertFalse(\F_file_exists(__DIR__ . '/' . uniqid('no-such-file-', true)));
    }

    public function testFileExistsRejectsUrlsAndStreamWrappers(): void
    {
        $this->assertFalse(\F_file_exists('http://127.0.0.1/'));
        $this->assertFalse(\F_file_exists('https://example.invalid/'));
        $this->assertFalse(\F_file_exists('file://' . __FILE__));
        $this->assertFalse(\F_file_exists('php://filter/resource=' . __FILE__));
        $this->assertFalse(\F_file_exists('://' . __FILE__));
    }
}
