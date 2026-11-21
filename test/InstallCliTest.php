<?php

//============================================================+
// File name   : InstallCliTest.php
// Begin       : 2026-11-18
//
// Description : Unit tests for the database type check of the
//               command-line installer (install/install_cli.php).
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test;

use PHPUnit\Framework\TestCase;

/**
 * @file
 * Runs install/install_cli.php with an unsupported database type; the script exits before any
 * database or file operation.
 * @package com.tecnick.tcexam.test
 */
final class InstallCliTest extends TestCase
{
    public function testLegacyMysqlDriverTypeIsRejected(): void
    {
        $previous = getenv('TCEXAM_DB_TYPE');
        putenv('TCEXAM_DB_TYPE=MYSQLDEPRECATED');
        $output = [];
        $code = -1;
        try {
            exec(
                escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../install/install_cli.php') . ' 2>&1',
                $output,
                $code,
            );
        } finally {
            putenv($previous === false ? 'TCEXAM_DB_TYPE' : 'TCEXAM_DB_TYPE=' . $previous);
        }

        $text = implode("\n", $output);
        $this->assertSame(2, $code, $text);
        $this->assertStringContainsString('unsupported TCEXAM_DB_TYPE "MYSQLDEPRECATED"', $text);
    }
}
