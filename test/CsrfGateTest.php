<?php

//============================================================+
// File name   : CsrfGateTest.php
// Begin       : 2026-10-17
//
// Description : Unit tests for the CSRF check that runs when
//               shared/code/tce_functions_form.php is included.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @file
 * The CSRF check runs at include time and calls exit(), so each case includes the form helpers
 * in a separate PHP process with a simulated request.
 * @package com.tecnick.tcexam.test
 */
final class CsrfGateTest extends TestCase
{
    private static string $script = '';

    public static function setUpBeforeClass(): void
    {
        $root = var_export(realpath(__DIR__ . '/..'), true);
        $code = <<<PHP
            <?php
            define('K_RANDOM_SECURITY', 'test-csrf-gate-secret');
            define('K_COOKIE_HTTPONLY', true);
            define('K_COOKIE_SECURE', true);
            define('K_COOKIE_SAMESITE', 'Strict');
            require {$root} . '/shared/code/tce_functions_general.php';
            require {$root} . '/shared/code/tce_functions_session.php';
            [, \$method, \$token, \$button] = \$argv;
            \$_SERVER['REQUEST_METHOD'] = \$method;
            \$_POST = [];
            if (\$token === 'valid') {
                \$_POST['csrf_token'] = F_getCSRFToken();
            } elseif (\$token === 'invalid') {
                \$_POST['csrf_token'] = 'invalid';
            }
            if (\$button !== '-') {
                \$_POST[\$button] = '1';
            }
            require {$root} . '/shared/code/tce_functions_form.php';
            echo 'PASSED';
            PHP;
        self::$script = (string) tempnam(sys_get_temp_dir(), 'tcexam-csrf-gate-');
        file_put_contents(self::$script, $code);
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$script);
    }

    private function request(string $method, string $tokenMode, string $button): string
    {
        $cmd =
            escapeshellarg(PHP_BINARY)
            . ' -n '
            . escapeshellarg(self::$script)
            . ' '
            . escapeshellarg($method)
            . ' '
            . escapeshellarg($tokenMode)
            . ' '
            . escapeshellarg($button);
        $output = [];
        exec($cmd . ' 2>&1', $output);
        return implode("\n", $output);
    }

    /**
     * @return array<string,array{0:string}>
     */
    public static function postActions(): array
    {
        return [
            'listed button' => ['update'],
            'backup' => ['backup'],
            'download' => ['download'],
            'forcerestore' => ['forcerestore'],
            'sendfile' => ['sendfile'],
            'rename' => ['rename'],
            'unlisted field' => ['group_name'],
        ];
    }

    #[DataProvider('postActions')]
    public function testPostWithoutTokenIsRejected(string $button): void
    {
        $this->assertSame('Forbidden', $this->request('POST', 'none', $button));
    }

    #[DataProvider('postActions')]
    public function testPostWithInvalidTokenIsRejected(string $button): void
    {
        $this->assertSame('Forbidden', $this->request('POST', 'invalid', $button));
    }

    #[DataProvider('postActions')]
    public function testPostWithValidTokenIsAccepted(string $button): void
    {
        $this->assertSame('PASSED', $this->request('POST', 'valid', $button));
    }

    public function testGetIsNotChecked(): void
    {
        $this->assertSame('PASSED', $this->request('GET', 'none', 'backup'));
    }

    public function testEmptyPostIsNotChecked(): void
    {
        $this->assertSame('PASSED', $this->request('POST', 'none', '-'));
        $this->assertSame('Forbidden', $this->request('POST', 'invalid', '-'));
    }
}
