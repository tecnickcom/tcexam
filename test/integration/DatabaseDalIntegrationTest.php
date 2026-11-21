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
            $levels[(string) ($row['user_name'] ?? '')] = (int) ($row['user_level'] ?? 0);
            $row = \F_db_fetch_assoc($res);
        }

        $this->assertGreaterThanOrEqual(2, \F_db_num_rows($res));
        $this->assertArrayHasKey('anonymous', $levels);
        $this->assertArrayHasKey('admin', $levels);
        $this->assertSame(0, $levels['anonymous'] ?? null);
        $this->assertSame(10, $levels['admin'] ?? null);
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

    public function testFourByteCharactersRoundTrip(): void
    {
        // emoji (U+1F600) and mathematical italic small alpha (U+1D6FC) need 4 bytes in UTF-8
        $name = self::ROW_PREFIX . "utf8_a\u{1F600}b\u{1D6FC}";
        $ins = \F_db_query(
            'INSERT INTO ' . \K_TABLE_GROUPS . " (group_name) VALUES ('" . \F_escape_sql($this->db, $name, false) . "')",
            $this->db
        );
        $this->assertNotFalse($ins, 'a 4-byte character must be accepted');

        $res = \F_db_query(
            'SELECT group_name FROM ' . \K_TABLE_GROUPS . " WHERE group_name LIKE '" . self::ROW_PREFIX . "utf8_%'",
            $this->db
        );
        $row = \F_db_fetch_assoc($res);
        $this->assertSame(bin2hex($name), bin2hex((string) ($row['group_name'] ?? '')), 'the value must round-trip byte for byte');
    }

    public function testMysqlSessionCharsetIsUtf8mb4(): void
    {
        $this->requireMysql();
        $res = \F_db_query(
            'SELECT @@character_set_client AS c, @@character_set_connection AS n, @@character_set_results AS r',
            $this->db
        );
        $row = \F_db_fetch_assoc($res);
        $this->assertSame(['c' => 'utf8mb4', 'n' => 'utf8mb4', 'r' => 'utf8mb4'], $row);
    }

    public function testMysqlTablesUseUtf8mb4(): void
    {
        $this->requireMysql();
        $res = \F_db_query(
            'SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME FROM information_schema.COLUMNS'
            . " WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'tce\\_%'"
            . " AND CHARACTER_SET_NAME IS NOT NULL AND CHARACTER_SET_NAME <> 'utf8mb4'",
            $this->db
        );
        $this->assertNotFalse($res);
        $this->assertSame(0, \F_db_num_rows($res), 'every text column of the tce_ tables must be utf8mb4');
    }

    public function testMysqlBinaryCollationWorksOnUtf8mb3Columns(): void
    {
        $this->requireMysql();
        $this->assertNotFalse(\F_db_query(
            'CREATE TEMPORARY TABLE itest_mb3 (d TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci) ENGINE=InnoDB',
            $this->db
        ));
        $this->assertNotFalse(\F_db_query("INSERT INTO itest_mb3 (d) VALUES ('Abc')", $this->db));

        $collation = \F_db_mysql_bin_collation();
        $same = \F_db_query("SELECT COUNT(*) AS n FROM itest_mb3 WHERE d='Abc' COLLATE " . $collation, $this->db);
        $this->assertNotFalse($same, 'the binary collation must be valid on a utf8mb4 connection');
        $this->assertSame(1, (int) \F_db_fetch_assoc($same)['n']);

        $case = \F_db_query("SELECT COUNT(*) AS n FROM itest_mb3 WHERE d='abc' COLLATE " . $collation, $this->db);
        $this->assertSame(0, (int) \F_db_fetch_assoc($case)['n'], 'the comparison must stay case-sensitive');

        // a 4-byte character does not fit a utf8mb3 column: the strict SQL mode rejects it
        $bad = \F_db_query("INSERT INTO itest_mb3 (d) VALUES ('x\u{1F600}y')", $this->db);
        $this->assertFalse($bad, 'no truncated row may be stored');

        \F_db_query('DROP TEMPORARY TABLE itest_mb3', $this->db);
    }

    public function testMysqlUpgradeScriptConvertsUtf8mb3Schema(): void
    {
        $this->requireMysql();
        $prefix = 'itup_';
        $old = __DIR__ . '/../fixtures/mysql_db_structure_utf8mb3.sql';
        $upgrade = __DIR__ . '/../../install/upgrade/mysql/mysql_db_upgrade_utf8mb4.sql';
        $tables = $this->prefixedTables($old, $prefix);
        $this->assertNotSame([], $tables);

        try {
            $this->runSqlFile($old, $prefix);
            $this->assertGreaterThan(0, $this->countNonUtf8mb4Columns($prefix), 'the fixture must start as utf8mb3');

            $this->runSqlFile($upgrade, $prefix);
            $this->assertSame(0, $this->countNonUtf8mb4Columns($prefix), 'the upgrade must convert every text column');
            $this->assertSame(
                $this->columnDefinitions('tce_'),
                $this->columnDefinitions($prefix),
                'an upgraded schema must match a fresh installation',
            );
        } finally {
            \F_db_query('SET FOREIGN_KEY_CHECKS = 0', $this->db);
            foreach ($tables as $table) {
                \F_db_query('DROP TABLE IF EXISTS ' . $table, $this->db);
            }
            \F_db_query('SET FOREIGN_KEY_CHECKS = 1', $this->db);
        }
    }

    private function requireMysql(): void
    {
        if ((string) getenv('TCEXAM_DB_TYPE') !== 'MYSQL') {
            $this->markTestSkipped('MySQL only.');
        }
    }

    /**
     * Return the table names created by an SQL file, with the tce_ prefix replaced.
     *
     * @return list<string>
     */
    private function prefixedTables(string $file, string $prefix): array
    {
        $tables = [];
        foreach (explode("\n", (string) file_get_contents($file)) as $line) {
            if (!str_starts_with($line, 'CREATE TABLE tce_')) {
                continue;
            }

            $tables[] = $prefix . trim(substr($line, \strlen('CREATE TABLE tce_')), " (\t\r");
        }

        return $tables;
    }

    /** Run the statements of an SQL file, replacing the tce_ table prefix. */
    private function runSqlFile(string $file, string $prefix): void
    {
        $sql = str_replace('tce_', $prefix, (string) file_get_contents($file));
        $sql = (string) preg_replace('~/\*.*?\*/~s', ' ', $sql);
        $statements = preg_split('/;\s*\n/', $sql);
        foreach ($statements === false ? [] : $statements as $statement) {
            $statement = trim($statement, " \t\r\n;");
            if ($statement === '') {
                continue;
            }

            $this->assertNotFalse(\F_db_query($statement, $this->db), 'SQL failed: ' . $statement . ' ' . \F_db_error($this->db));
        }
    }

    /**
     * Return the type, nullability and collation of every column of the tables with the given prefix.
     *
     * @return array<string,string> map of "table.column" (without prefix) to "type|nullable|collation"
     */
    private function columnDefinitions(string $prefix): array
    {
        $res = \F_db_query(
            'SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLLATION_NAME FROM information_schema.COLUMNS'
            . " WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '" . str_replace('_', '\\_', $prefix) . "%'"
            . ' ORDER BY TABLE_NAME, COLUMN_NAME',
            $this->db
        );
        $this->assertNotFalse($res);
        $columns = [];
        $row = \F_db_fetch_assoc($res);
        while (\is_array($row)) {
            $table = substr((string) ($row['TABLE_NAME'] ?? ''), \strlen($prefix));
            $columns[$table . '.' . (string) ($row['COLUMN_NAME'] ?? '')] =
                (string) ($row['COLUMN_TYPE'] ?? '') . '|' . (string) ($row['IS_NULLABLE'] ?? '') . '|' . (string) ($row['COLLATION_NAME'] ?? '');
            $row = \F_db_fetch_assoc($res);
        }

        return $columns;
    }

    private function countNonUtf8mb4Columns(string $prefix): int
    {
        $res = \F_db_query(
            'SELECT COUNT(*) AS n FROM information_schema.COLUMNS'
            . " WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '" . $prefix . "%'"
            . " AND CHARACTER_SET_NAME IS NOT NULL AND CHARACTER_SET_NAME <> 'utf8mb4'",
            $this->db
        );

        return (int) \F_db_fetch_assoc($res)['n'];
    }
}
