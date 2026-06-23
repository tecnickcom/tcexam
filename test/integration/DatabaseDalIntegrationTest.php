<?php

//============================================================+
// File name   : DatabaseDalIntegrationTest.php
// Begin       : 2026-06-22
//
// Description : Integration tests for the Database Abstraction Layer (DAL)
//               run against a real database server.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test\Integration;

use PHPUnit\Framework\TestCase;

/**
 * @file
 * Integration tests for the Database Abstraction Layer (shared/code/tce_db_dal_*.php) against a
 * live, freshly-seeded database. They are driver-agnostic: the DAL implementation and connection
 * settings are selected from the TCEXAM_DB_* environment variables, so the same suite runs against
 * both MySQL/MariaDB and PostgreSQL. The Dockerised environment (`make dockertest`) sets those
 * variables; outside it (e.g. the host `make test`) every test self-skips.
 * @package com.tecnick.tcexam.test
 */
final class DatabaseDalIntegrationTest extends TestCase
{
    /** Prefix for rows created by this suite, so they can be isolated and cleaned up. */
    private const ROW_PREFIX = 'itest_';

    /** Live database link returned by F_db_connect(). */
    private mixed $db = null;

    /** Guards the one-time DAL include (the DAL declares global F_db_* functions). */
    private static bool $dalLoaded = false;

    protected function setUp(): void
    {
        $type = (string) getenv('TCEXAM_DB_TYPE');
        if ($type === '') {
            $this->markTestSkipped(
                'Integration database not configured: set TCEXAM_DB_* (run via `make dockertest`).'
            );
        }

        if ($type !== 'MYSQL' && $type !== 'POSTGRESQL') {
            $this->markTestSkipped('Unsupported TCEXAM_DB_TYPE: ' . $type);
        }

        self::loadDal($type);
        self::defineTableConstants();

        $this->db = \F_db_connect(
            (string) getenv('TCEXAM_DB_HOST'),
            (string) getenv('TCEXAM_DB_PORT'),
            (string) getenv('TCEXAM_DB_USER'),
            (string) getenv('TCEXAM_DB_PASSWORD'),
            (string) getenv('TCEXAM_DB_NAME')
        );

        $this->assertNotFalse($this->db, 'F_db_connect() should open a live connection');
    }

    protected function tearDown(): void
    {
        if (empty($this->db)) {
            return;
        }

        // Drop anything this suite inserted, then release the connection (best-effort).
        try {
            \F_db_query(
                'DELETE FROM ' . \K_TABLE_GROUPS . " WHERE group_name LIKE '" . self::ROW_PREFIX . "%'",
                $this->db
            );
            \F_db_close($this->db);
        } catch (\Throwable) {
            // cleanup failures are not test failures
        }

        $this->db = null;
    }

    /** Load the DAL implementation matching the configured database type. */
    private static function loadDal(string $type): void
    {
        if (self::$dalLoaded) {
            return;
        }

        $dal = match ($type) {
            'MYSQL' => __DIR__ . '/../../shared/code/tce_db_dal_mysqli.php',
            'POSTGRESQL' => __DIR__ . '/../../shared/code/tce_db_dal_postgresql.php',
            default => null,
        };

        if ($dal === null) {
            self::fail('Unsupported TCEXAM_DB_TYPE: ' . $type);
        }

        require_once $dal;
        self::$dalLoaded = true;
    }

    /** Define the subset of table-name constants these tests reference. */
    private static function defineTableConstants(): void
    {
        if (! \defined('K_TABLE_PREFIX')) {
            \define('K_TABLE_PREFIX', 'tce_');
        }

        if (! \defined('K_TABLE_USERS')) {
            \define('K_TABLE_USERS', \K_TABLE_PREFIX . 'users');
        }

        if (! \defined('K_TABLE_GROUPS')) {
            \define('K_TABLE_GROUPS', \K_TABLE_PREFIX . 'user_groups');
        }
    }

    public function testSeededUsersArePresent(): void
    {
        $res = \F_db_query(
            'SELECT user_name, user_level FROM ' . \K_TABLE_USERS . ' ORDER BY user_level',
            $this->db
        );
        $this->assertNotFalse($res, 'querying the seeded schema/data should succeed');

        $levels = [];
        $row = \F_db_fetch_assoc($res);
        while (is_array($row)) {
            $levels[$row['user_name']] = (int) $row['user_level'];
            $row = \F_db_fetch_assoc($res);
        }

        $this->assertGreaterThanOrEqual(2, \F_db_num_rows($res));
        $this->assertArrayHasKey('anonymous', $levels);
        $this->assertArrayHasKey('admin', $levels);
        $this->assertSame(0, $levels['anonymous']);
        $this->assertSame(10, $levels['admin']);
    }

    public function testEscapeSqlRoundTripsThroughTheServer(): void
    {
        // A value with the characters SQL injection relies on; after escaping it must survive a
        // round-trip through the server unchanged (proving the escaping matches the dialect).
        $raw = "O'Brien \"quote\" \\ end";
        $escaped = \F_escape_sql($this->db, $raw, false);

        $res = \F_db_query("SELECT '" . $escaped . "' AS v", $this->db);
        $this->assertNotFalse($res, 'a query embedding the escaped value should be valid SQL');

        $row = \F_db_fetch_assoc($res);
        $this->assertSame($raw, $row['v'], 'the escaped value must round-trip unchanged');
    }

    public function testInsertFetchAndDeleteRoundTrip(): void
    {
        $name = self::ROW_PREFIX . 'group_1';

        $ins = \F_db_query(
            'INSERT INTO ' . \K_TABLE_GROUPS . " (group_name) VALUES ('" . $name . "')",
            $this->db
        );
        $this->assertNotFalse($ins, 'INSERT should succeed');
        $this->assertSame(1, \F_db_affected_rows($this->db, $ins));

        $id = (int) \F_db_insert_id($this->db, \K_TABLE_GROUPS, 'group_id');
        $this->assertGreaterThan(0, $id, 'a generated id should be returned for the new row');

        $sel = \F_db_query(
            'SELECT group_id, group_name FROM ' . \K_TABLE_GROUPS . " WHERE group_name = '" . $name . "'",
            $this->db
        );
        $this->assertSame(1, \F_db_num_rows($sel));

        $found = \F_db_fetch_assoc($sel);
        $this->assertSame($name, $found['group_name']);
        $this->assertSame($id, (int) $found['group_id']);

        $del = \F_db_query(
            'DELETE FROM ' . \K_TABLE_GROUPS . " WHERE group_name = '" . $name . "'",
            $this->db
        );
        $this->assertNotFalse($del);
        $this->assertSame(1, \F_db_affected_rows($this->db, $del));
    }

    public function testDatetimeDiffSecondsExpressionEvaluates(): void
    {
        // The DAL emits a dialect-specific seconds-difference expression; verify it is valid SQL
        // and computes the expected value (60s) on the live server.
        if ((string) getenv('TCEXAM_DB_TYPE') === 'POSTGRESQL') {
            $expr = \F_db_datetime_diff_seconds(
                "TIMESTAMP '2024-01-01 00:00:00'",
                "TIMESTAMP '2024-01-01 00:01:00'"
            );
        } else {
            $expr = \F_db_datetime_diff_seconds("'2024-01-01 00:00:00'", "'2024-01-01 00:01:00'");
        }

        $res = \F_db_query('SELECT ' . $expr . ' AS d', $this->db);
        $this->assertNotFalse($res);

        $row = \F_db_fetch_assoc($res);
        $this->assertSame(60, (int) $row['d']);
    }
}
