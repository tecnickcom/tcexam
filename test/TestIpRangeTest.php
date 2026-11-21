<?php

//============================================================+
// File name   : TestIpRangeTest.php
// Begin       : 2026-11-18
//
// Description : Unit tests for the test IP range check
//               (shared/code/tce_functions_test.php).
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
 * Tests for F_isValidIP().
 * @package com.tecnick.tcexam.test
 */
final class TestIpRangeTest extends TestCase
{
    /**
     * @return array<string,array{0:string,1:string,2:bool}>
     */
    public static function ipRangeCases(): array
    {
        return [
            'any: IPv4' => ['192.0.2.10', '*.*.*.*', true],
            'any: IPv4-mapped IPv6' => ['::ffff:198.51.100.7', '*.*.*.*', true],
            'any: native IPv6' => ['2001:db8::1', '*.*.*.*', true],
            'any: link-local IPv6 with zone' => ['fe80::1%eth0', '*.*.*.*', true],
            'any: bare star, IPv6' => ['2001:db8::1', '*', true],
            'any: bare star, IPv4' => ['192.0.2.10', '*', true],
            'partial wildcard: IPv4 match' => ['192.168.1.1', '192.168.*.*', true],
            'partial wildcard: IPv4 mismatch' => ['10.0.0.1', '192.168.*.*', false],
            'partial wildcard: IPv6 mismatch' => ['2001:db8::1', '192.168.*.*', false],
            'IPv6 range: inside' => ['2001:db8::10', '2001:db8::-2001:db8::ffff', true],
            'IPv6 range: outside' => ['2001:db9::1', '2001:db8::-2001:db8::ffff', false],
            'IPv4 range: inside' => ['192.0.2.20', '192.0.2.16-192.0.2.32', true],
            'IPv4 range: outside' => ['192.0.2.40', '192.0.2.16-192.0.2.32', false],
            'exact IPv4' => ['192.0.2.10', '192.0.2.10', true],
            'exact IPv6' => ['2001:db8::1', '2001:db8:0:0:0:0:0:1', true],
            'list with spaces: second entry' => ['10.0.0.1', '192.168.*.*, 10.0.0.1', true],
            'list with spaces: wildcard entry' => ['2001:db8::1', '10.0.0.1, *.*.*.*', true],
            'list with empty entry' => ['10.0.0.1', '192.168.*.*,,10.0.0.1', true],
            'list without match' => ['10.0.0.2', '192.168.*.*,10.0.0.1', false],
        ];
    }

    #[DataProvider('ipRangeCases')]
    public function testIpRange(string $userIp, string $range, bool $expected): void
    {
        $this->assertSame($expected, \F_isValidIP(\getNormalizedIP($userIp), $range));
    }

    public function testEmptyInputIsRejected(): void
    {
        $this->assertFalse(\F_isValidIP('', '*.*.*.*'));
        $this->assertFalse(\F_isValidIP(\getNormalizedIP('192.0.2.10'), ''));
    }

    public function testInvalidUserIpIsRejected(): void
    {
        $this->assertFalse(\F_isValidIP('not-an-ip', '*.*.*.*'));
    }
}
