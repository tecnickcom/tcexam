<?php

//============================================================+
// File name   : TcecodeObjectsTest.php
// Begin       : 2026-10-17
//
// Description : Unit tests for the rendering of cache files referenced by
//               TCECode [object] tags and file previews
//               (shared/code/tce_functions_tcecode.php).
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
 * Tests for F_objects_replacement(), F_objects_callback() and F_is_valid_object_path().
 * The functions include '../config/tce_config.php' and '../../shared/config/tce_mime.php'
 * relative to the working directory, so the tests run from a temporary directory tree that
 * provides both files.
 * @package com.tecnick.tcexam.test
 */
final class TcecodeObjectsTest extends TestCase
{
    /** 1x1 PNG image. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    private static string $root = '';

    private static string $cwd = '';

    public static function setUpBeforeClass(): void
    {
        self::$cwd = (string) getcwd();
        self::$root = (string) tempnam(sys_get_temp_dir(), 'tcexam-tcecode-test-');
        unlink(self::$root);
        mkdir(self::$root . '/area/code', 0o777, true);
        mkdir(self::$root . '/area/config', 0o777, true);
        mkdir(self::$root . '/shared/config', 0o777, true);
        file_put_contents(self::$root . '/area/config/tce_config.php', "<?php\n");
        copy(__DIR__ . '/../shared/config.default/tce_mime.php', self::$root . '/shared/config/tce_mime.php');
        if (!is_dir(K_PATH_CACHE . 'img')) {
            mkdir(K_PATH_CACHE . 'img', 0o777, true);
        }

        file_put_contents(K_PATH_CACHE . 'img/dot.png', (string) base64_decode(self::PNG, true));
        chdir(self::$root . '/area/code');
    }

    public static function tearDownAfterClass(): void
    {
        chdir(self::$cwd);
        unlink(self::$root . '/area/config/tce_config.php');
        unlink(self::$root . '/shared/config/tce_mime.php');
        rmdir(self::$root . '/area/code');
        rmdir(self::$root . '/area/config');
        rmdir(self::$root . '/area');
        rmdir(self::$root . '/shared/config');
        rmdir(self::$root . '/shared');
        rmdir(self::$root);
        unlink(K_PATH_CACHE . 'img/dot.png');
        rmdir(K_PATH_CACHE . 'img');
        rmdir(K_PATH_CACHE);
    }

    private static function assertNoEmbeddedDocument(string $html): void
    {
        foreach (['<object', '<embed', '<iframe', '<param', '<script'] as $tag) {
            self::assertStringNotContainsStringIgnoringCase($tag, $html);
        }
    }

    /**
     * @return array<string,array{0:string}>
     */
    public static function documentExtensions(): array
    {
        return [
            'xml' => ['xml'],
            'html' => ['html'],
            'htm' => ['htm'],
            'js' => ['js'],
            'swf' => ['swf'],
            'pdf' => ['pdf'],
            'txt' => ['txt'],
        ];
    }

    #[DataProvider('documentExtensions')]
    public function testDocumentsAreRenderedAsDownloadLinks(string $extension): void
    {
        $html = \F_objects_replacement('uid/3/file', $extension);
        self::assertNoEmbeddedDocument($html);
        $this->assertStringStartsWith('<a href="/cache/uid/3/file.' . $extension . '" download="">', $html);
    }

    public function testUnknownTypeIsNotRendered(): void
    {
        $html = \F_objects_replacement('uid/3/file', 'unknownext');
        self::assertNoEmbeddedDocument($html);
        $this->assertStringContainsString('UNKNOW MIME TYPE', $html);
    }

    public function testImagesUseImgElement(): void
    {
        $this->assertSame('<img src="/cache/img/dot.png" alt="image:img/dot.png" width="1" height="1" class="tcecode" />', \F_objects_replacement(
            'img/dot',
            'png',
        ));
        $this->assertStringStartsWith('<img src="/cache/img/logo.svg"', \F_objects_replacement('img/logo', 'svg'));
    }

    public function testAudioAndVideoUseMediaElements(): void
    {
        $audio = \F_objects_replacement('media/song', 'mp3');
        $this->assertStringStartsWith('<audio controls="controls" preload="none" src="/cache/media/song.mp3"', $audio);
        self::assertNoEmbeddedDocument($audio);

        $video = \F_objects_replacement('media/clip', 'ogv', 320, 240);
        $this->assertStringStartsWith(
            '<video controls="controls" preload="none" src="/cache/media/clip.ogv" width="320" height="240"',
            $video,
        );
        self::assertNoEmbeddedDocument($video);
    }

    /**
     * @return array<string,array{0:string,1:string}>
     */
    public static function invalidPaths(): array
    {
        return [
            'parent segment' => ['../admin/code/index', 'png'],
            'nested parent segment' => ['uid/../../x', 'png'],
            'absolute path' => ['/etc/x', 'png'],
            'scheme' => ['http://example.com/x', 'png'],
            'protocol relative' => ['//example.com/x', 'png'],
            'backslash' => ['uid\\3\\x', 'png'],
            'hidden segment' => ['uid/.hidden/x', 'png'],
            'empty segment' => ['uid//x', 'png'],
            'control character' => ["uid/x\n", 'png'],
            'empty name' => ['', 'png'],
            'quoted extension' => ['x', 'png" onerror="alert(1)'],
            'empty extension' => ['x', ''],
        ];
    }

    #[DataProvider('invalidPaths')]
    public function testInvalidPathsAreRejected(string $name, string $extension): void
    {
        $this->assertFalse(\F_is_valid_object_path($name, $extension));
        $this->assertSame('[ERROR - INVALID OBJECT PATH]', \F_objects_replacement($name, $extension));
    }

    public function testPathSegmentsAreUrlEncoded(): void
    {
        $this->assertStringStartsWith('<img src="/cache/my%20dir/a%26b.png"', \F_objects_replacement(
            'my dir/a&b',
            'png',
        ));
    }

    public function testAltTextIsEscaped(): void
    {
        $html = \F_objects_replacement('img/dot', 'png', 0, 0, '"><script>alert(1)</script>');
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringContainsString('alt="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;"', $html);
        $this->assertStringContainsString('alt="a &amp; b"', \F_objects_replacement(
            'img/dot',
            'png',
            0,
            0,
            'a &amp; b',
        ));
    }

    public function testTcecodeObjectTagDoesNotEmbedDocuments(): void
    {
        $html = \F_objects_callback(['[object]uid/3/poc.xml[/object]', 'uid/3/poc', 'xml']);
        self::assertNoEmbeddedDocument($html);
        $this->assertStringStartsWith('<a href="/cache/uid/3/poc.xml" download="">', $html);
    }

    public function testTcecodeObjectTagPathIsDecodedBeforeValidation(): void
    {
        $this->assertSame(
            '[ERROR - INVALID OBJECT PATH]',
            \F_objects_callback(['[object]uid/../x.png[/object]', 'uid/../x', 'png']),
        );
        $this->assertStringStartsWith(
            '<img src="/cache/%22onerror%3Dx.png"',
            \F_objects_callback(['[object]&quot;onerror=x.png[/object]', '&quot;onerror=x', 'png']),
        );
        $this->assertStringStartsWith(
            '<img src="/cache/a%26b.png"',
            \F_objects_callback(['[object]a&amp;b.png[/object]', 'a&amp;b', 'png']),
        );
    }
}
