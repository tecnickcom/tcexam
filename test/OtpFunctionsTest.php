<?php

//============================================================+
// File name   : OtpFunctionsTest.php
// Begin       : 2026-11-02
//
// Description : Unit tests for the One Time Password functions
//               (shared/code/tce_functions_otp.php).
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test;

use PHPUnit\Framework\TestCase;

/**
 * @file
 * Tests for F_getOTP() and F_isValidOTPCode() with the RFC 6238 SHA-1 test vectors.
 * @package com.tecnick.tcexam.test
 */
final class OtpFunctionsTest extends TestCase
{
    /** Base32 encoding of the RFC 6238 SHA-1 secret "12345678901234567890". */
    private const KEY = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    public function testGetOtpMatchesRfc6238Vectors(): void
    {
        self::assertSame(287082, F_getOTP(self::KEY, 59));
        self::assertSame(81804, F_getOTP(self::KEY, 1111111109));
        self::assertSame(5924, F_getOTP(self::KEY, 1234567890));
    }

    public function testCodeWithLeadingZeroIsValid(): void
    {
        self::assertTrue(F_isValidOTPCode(self::KEY, '081804', 1111111109));
        self::assertTrue(F_isValidOTPCode(self::KEY, '005924', 1234567890));
    }

    public function testPreviousAndNextStepAreValid(): void
    {
        self::assertTrue(F_isValidOTPCode(self::KEY, '081804', 1111111109 + 30));
        self::assertTrue(F_isValidOTPCode(self::KEY, '081804', 1111111109 - 30));
        self::assertFalse(F_isValidOTPCode(self::KEY, '081804', 1111111109 + 90));
    }

    public function testInvalidInputIsRejected(): void
    {
        self::assertFalse(F_isValidOTPCode(self::KEY, '81804', 1111111109));
        self::assertFalse(F_isValidOTPCode(self::KEY, '000000', 1111111109));
        self::assertFalse(F_isValidOTPCode(self::KEY, '', 1111111109));
        self::assertFalse(F_isValidOTPCode(self::KEY, null, 1111111109));
        self::assertFalse(F_isValidOTPCode(self::KEY, ['081804'], 1111111109));
    }

    public function testEmptyKeyIsRejected(): void
    {
        $code = sprintf('%06d', F_getOTP('', 1111111109));
        self::assertFalse(F_isValidOTPCode('', $code, 1111111109));
        self::assertFalse(F_isValidOTPCode(null, $code, 1111111109));
    }

    public function testNormalizeOtpKey(): void
    {
        $this->assertSame('JBSWY3DPEHPK3PXP', \F_normalizeOTPkey(' jbsw y3dp ehpk 3pxp '));
        $this->assertSame('MFRGG', \F_normalizeOTPkey("MFRGG===\n"));
        $this->assertSame('', \F_normalizeOTPkey('  '));
    }

    public function testValidOtpKey(): void
    {
        $this->assertTrue(\F_isValidOTPkey('ABCDEFGHIJKLMNOP'));
        $this->assertTrue(\F_isValidOTPkey(\F_getRandomOTPkey()));
        $this->assertFalse(\F_isValidOTPkey('ABCDEFGHIJKLMNO'), '15 characters');
        $this->assertFalse(\F_isValidOTPkey('ABCDEFGHIJKLMNO1'), '1 is not a base32 character');
        $this->assertFalse(\F_isValidOTPkey('abcdefghijklmnop'), 'the key must be normalized');
        $this->assertFalse(\F_isValidOTPkey(''));
    }
}
