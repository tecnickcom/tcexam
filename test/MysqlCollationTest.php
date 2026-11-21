<?php

//============================================================+
// File name   : MysqlCollationTest.php
// Begin       : 2026-11-18
//
// Description : Unit tests for the MySQL binary collation helper
//               (shared/code/tce_db_dal_mysqli.php).
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * @file
 * Tests for F_db_mysql_bin_collation(). Each case runs in its own process because the result
 * depends on the K_MYSQL_QA_BIN_COLLATION constant.
 * @package com.tecnick.tcexam.test
 */
final class MysqlCollationTest extends TestCase
{
    private static function load(): void
    {
        require_once __DIR__ . '/../shared/code/tce_db_dal_mysqli.php';
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDefaultIsUtf8mb4Bin(): void
    {
        self::load();
        $this->assertSame('utf8mb4_bin', \F_db_mysql_bin_collation());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testLegacyUtf8BinIsMappedToUtf8mb4Bin(): void
    {
        \define('K_MYSQL_QA_BIN_COLLATION', 'utf8_bin');
        self::load();
        $this->assertSame('utf8mb4_bin', \F_db_mysql_bin_collation());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testConfiguredUtf8mb4CollationIsKept(): void
    {
        \define('K_MYSQL_QA_BIN_COLLATION', 'utf8mb4_0900_bin');
        self::load();
        $this->assertSame('utf8mb4_0900_bin', \F_db_mysql_bin_collation());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testUnsafeValueIsReplaced(): void
    {
        \define('K_MYSQL_QA_BIN_COLLATION', 'utf8mb4_bin; DROP TABLE tce_users');
        self::load();
        $this->assertSame('utf8mb4_bin', \F_db_mysql_bin_collation());
    }
}
