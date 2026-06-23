<?php

//============================================================+
// File name   : GeneralFunctionsTest.php
// Begin       : 2026-06-22
//
// Description : Unit tests for shared/code/tce_functions_general.php
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test;

use PHPUnit\Framework\TestCase;

/**
 * @file
 * Tests for the pure general/utility/rating helpers.
 * @package com.tecnick.tcexam.test
 */
final class GeneralFunctionsTest extends TestCase
{
    public function testGetBoolean(): void
    {
        $this->assertTrue(\F_getBoolean(true));
        $this->assertFalse(\F_getBoolean(false));
        $this->assertTrue(\F_getBoolean('t'));    // PostgreSQL boolean text
        $this->assertTrue(\F_getBoolean('true'));
        $this->assertTrue(\F_getBoolean('1'));
        $this->assertFalse(\F_getBoolean('f'));
        $this->assertFalse(\F_getBoolean('0'));
        $this->assertTrue(\F_getBoolean(1));
        $this->assertFalse(\F_getBoolean(0));
    }

    public function testUnhtmlentities(): void
    {
        $this->assertSame('&', \unhtmlentities('&amp;'));
        $this->assertSame('<b>', \unhtmlentities('&lt;b&gt;'));
        // with $preserve_tagsign the angular-bracket entities stay literal
        $this->assertSame('&lt;x&gt;', \unhtmlentities('&lt;x&gt;', true));
    }

    public function testCompactString(): void
    {
        $this->assertSame('a b c d', \F_compact_string("a\tb\nc\rd"));
        $this->assertSame('say &quot;hi&quot;', \F_compact_string('say "hi"', true));
    }

    public function testReplaceAngulars(): void
    {
        $this->assertSame('&lt;a href&gt;', \F_replace_angulars('<a href>'));
    }

    public function testTextXmlRoundTrip(): void
    {
        $text = 'a<b> & c';
        $xml = \F_text_to_xml($text);
        $this->assertSame('a&lt;b&gt; &amp; c', $xml);
        $this->assertSame($text, \F_xml_to_text($xml));
        $this->assertSame('', \F_text_to_xml(''));
    }

    public function testTextTsvRoundTrip(): void
    {
        $text = "col1\tcol2\nrow2";
        $tsv = \F_text_to_tsv($text);
        $this->assertSame('col1\tcol2\nrow2', $tsv); // tab/newline escaped to literal sequences
        $this->assertSame($text, \F_tsv_to_text($tsv));
    }

    public function testFormatFloat(): void
    {
        $this->assertSame('1.235', \F_formatFloat(1.23456));
        $this->assertSame('2.000', \F_formatFloat(2));
        $this->assertSame('0.000', \F_formatFloat(null));
    }

    public function testFormatPercentage(): void
    {
        $this->assertSame('(&nbsp;50%)', \F_formatPercentage(0.5));       // ratio 0..1, space-padded
        $this->assertSame('(&nbsp;50%)', \F_formatPercentage(50, false)); // percentage 0..100
        $this->assertSame('(100%)', \F_formatPercentage(1.0));            // 3 digits => no padding
    }

    public function testFormatPdfAndXmlPercentage(): void
    {
        $this->assertSame('( 50%)', \F_formatPdfPercentage(0.5));
        $this->assertSame(' 50', \F_formatXMLPercentage(0.5));
    }

    public function testGetContrastColor(): void
    {
        $this->assertSame('ffffff', \getContrastColor('000000')); // dark background -> white
        $this->assertSame('000000', \getContrastColor('ffffff')); // light background -> black
    }

    public function testIsUrl(): void
    {
        $this->assertTrue(\F_isURL('https://example.com/path'));
        $this->assertTrue(\F_isURL('ftp://host/file'));
        $this->assertFalse(\F_isURL('just text'));
        $this->assertFalse(\F_isURL('/relative/path'));
    }

    public function testNormalizedIpInvariantsAndValidation(): void
    {
        // localhost forms collapse to the same normalized value
        $this->assertSame(\getNormalizedIP('127.0.0.1'), \getNormalizedIP('::1'));
        // an already-expanded IPv6 address normalizes to itself
        $ipv6 = '2001:0db8:0000:0000:0000:0000:0000:0001';
        $this->assertSame($ipv6, \getNormalizedIP($ipv6));
        // invalid inputs
        $this->assertFalse(\getNormalizedIP('not-an-ip'));
        $this->assertFalse(\getNormalizedIP('256.0.0.1'));
    }

    public function testIpAsBytes(): void
    {
        // always packs to the 16-byte IPv6 form, for both IPv4 and IPv6 input
        $this->assertSame(16, \strlen((string) \getIpAsBytes('192.168.1.1')));
        $this->assertSame(16, \strlen((string) \getIpAsBytes('2001:db8::1')));
        // case-insensitive: upper- and lower-case hex pack to identical bytes
        $this->assertSame(\getIpAsBytes('2001:DB8::1'), \getIpAsBytes('2001:db8::1'));
        // equivalent localhost forms pack identically
        $this->assertSame(\getIpAsBytes('127.0.0.1'), \getIpAsBytes('::1'));
        // ordering matches numeric value (byte-wise strcmp)
        $this->assertLessThan(0, \strcmp((string) \getIpAsBytes('::9'), (string) \getIpAsBytes('::a')));
        // invalid input
        $this->assertFalse(\getIpAsBytes('not-an-ip'));
    }

    public function testSubstrUtf8(): void
    {
        $this->assertSame('hel', \F_substr_utf8('hello', 0, 3));
        $this->assertSame('caf', \F_substr_utf8('café', 0, 3));
    }

    public function testUtrim(): void
    {
        $this->assertSame('hi there', \utrim('   hi there   '));
        $this->assertSame('', \utrim(''));
    }

    public function testBcdechex(): void
    {
        if (! \extension_loaded('bcmath')) {
            $this->markTestSkipped('bcmath extension not available');
        }

        $this->assertSame('FF', \bcdechex('255'));
        $this->assertSame('10', \bcdechex('16'));
        $this->assertSame('0', \bcdechex('0'));
    }
}
