<?php

//============================================================+
// File name   : TmxResourceBundleTest.php
// Begin       : 2026-06-22
//
// Description : Unit tests for the TMX translation parser in
//               shared/code/tce_tmx.php
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

namespace Test;

use PHPUnit\Framework\TestCase;
use TMXResourceBundle;

/**
 * @file
 * Tests for TMX (translation memory) parsing into the per-language resource array.
 * @package com.tecnick.tcexam.test
 */
final class TmxResourceBundleTest extends TestCase
{
    private function fixture(): string
    {
        return __DIR__ . '/fixtures/sample.tmx';
    }

    public function testParsesRequestedLanguage(): void
    {
        $bundle = new TMXResourceBundle($this->fixture(), 'IT', '');
        $res = $bundle->getResource();
        $this->assertSame('Ciao', $res['greeting']);
        $this->assertSame('Mondo', $res['world']);
    }

    public function testLanguageMatchIsCaseInsensitive(): void
    {
        $bundle = new TMXResourceBundle($this->fixture(), 'en', '');
        $res = $bundle->getResource();
        $this->assertSame('Hello', $res['greeting']);
        $this->assertSame('World', $res['world']);
    }

    public function testMissingLanguageYieldsEmptyString(): void
    {
        $bundle = new TMXResourceBundle($this->fixture(), 'XX', '');
        $res = $bundle->getResource();
        $this->assertArrayHasKey('greeting', $res);
        $this->assertSame('', $res['greeting']);
    }

    public function testParsesEntitiesAndAccents(): void
    {
        $bundle = new TMXResourceBundle($this->fixture(), 'IT', '');
        $res = $bundle->getResource();
        // the XML parser decodes &amp; to & and preserves the accented UTF-8 characters
        $this->assertSame('Caffè & Tè', $res['special']);
    }
}
