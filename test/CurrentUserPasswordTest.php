<?php

//============================================================+
// File name   : CurrentUserPasswordTest.php
// Begin       : 2026-10-17
//
// Description : Unit tests for the current user password lock
//               and SSO checks (shared/code/tce_functions_authorization.php).
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test;

use PHPUnit\Framework\TestCase;

/**
 * @file
 * Tests for F_has_current_user_password(), F_get_current_user_password_lock_time() and the
 * lock and input checks of F_check_current_user_password() that return before the database query.
 * F_check_current_user_password() includes '../config/tce_config.php' relative to the working
 * directory, so the tests run from a temporary directory tree that provides the file.
 * @package com.tecnick.tcexam.test
 */
final class CurrentUserPasswordTest extends TestCase
{
    private static string $root = '';

    private static string $cwd = '';

    /** @var array<string,mixed> */
    private array $session = [];

    public static function setUpBeforeClass(): void
    {
        self::$cwd = (string) getcwd();
        self::$root = (string) tempnam(sys_get_temp_dir(), 'tcexam-password-test-');
        unlink(self::$root);
        mkdir(self::$root . '/area/code', 0o777, true);
        mkdir(self::$root . '/area/config', 0o777, true);
        file_put_contents(self::$root . '/area/config/tce_config.php', "<?php\n");
        chdir(self::$root . '/area/code');
    }

    public static function tearDownAfterClass(): void
    {
        chdir(self::$cwd);
        unlink(self::$root . '/area/config/tce_config.php');
        rmdir(self::$root . '/area/code');
        rmdir(self::$root . '/area/config');
        rmdir(self::$root . '/area');
        rmdir(self::$root);
    }

    protected function setUp(): void
    {
        $this->session = $_SESSION ?? [];
        $_SESSION = ['session_user_id' => 2];
    }

    protected function tearDown(): void
    {
        $_SESSION = $this->session;
    }

    public function testPasswordIsCheckedUnlessSsoSession(): void
    {
        $this->assertTrue(\F_has_current_user_password());
        $_SESSION['session_user_sso'] = false;
        $this->assertTrue(\F_has_current_user_password());
        $_SESSION['session_user_sso'] = true;
        $this->assertFalse(\F_has_current_user_password());
    }

    public function testNoLockBelowFiveFailures(): void
    {
        $this->assertSame(0, \F_get_current_user_password_lock_time());
        $_SESSION['session_password_failures'] = 4;
        $_SESSION['session_password_failure_time'] = time();
        $this->assertSame(0, \F_get_current_user_password_lock_time());
    }

    public function testLockAfterFiveFailures(): void
    {
        $_SESSION['session_password_failures'] = 5;
        $_SESSION['session_password_failure_time'] = time() - 100;
        $lock = (int) \F_get_current_user_password_lock_time();
        $this->assertGreaterThan(790, $lock);
        $this->assertLessThanOrEqual(800, $lock);
    }

    public function testLockExpiresAfterFifteenMinutes(): void
    {
        $_SESSION['session_password_failures'] = 5;
        $_SESSION['session_password_failure_time'] = time() - 900;
        $this->assertSame(0, \F_get_current_user_password_lock_time());
    }

    public function testLockedCheckFailsWithoutCountingAttempt(): void
    {
        $time = time() - 10;
        $_SESSION['session_password_failures'] = 5;
        $_SESSION['session_password_failure_time'] = $time;
        $this->assertFalse(\F_check_current_user_password('any-password'));
        $this->assertSame(5, $_SESSION['session_password_failures'] ?? null);
        $this->assertSame($time, $_SESSION['session_password_failure_time'] ?? null);
    }

    public function testInvalidInputFails(): void
    {
        $this->assertFalse(\F_check_current_user_password(''));
        $this->assertFalse(\F_check_current_user_password(['x']));
        $this->assertFalse(\F_check_current_user_password(null));
        unset($_SESSION['session_user_id']);
        $this->assertFalse(\F_check_current_user_password('any-password'));
        $this->assertArrayNotHasKey('session_password_failures', $_SESSION);
    }
}
